#!/usr/bin/env bash
# Recovery helper for moving every table from one MySQL schema to another.
# Destructive: the target schema is dropped and recreated before tables move.

set -euo pipefail

ROOT_DIR="$(git rev-parse --show-toplevel)"
APP_DIR="$ROOT_DIR/_inc/laravel"
ENV_FILE="$APP_DIR/.env"
SUDO_FILE="$ROOT_DIR/.gh/sudo.yml"

SOURCE_DB="${SOURCE_DB:-}"
TARGET_DB="${TARGET_DB:-erp_brand_new_ideas_company_db}"
DB_USER="${DB_USER:-$(sed -n 's/^DB_USERNAME=//p' "$ENV_FILE" | tail -n 1)}"
DB_PASS="${DB_PASS:-$(sed -n 's/^DB_PASSWORD=//p' "$ENV_FILE" | tail -n 1)}"
LOG="${LOG:-/tmp/rename_db.log}"
WORK_DIR="${TMPDIR:-/tmp}"
BATCH="${BATCH:-10}"

SUDO_PASS="${SUDO_PASS:-}"
if [ -z "$SUDO_PASS" ] && [ -f "$SUDO_FILE" ]; then
    SUDO_PASS="$(sed -n 's/^password:[[:space:]]*//p' "$SUDO_FILE" | tail -n 1)"
fi

log() { printf '[%s] %s\n' "$(date '+%H:%M:%S')" "$*" | tee -a "$LOG"; }

mysql_root() {
    if [ -n "$SUDO_PASS" ]; then
        printf '%s\n' "$SUDO_PASS" | sudo -S mysql "$@"
    else
        sudo mysql "$@"
    fi
}

if [ -z "$SOURCE_DB" ]; then
    echo "Set SOURCE_DB to the donor schema before running." >&2
    exit 2
fi

if [ "${CONFIRM_DB_RENAME:-}" != "DROP_AND_RECREATE_TARGET" ]; then
    echo "Refusing to run without CONFIRM_DB_RENAME=DROP_AND_RECREATE_TARGET." >&2
    exit 2
fi

for db_name in "$SOURCE_DB" "$TARGET_DB"; do
    if [[ ! "$db_name" =~ ^[A-Za-z0-9_]+$ ]]; then
        echo "Unsafe database name: $db_name" >&2
        exit 2
    fi
done

RENAME_BATCH="$WORK_DIR/rename_batch.sql"
RENAME_CURRENT="$WORK_DIR/rename_current_batch.sql"
RENAME_ERRORS="$WORK_DIR/rename_errors.log"

log "Starting DB table move: $SOURCE_DB -> $TARGET_DB"

SRC_COUNT="$(mysql_root -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$SOURCE_DB';" 2>/dev/null)"
log "Source tables in $SOURCE_DB: $SRC_COUNT"

log "Recreating target DB $TARGET_DB..."
mysql_root -e "
SET FOREIGN_KEY_CHECKS=0;
DROP DATABASE IF EXISTS $TARGET_DB;
CREATE DATABASE $TARGET_DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON $TARGET_DB.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
SET FOREIGN_KEY_CHECKS=1;
" 2>/dev/null
log "Target DB recreated."

mysql_root -N -e "
SELECT CONCAT('RENAME TABLE $SOURCE_DB.\`', table_name, '\` TO $TARGET_DB.\`', table_name, '\`;')
FROM information_schema.tables
WHERE table_schema = '$SOURCE_DB'
ORDER BY table_name;
" 2>/dev/null > "$RENAME_BATCH"

TOTAL="$(wc -l < "$RENAME_BATCH")"
log "Generated $TOTAL RENAME statements."

MOVED=0
while IFS= read -r line; do
    printf '%s\n' "$line" >> "$RENAME_CURRENT"
    ((MOVED++))

    if (( MOVED % BATCH == 0 )) || (( MOVED == TOTAL )); then
        if mysql_root < "$RENAME_CURRENT" 2>>"$RENAME_ERRORS"; then
            log "Batch complete: $MOVED/$TOTAL tables moved"
        else
            log "ERROR in batch ending at table $MOVED; continuing"
            tail -3 "$RENAME_ERRORS" >> "$LOG"
        fi
        : > "$RENAME_CURRENT"
        sleep 1
    fi
done < "$RENAME_BATCH"

DST_COUNT="$(mysql_root -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$TARGET_DB';" 2>/dev/null)"
log "Target tables in $TARGET_DB: $DST_COUNT (expected: $TOTAL)"

if [ "$DST_COUNT" -eq "$TOTAL" ]; then
    log "All $TOTAL tables moved successfully."
    if [ "${UPDATE_ENV:-0}" = "1" ]; then
        sed -i "s/^DB_DATABASE=.*/DB_DATABASE=$TARGET_DB/" "$ENV_FILE"
        sed -i "s/^DB_USERNAME=.*/DB_USERNAME=$DB_USER/" "$ENV_FILE"
        sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$DB_PASS|" "$ENV_FILE"
        log ".env updated to point to $TARGET_DB"
    fi
    rm -f "$RENAME_BATCH" "$RENAME_CURRENT" "$RENAME_ERRORS"
else
    log "Mismatch: expected $TOTAL, got $DST_COUNT"
    log "Tables remaining in $SOURCE_DB:"
    mysql_root -N -e "SELECT table_name FROM information_schema.tables WHERE table_schema='$SOURCE_DB';" 2>/dev/null | tee -a "$LOG"
fi

log "Done."
