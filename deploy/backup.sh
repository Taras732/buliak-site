#!/bin/sh
# Бекап Буляк (VPS): дамп БД + архів wp-content. Зберігає останні 7 копій кожного типу.
# Cron: 30 3 * * *  /root/docker/buliak/backup.sh
set -eu

ENV=/root/docker/buliak/.env
DEST=/root/backups
KEEP=7
DATE=$(date +%F_%H%M)

DB_ROOT=$(grep '^DB_ROOT=' "$ENV" | cut -d= -f2-)
mkdir -p "$DEST"

# 1) Дамп БД (без пароля в ps — через MYSQL_PWD)
docker exec -e MYSQL_PWD="$DB_ROOT" buliak-db \
  mariadb-dump -uroot --single-transaction --quick --default-character-set=utf8mb4 buliak \
  | gzip > "$DEST/buliak-db-$DATE.sql.gz"

# 2) wp-content (uploads / теми / плагіни / mu-plugins)
docker exec buliak-wp tar czf - -C /var/www/html wp-content \
  > "$DEST/buliak-wpcontent-$DATE.tar.gz"

# 3) Ротація: лишити KEEP найновіших кожного типу
ls -1t "$DEST"/buliak-db-*.sql.gz        2>/dev/null | tail -n +$((KEEP+1)) | xargs -r rm -f
ls -1t "$DEST"/buliak-wpcontent-*.tar.gz 2>/dev/null | tail -n +$((KEEP+1)) | xargs -r rm -f

DBSIZE=$(du -h "$DEST/buliak-db-$DATE.sql.gz" | cut -f1)
WPSIZE=$(du -h "$DEST/buliak-wpcontent-$DATE.tar.gz" | cut -f1)
echo "$(date '+%F %T') OK db=$DBSIZE wp=$WPSIZE ($DATE)" >> "$DEST/backup.log"
