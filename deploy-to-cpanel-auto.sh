#!/bin/bash
#
# cPanel Auto-Deploy: Build, upload files via FTP, create DB via API, run schema import.
# Requires: .cpanel-deploy.env (copy from .cpanel-deploy.env.example)
#

set -e
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# --- Load config ---
CONFIG_FILE=".cpanel-deploy.env"
if [[ ! -f "$CONFIG_FILE" ]]; then
  echo "Missing $CONFIG_FILE"
  echo "Copy .cpanel-deploy.env.example to .cpanel-deploy.env and fill in your cPanel/FTP and domain."
  exit 1
fi
# shellcheck source=/dev/null
source "$CONFIG_FILE"

DOMAIN="${DOMAIN:-}"
FTP_HOST="${FTP_HOST:-}"
FTP_USER="${FTP_USER:-}"
FTP_PASS="${FTP_PASS:-}"
CPANEL_HOST="${CPANEL_HOST:-}"
CPANEL_USER="${CPANEL_USER:-}"
CPANEL_TOKEN="${CPANEL_TOKEN:-}"
CPANEL_PASS="${CPANEL_PASS:-}"
DB_NAME="${DB_NAME:-inventory_sales}"
DB_USER="${DB_USER:-inventory_user}"
DB_PASS="${DB_PASS:-}"
SKIP_DB_CREATE="${SKIP_DB_CREATE:-0}"
SKIP_DB_IMPORT="${SKIP_DB_IMPORT:-0}"

if [[ -z "$DOMAIN" || -z "$FTP_HOST" || -z "$FTP_USER" || -z "$FTP_PASS" ]]; then
  echo "In .cpanel-deploy.env set: DOMAIN, FTP_HOST, FTP_USER, FTP_PASS"
  exit 1
fi

# JWT secret for .env
JWT_SECRET="${JWT_SECRET:-$(openssl rand -base64 32 2>/dev/null || echo 'change-me-32-chars-minimum')}"

echo "=========================================="
echo "cPanel Auto-Deploy"
echo "=========================================="
echo "Domain: $DOMAIN"
echo "FTP: $FTP_USER@$FTP_HOST"
echo ""

# --- 1. Build deploy package ---
echo "1. Building deploy package..."
./DEPLOY_TO_CPANEL.sh
cp -f scripts/import-db-once.php cpanel-deploy/run_import_once.php
echo ""

# --- 2. Create database & user via cPanel API (optional) ---
FULL_DB_NAME="$DB_NAME"
FULL_DB_USER="$DB_USER"
AUTH="${CPANEL_TOKEN:-$CPANEL_PASS}"

if [[ "$SKIP_DB_CREATE" != "1" && -n "$CPANEL_HOST" && -n "$CPANEL_USER" && -n "$AUTH" ]]; then
  echo "2. Creating database and user via cPanel API..."
  BASE="https://${CPANEL_HOST}:2083/execute"

  create_db=$(curl -sk -u "${CPANEL_USER}:${AUTH}" "${BASE}/Mysql/create_database?name=${DB_NAME}" 2>/dev/null || true)
  if echo "$create_db" | grep -q '"result"'; then
    # Try to get full database name (cPanel may add prefix)
    FULL_DB_NAME=$(echo "$create_db" | python3 -c "
import sys, json
try:
    d = json.load(sys.stdin)
    r = d.get('result') or {}
    data = r.get('data')
    if isinstance(data, list) and data:
        print(data[0].get('name', '$DB_NAME'))
    elif isinstance(data, dict):
        print(data.get('name', '$DB_NAME'))
    else:
        print('$DB_NAME')
except Exception:
    print('$DB_NAME')
" 2>/dev/null) || FULL_DB_NAME="$DB_NAME"
    echo "   Database: $FULL_DB_NAME"
  else
    echo "   Create database response: $create_db"
  fi

  create_user=$(curl -sk -u "${CPANEL_USER}:${AUTH}" "${BASE}/Mysql/create_user?name=${DB_USER}&password=${DB_PASS}" 2>/dev/null || true)
  if echo "$create_user" | grep -q '"result"'; then
    FULL_DB_USER=$(echo "$create_user" | python3 -c "
import sys, json
try:
    d = json.load(sys.stdin)
    r = d.get('result') or {}
    data = r.get('data')
    if isinstance(data, list) and data:
        print(data[0].get('name', '$DB_USER'))
    elif isinstance(data, dict):
        print(data.get('name', '$DB_USER'))
    else:
        print('$DB_USER')
except Exception:
    print('$DB_USER')
" 2>/dev/null) || FULL_DB_USER="$DB_USER"
    echo "   User: $FULL_DB_USER"
  else
    echo "   Create user response: $create_user"
  fi

  priv=$(curl -sk -u "${CPANEL_USER}:${AUTH}" "${BASE}/Mysql/set_privileges_on_database?database=${FULL_DB_NAME}&user=${FULL_DB_USER}&privileges=ALL%20PRIVILEGES" 2>/dev/null || true)
  if echo "$priv" | grep -q '"result"'; then
    echo "   Privileges set."
  else
    echo "   Set privileges response: $priv"
  fi
  echo ""
else
  if [[ "$SKIP_DB_CREATE" == "1" ]]; then
    echo "2. Skipping DB create (SKIP_DB_CREATE=1). Using DB_NAME/DB_USER from config."
    FULL_DB_NAME="$DB_NAME"
    FULL_DB_USER="$DB_USER"
  fi
  echo ""
fi

# --- 3. Build .env content ---
ENV_CONTENT="DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=${FULL_DB_NAME}
DB_USERNAME=${FULL_DB_USER}
DB_PASSWORD=${DB_PASS}
APP_ENV=production
APP_DEBUG=false
APP_URL=${DOMAIN}
JWT_SECRET=${JWT_SECRET}
CORS_ALLOWED_ORIGINS=${DOMAIN}
"
echo "$ENV_CONTENT" > cpanel-deploy/api/.env.generated

# --- 4. Upload via FTP ---
echo "3. Uploading files via FTP..."
DEPLOY_DIR="cpanel-deploy"
FTP_URL="ftp://${FTP_HOST}/public_html"
# Escape special characters in password for URL
FTP_PASS_ESC=$(echo "$FTP_PASS" | sed 's/%/%25/g; s/@/%40/g; s/:/%3A/g; s/#/%23/g')
FTP_AUTH="${FTP_USER}:${FTP_PASS_ESC}"

upload_file() {
  local src="$1"
  local dest="$2"
  curl -sk --ftp-create-dirs -T "$src" "ftp://${FTP_HOST}/${dest}" --user "${FTP_USER}:${FTP_PASS}" 2>/dev/null && echo "   $dest" || true
}

# Upload all files (skip .env.generated)
count=0
while IFS= read -r -d '' f; do
  [[ "$f" == *".env.generated" ]] && continue
  rel="${f#$DEPLOY_DIR/}"
  dest="public_html/$rel"
  if curl -sk --ftp-create-dirs -T "$f" "ftp://${FTP_HOST}/${dest}" --user "${FTP_USER}:${FTP_PASS}" 2>/dev/null; then
    count=$((count + 1))
    [[ $((count % 20)) -eq 0 ]] && echo "   uploaded $count files..."
  fi
done < <(find "$DEPLOY_DIR" -type f -print0 2>/dev/null)
echo "   Total: $count files"

# Upload .env (overwrite api/.env on server)
curl -sk --ftp-create-dirs -T "cpanel-deploy/api/.env.generated" "ftp://${FTP_HOST}/public_html/api/.env" --user "${FTP_USER}:${FTP_PASS}" 2>/dev/null && echo "   api/.env uploaded" || echo "   Warning: could not upload .env (upload it manually)"

# Clean up local generated .env
rm -f cpanel-deploy/api/.env.generated

echo "   Upload complete."
echo ""

# --- 5. Run database import ---
if [[ "$SKIP_DB_IMPORT" != "1" ]]; then
  echo "4. Running database import..."
  IMPORT_URL="${DOMAIN}/run_import_once.php"
  resp=$(curl -sk "$IMPORT_URL" 2>/dev/null || true)
  if echo "$resp" | grep -q '"ok":true'; then
    echo "   Schema imported successfully."
  else
    echo "   Import response: $resp"
    echo "   If you see connection errors, check api/.env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)."
    echo "   You can also import manually: phpMyAdmin → Import → api/database/schema.sql"
  fi
  echo ""
  echo "5. One-time importer (run_import_once.php) was run."
  echo "   For security, delete it in cPanel File Manager: public_html/run_import_once.php"
else
  echo "4. Skipping DB import (SKIP_DB_IMPORT=1). Import api/database/schema.sql via phpMyAdmin if needed."
fi

echo ""
echo "=========================================="
echo "Deployment complete"
echo "=========================================="
echo "Frontend:  $DOMAIN"
echo "API health: $DOMAIN/api/health"
echo ""
echo "If the app does not load:"
echo "  - Check PHP version in cPanel (8.1+), enable PDO, PDO_MySQL, mbstring, OpenSSL, JSON"
echo "  - Ensure api/.env has correct DB_DATABASE and DB_USERNAME (with cPanel prefix if any)"
echo ""
