#!/bin/bash
# Create MySQL database and import schema for PHP backend. Run from project root.
set -e
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="$ROOT/backend-php/.env"
SCHEMA="$ROOT/backend-php/database/schema.sql"
if [ ! -f "$SCHEMA" ]; then echo "Schema not found: $SCHEMA"; exit 1; fi
DB_HOST="localhost"
DB_PORT="3306"
DB_DATABASE="inventory_sales_db"
DB_USERNAME="root"
DB_PASSWORD=""
if [ -f "$ENV_FILE" ]; then
  while IFS= read -r line; do
    [[ "$line" =~ ^#.*$ || -z "$line" ]] && continue
    if [[ "$line" =~ ^(DB_HOST|DB_PORT|DB_DATABASE|DB_USERNAME|DB_PASSWORD)=(.*)$ ]]; then
      key="${BASH_REMATCH[1]}"
      val="${BASH_REMATCH[2]}"
      case "$key" in DB_HOST) DB_HOST="$val" ;; DB_PORT) DB_PORT="$val" ;; DB_DATABASE) DB_DATABASE="$val" ;; DB_USERNAME) DB_USERNAME="$val" ;; DB_PASSWORD) DB_PASSWORD="$val" ;; esac
    fi
  done < "$ENV_FILE"
fi
echo "Creating database: $DB_DATABASE (user: $DB_USERNAME)"
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" ${DB_PASSWORD:+-p"$DB_PASSWORD"} -e "CREATE DATABASE IF NOT EXISTS \`$DB_DATABASE\`;"
echo "Importing schema..."
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" ${DB_PASSWORD:+-p"$DB_PASSWORD"} "$DB_DATABASE" < "$SCHEMA"
echo "Done. Database $DB_DATABASE is ready."
