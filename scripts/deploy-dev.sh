#!/usr/bin/env bash

set -Eeuo pipefail
IFS=$'\n\t'
umask 0027

readonly SOURCE_ROOT="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
readonly DEPLOY_CONTEXT="${DEPLOY_CONTEXT:-dev}"
readonly EXPECTED_DEPLOY_MARKER="mamo-ut-${DEPLOY_CONTEXT}"
readonly APP_ROOT="${APP_ROOT:-/var/www/mamo-ut}"
readonly RELEASES_DIR="$APP_ROOT/releases"
readonly SHARED_DIR="$APP_ROOT/shared"
readonly CURRENT_LINK="$APP_ROOT/current"
readonly LOCK_FILE="$APP_ROOT/.deploy.lock"
readonly DEPLOY_MARKER="$APP_ROOT/.mamo-ut-deploy-root"
readonly HEALTHCHECK_URL="${HEALTHCHECK_URL:-}"
readonly KEEP_RELEASES="${KEEP_RELEASES:-5}"
readonly RELOAD_SERVICES="${RELOAD_SERVICES:-false}"
readonly ACTION="${1:-deploy}"

log() {
    printf '[deploy-%s] %s\n' "$DEPLOY_CONTEXT" "$*"
}

fail() {
    printf '[deploy-%s] ERROR: %s\n' "$DEPLOY_CONTEXT" "$*" >&2
    exit 1
}

require_command() {
    command -v "$1" >/dev/null 2>&1 \
        || fail "Required command '$1' is not installed or is not on PATH."
}

validate_configuration() {
    local canonical_root
    local marker_value

    require_command realpath
    [[ "$DEPLOY_CONTEXT" == "dev" || "$DEPLOY_CONTEXT" == "production" ]] \
        || fail "DEPLOY_CONTEXT must be 'dev' or 'production'."
    [[ "$APP_ROOT" == "/var/www/mamo-ut" ]] \
        || fail "APP_ROOT must be exactly /var/www/mamo-ut for this repository."
    canonical_root="$(realpath -m -- "$APP_ROOT")"
    [[ "$canonical_root" == "/var/www/mamo-ut" ]] \
        || fail "APP_ROOT resolves to unexpected path '$canonical_root'."
    [[ -f "$DEPLOY_MARKER" && ! -L "$DEPLOY_MARKER" ]] \
        || fail "Missing trusted deploy marker $DEPLOY_MARKER. Follow the matching DEV or production deployment runbook."
    marker_value="$(<"$DEPLOY_MARKER")"
    [[ "$marker_value" == "$EXPECTED_DEPLOY_MARKER" ]] \
        || fail "Invalid deploy marker in $DEPLOY_MARKER; expected $EXPECTED_DEPLOY_MARKER."
    [[ -n "$HEALTHCHECK_URL" ]] \
        || fail "HEALTHCHECK_URL is required (for example http://127.0.0.1/up)."
    [[ "$KEEP_RELEASES" =~ ^[0-9]+$ ]] \
        || fail "KEEP_RELEASES must be an integer."
    ((KEEP_RELEASES >= 2 && KEEP_RELEASES <= 50)) \
        || fail "KEEP_RELEASES must be between 2 and 50."
    [[ "$RELOAD_SERVICES" == "true" || "$RELOAD_SERVICES" == "false" ]] \
        || fail "RELOAD_SERVICES must be 'true' or 'false'."
}

prepare_layout() {
    install -d -m 2775 "$APP_ROOT" "$RELEASES_DIR" "$SHARED_DIR"
    install -d -m 2775 \
        "$SHARED_DIR/storage/app/public" \
        "$SHARED_DIR/storage/framework/cache/data" \
        "$SHARED_DIR/storage/framework/sessions" \
        "$SHARED_DIR/storage/framework/testing" \
        "$SHARED_DIR/storage/framework/views" \
        "$SHARED_DIR/storage/logs"

    [[ -f "$SHARED_DIR/.env" ]] \
        || fail "Missing $SHARED_DIR/.env. Provision it on the server with mode 0640; never commit it."
    [[ ! -e "$CURRENT_LINK" || -L "$CURRENT_LINK" ]] \
        || fail "$CURRENT_LINK exists but is not a symbolic link."
}

acquire_lock() {
    require_command flock
    exec 9>"$LOCK_FILE"
    flock -n 9 || fail "Another deployment for this environment is already running."
}

atomic_switch() {
    local target="$1"
    local temporary_link="$APP_ROOT/.current-${RANDOM}-${RANDOM}"

    [[ -d "$target" ]] || fail "Release target does not exist: $target"
    ln -s "$target" "$temporary_link"
    mv -Tf "$temporary_link" "$CURRENT_LINK"
}

restart_runtime() {
    php "$CURRENT_LINK/artisan" queue:restart

    if [[ "$RELOAD_SERVICES" == "true" ]]; then
        require_command sudo
        sudo -n systemctl reload php8.3-fpm
        sudo -n systemctl reload nginx
        sudo -n supervisorctl restart 'mamo-ut-worker:*'
    fi
}

health_check() {
    require_command curl
    curl \
        --fail \
        --silent \
        --show-error \
        --location \
        --max-time 15 \
        --retry 5 \
        --retry-all-errors \
        --retry-delay 2 \
        "$HEALTHCHECK_URL" >/dev/null
}

rollback_after_failed_switch() {
    local previous_release="$1"

    if [[ -n "$previous_release" && -d "$previous_release" ]]; then
        log "Health check failed; switching back to $previous_release."
        atomic_switch "$previous_release"
        restart_runtime || true
        health_check || true
    else
        log "Health check failed on the first deployment; removing the failed current link."
        unlink "$CURRENT_LINK"
    fi
}

publish_runtime_permissions() {
    local release_dir="$1"

    chgrp -R www-data "$release_dir"
    chmod -R g+rX "$release_dir"
    find "$release_dir" -type d -exec chmod g+s {} +
}

prune_old_releases() {
    local current_release
    local candidate
    local candidate_real
    local release_name
    local position=0

    current_release="$(readlink -f "$CURRENT_LINK")"

    while IFS= read -r candidate; do
        position=$((position + 1))
        if ((position <= KEEP_RELEASES)) || [[ "$candidate" == "$current_release" ]]; then
            continue
        fi

        candidate_real="$(realpath -- "$candidate")"
        case "$candidate_real" in
            "$RELEASES_DIR"/*)
                ;;
            *)
                fail "Refusing to prune path outside the releases directory: $candidate_real"
                ;;
        esac

        release_name="$(basename -- "$candidate_real")"
        [[ "$release_name" =~ ^[0-9]{14}-[0-9a-fA-F]{12}$ ]] \
            || fail "Refusing to prune directory with invalid release name: $release_name"
        [[ -d "$candidate_real" && ! -L "$candidate" && -f "$candidate_real/REVISION" ]] \
            || fail "Refusing to prune invalid release path: $candidate"
        log "Pruning old release $candidate_real."
        rm -rf -- "$candidate_real"
    done < <(find "$RELEASES_DIR" -mindepth 1 -maxdepth 1 -type d -print | sort -r)
}

deploy_release() {
    local sha="${DEPLOY_SHA:-${GITHUB_SHA:-}}"
    local release_id
    local release_dir
    local previous_release=""

    require_command composer
    require_command chgrp
    require_command git
    require_command npm
    require_command php
    require_command rsync

    [[ -f "$SOURCE_ROOT/composer.json" && -f "$SOURCE_ROOT/composer.lock" ]] \
        || fail "Deployment requires composer.json and composer.lock."
    [[ -f "$SOURCE_ROOT/package.json" && -f "$SOURCE_ROOT/package-lock.json" ]] \
        || fail "Deployment requires package.json and package-lock.json."
    [[ -f "$SOURCE_ROOT/artisan" ]] \
        || fail "Deployment requires the Laravel artisan entry point."

    if [[ -z "$sha" ]]; then
        sha="$(git -C "$SOURCE_ROOT" rev-parse HEAD)"
    fi
    [[ "$sha" =~ ^[0-9a-fA-F]{40}$ ]] \
        || fail "DEPLOY_SHA/GITHUB_SHA (or local HEAD) is not a full Git commit SHA."

    release_id="$(date -u +%Y%m%d%H%M%S)-${sha:0:12}"
    release_dir="$RELEASES_DIR/$release_id"
    [[ ! -e "$release_dir" ]] \
        || fail "Release already exists: $release_dir"

    if [[ -L "$CURRENT_LINK" ]]; then
        previous_release="$(readlink -f "$CURRENT_LINK")"
    fi

    log "Preparing release $release_id."
    install -d "$release_dir"
    rsync -a \
        --exclude='.env' \
        --exclude='.git' \
        --exclude='node_modules' \
        --exclude='storage' \
        --exclude='vendor' \
        "$SOURCE_ROOT/" "$release_dir/"

    ln -s "$SHARED_DIR/.env" "$release_dir/.env"
    ln -s "$SHARED_DIR/storage" "$release_dir/storage"
    install -d "$release_dir/bootstrap/cache"
    chmod ug+rwx "$release_dir/bootstrap/cache"

    (
        cd "$release_dir"

        composer install \
            --no-dev \
            --no-interaction \
            --no-progress \
            --prefer-dist \
            --optimize-autoloader

        node -e '
            const manifest = require("./package.json");
            if (!manifest.scripts || typeof manifest.scripts.build !== "string" || !manifest.scripts.build.trim()) {
                console.error("[deploy] ERROR: package.json must define a non-empty build script.");
                process.exit(1);
            }
        '
        npm ci --no-audit --no-fund
        npm run build

        php artisan optimize:clear
        php artisan migrate --force --no-interaction
        php artisan storage:link --force
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
    )

    printf '%s\n' "$sha" > "$release_dir/REVISION"
    publish_runtime_permissions "$release_dir"

    log "Switching current to $release_id."
    atomic_switch "$release_dir"

    if ! restart_runtime || ! health_check; then
        rollback_after_failed_switch "$previous_release"
        fail "Release $release_id failed its post-switch checks and was rolled back. Database migrations are not automatically reversed."
    fi

    log "Release $release_id is healthy at $HEALTHCHECK_URL."
    prune_old_releases
}

rollback_release() {
    local requested_release="${2:-}"
    local current_release=""
    local target_release=""
    local candidate
    local releases=()

    require_command php

    [[ -L "$CURRENT_LINK" ]] \
        || fail "Cannot roll back because $CURRENT_LINK is not a symbolic link."
    current_release="$(readlink -f "$CURRENT_LINK")"

    if [[ -n "$requested_release" ]]; then
        [[ "$requested_release" =~ ^[0-9]{14}-[0-9a-fA-F]{12}$ ]] \
            || fail "Rollback release must have format YYYYMMDDhhmmss-<12 hex characters>."
        target_release="$RELEASES_DIR/$requested_release"
    else
        while IFS= read -r candidate; do
            releases+=("$candidate")
        done < <(find "$RELEASES_DIR" -mindepth 1 -maxdepth 1 -type d -print | sort -r)

        for candidate in "${releases[@]}"; do
            if [[ "$candidate" != "$current_release" && -f "$candidate/REVISION" ]]; then
                target_release="$candidate"
                break
            fi
        done
    fi

    [[ -n "$target_release" && -d "$target_release" ]] \
        || fail "No previous valid release is available for rollback."

    log "Rolling back from $current_release to $target_release."
    atomic_switch "$target_release"
    if ! restart_runtime || ! health_check; then
        atomic_switch "$current_release"
        restart_runtime || true
        fail "Rollback target was unhealthy; the original release was restored."
    fi

    log "Rollback completed. Database migrations were not reversed."
}

validate_configuration
prepare_layout
acquire_lock

case "$ACTION" in
    deploy)
        deploy_release
        ;;
    rollback)
        rollback_release "$@"
        ;;
    *)
        fail "Unknown action '$ACTION'. Use 'deploy' or 'rollback [release-id]'."
        ;;
esac
