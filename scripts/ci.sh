#!/usr/bin/env bash

set -Eeuo pipefail
IFS=$'\n\t'

readonly PROJECT_ROOT="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
readonly MODE="${1:-all}"

cd "$PROJECT_ROOT"

log() {
    printf '[ci] %s\n' "$*"
}

fail() {
    printf '[ci] ERROR: %s\n' "$*" >&2
    exit 1
}

require_command() {
    command -v "$1" >/dev/null 2>&1 \
        || fail "Required command '$1' is not installed or is not on PATH."
}

run_repository_checks() {
    log "Running repository checks."

    require_command git

    git diff --check
    git diff --cached --check
    if git rev-parse --verify HEAD^ >/dev/null 2>&1; then
        git diff --check HEAD^ HEAD
    fi

    local tracked_env
    while IFS= read -r -d '' tracked_env; do
        case "$tracked_env" in
            .env.example | .env.testing.example)
                ;;
            *)
                fail "Tracked runtime environment file '$tracked_env'. Keep only reviewed example templates."
                ;;
        esac
    done < <(git ls-files -z -- '.env*')

    local credential_pattern
    local pem_prefix='-----BEGIN '
    local potential_secret_files
    credential_pattern="${pem_prefix}((OPENSSH|RSA|EC|DSA) |ENCRYPTED )?PRIVATE KEY-----|${pem_prefix}PGP PRIVATE KEY BLOCK-----|github_pat_[A-Za-z0-9_]{20,}|gh[pousr]_[A-Za-z0-9]{20,}|AKIA[0-9A-Z]{16}"
    potential_secret_files="$(
        git grep --cached -IlE -e "$credential_pattern" \
            -- . 2>/dev/null || true
    )"
    if [[ -n "$potential_secret_files" ]]; then
        printf '[ci] Potential credential material found in tracked file(s):\n%s\n' "$potential_secret_files" >&2
        fail "Remove the credential, rotate it if real, and purge it from Git history before continuing."
    fi

    local shell_script
    while IFS= read -r -d '' shell_script; do
        bash -n "$shell_script"
    done < <(find scripts -type f -name '*.sh' -print0 2>/dev/null)

    if [[ ! -f composer.json && ! -f package.json ]]; then
        log "Documentation-only bootstrap detected; application checks are intentionally skipped."
    fi
}

run_backend_checks() {
    if [[ ! -f composer.json ]]; then
        log "composer.json is absent; backend checks are not applicable yet."
        return
    fi

    log "Running PHP/Laravel checks."
    require_command php
    require_command composer

    [[ -f composer.lock ]] \
        || fail "composer.json exists but composer.lock is missing. Commit the lock file before CI."

    composer validate --strict --no-interaction
    composer install \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --optimize-autoloader
    composer check-platform-reqs
    composer audit --locked --no-interaction

    [[ -f artisan ]] \
        || fail "composer.json exists but artisan is missing. A Laravel application must commit artisan."
    [[ -x vendor/bin/pint ]] \
        || fail "Laravel Pint is missing. Add laravel/pint to require-dev and refresh composer.lock."
    [[ -x vendor/bin/phpstan ]] \
        || fail "PHPStan/Larastan is missing. Add it to require-dev and commit its configuration."
    [[ -f phpstan.neon || -f phpstan.neon.dist ]] \
        || fail "PHPStan/Larastan requires phpstan.neon or phpstan.neon.dist."

    local php_file
    local php_directories=(app bootstrap config database routes tests)
    local existing_directories=()
    local directory
    for directory in "${php_directories[@]}"; do
        [[ -d "$directory" ]] && existing_directories+=("$directory")
    done

    if ((${#existing_directories[@]} > 0)); then
        while IFS= read -r -d '' php_file; do
            php -l "$php_file" >/dev/null
        done < <(find "${existing_directories[@]}" -type f -name '*.php' -print0)
    fi

    vendor/bin/pint --test
    vendor/bin/phpstan analyse --memory-limit=1G --no-progress

    # This database is disposable and exists only in GitHub Actions. Never run
    # migrate:fresh against an unverified developer database.
    if [[ "${CI:-false}" == "true" && "${DB_DATABASE:-}" == "mamo_ut_testing" ]]; then
        php artisan migrate:fresh --force --no-interaction
    else
        log "Skipping migrate:fresh outside the dedicated CI database."
    fi

    php artisan test
}

require_npm_script() {
    local script_name="$1"
    node -e '
        const manifest = require("./package.json");
        const name = process.argv[1];
        if (!manifest.scripts || typeof manifest.scripts[name] !== "string" || !manifest.scripts[name].trim()) {
            console.error(`[ci] ERROR: package.json must define a non-empty "${name}" script.`);
            process.exit(1);
        }
    ' "$script_name"
}

run_frontend_checks() {
    if [[ ! -f package.json ]]; then
        log "package.json is absent; frontend checks are not applicable yet."
        return
    fi

    log "Running Node/Tailwind checks."
    require_command node
    require_command npm

    [[ -f package-lock.json ]] \
        || fail "package.json exists but package-lock.json is missing. Commit the npm lock file before CI."

    require_npm_script lint
    require_npm_script test
    require_npm_script build

    npm ci --no-audit --no-fund
    npm audit --audit-level=high
    npm run lint
    npm run test
    npm run build
}

case "$MODE" in
    repository)
        run_repository_checks
        ;;
    backend)
        run_backend_checks
        ;;
    frontend)
        run_frontend_checks
        ;;
    all)
        run_repository_checks
        run_backend_checks
        run_frontend_checks
        ;;
    *)
        fail "Unknown mode '$MODE'. Use: repository, backend, frontend, or all."
        ;;
esac

log "Mode '$MODE' completed successfully."
