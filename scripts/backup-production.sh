#!/usr/bin/env bash

set -Eeuo pipefail
IFS=$'\n\t'
umask 0077

readonly ACTION="${1:-database}"
readonly BACKUP_ROOT="${BACKUP_ROOT:-/var/backups/mamo-ut}"
readonly ARCHIVE_DIR="$BACKUP_ROOT/archives"
readonly BACKUP_MARKER="$BACKUP_ROOT/.mamo-ut-backup-root"
readonly LOCK_FILE="$BACKUP_ROOT/.backup.lock"
readonly MYSQL_DEFAULTS_FILE="${MYSQL_DEFAULTS_FILE:-/etc/mamo-ut/mysql-backup.cnf}"
readonly DB_NAME="${DB_NAME:-mamo_ut}"
readonly SHARED_STORAGE="${SHARED_STORAGE:-/var/www/mamo-ut/shared/storage}"
readonly OFFSITE_REMOTE="${OFFSITE_REMOTE:-}"
readonly TIMESTAMP="$(date -u +%Y%m%dT%H%M%SZ)"

log() {
    printf '[backup-production] %s\n' "$*"
}

fail() {
    printf '[backup-production] ERROR: %s\n' "$*" >&2
    exit 1
}

require_command() {
    command -v "$1" >/dev/null 2>&1 \
        || fail "Required command '$1' is not installed or is not on PATH."
}

validate_configuration() {
    local canonical_root
    local marker_value

    require_command flock
    require_command gzip
    require_command mysqldump
    require_command rclone
    require_command realpath
    require_command sha256sum

    [[ "$ACTION" == "database" || "$ACTION" == "full" ]] \
        || fail "Action must be 'database' or 'full'."
    [[ "$BACKUP_ROOT" == "/var/backups/mamo-ut" ]] \
        || fail "BACKUP_ROOT must be exactly /var/backups/mamo-ut."
    canonical_root="$(realpath -m -- "$BACKUP_ROOT")"
    [[ "$canonical_root" == "/var/backups/mamo-ut" ]] \
        || fail "BACKUP_ROOT resolves to unexpected path '$canonical_root'."
    [[ -f "$BACKUP_MARKER" && ! -L "$BACKUP_MARKER" ]] \
        || fail "Missing trusted backup marker $BACKUP_MARKER."
    marker_value="$(<"$BACKUP_MARKER")"
    [[ "$marker_value" == "mamo-ut-production-backup" ]] \
        || fail "Invalid backup marker in $BACKUP_MARKER."
    [[ -f "$MYSQL_DEFAULTS_FILE" && ! -L "$MYSQL_DEFAULTS_FILE" ]] \
        || fail "Missing MySQL defaults file $MYSQL_DEFAULTS_FILE."
    [[ -n "$OFFSITE_REMOTE" ]] \
        || fail "OFFSITE_REMOTE must name an encrypted rclone destination."

    if [[ "$ACTION" == "full" ]]; then
        require_command tar
        [[ -d "$SHARED_STORAGE" && ! -L "$SHARED_STORAGE" ]] \
            || fail "Shared storage is missing or is a symlink: $SHARED_STORAGE."
    fi
}

acquire_lock() {
    exec 9>"$LOCK_FILE"
    flock -n 9 || fail "Another production backup is already running."
}

upload_archive() {
    local archive="$1"

    sha256sum "$archive" > "$archive.sha256"
    rclone copyto "$archive" "$OFFSITE_REMOTE/$(basename -- "$archive")" --checksum
    rclone copyto "$archive.sha256" "$OFFSITE_REMOTE/$(basename -- "$archive.sha256")" --checksum
}

backup_database() {
    local final_archive="$ARCHIVE_DIR/database-$TIMESTAMP.sql.gz"
    local partial_archive="$final_archive.partial"

    trap 'rm -f -- "$partial_archive"' RETURN

    log "Creating consistent logical database backup."
    mysqldump \
        --defaults-extra-file="$MYSQL_DEFAULTS_FILE" \
        --single-transaction \
        --quick \
        --routines \
        --triggers \
        --events \
        --hex-blob \
        "$DB_NAME" \
        | gzip -9 > "$partial_archive"

    gzip -t "$partial_archive"
    mv -- "$partial_archive" "$final_archive"
    upload_archive "$final_archive"
    trap - RETURN

    log "Database backup uploaded: $(basename -- "$final_archive")."
}

backup_media() {
    local final_archive="$ARCHIVE_DIR/media-$TIMESTAMP.tar.gz"
    local partial_archive="$final_archive.partial"

    trap 'rm -f -- "$partial_archive"' RETURN

    log "Creating full shared-storage backup."
    tar -C "$SHARED_STORAGE" -czf "$partial_archive" .
    gzip -t "$partial_archive"
    mv -- "$partial_archive" "$final_archive"
    upload_archive "$final_archive"
    trap - RETURN

    log "Media backup uploaded: $(basename -- "$final_archive")."
}

prune_local_archives() {
    find "$ARCHIVE_DIR" \
        -maxdepth 1 \
        -type f \
        \( -name 'database-*.sql.gz' -o -name 'database-*.sql.gz.sha256' \
            -o -name 'media-*.tar.gz' -o -name 'media-*.tar.gz.sha256' \) \
        -mtime +8 \
        -delete
}

validate_configuration
install -d -m 0700 "$ARCHIVE_DIR"
acquire_lock
backup_database

if [[ "$ACTION" == "full" ]]; then
    backup_media
fi

prune_local_archives

