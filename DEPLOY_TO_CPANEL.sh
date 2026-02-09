#!/bin/bash

# cPanel Deployment Script
# This script prepares files for cPanel deployment

echo "=========================================="
echo "Preparing files for cPanel deployment..."
echo "=========================================="

# Create deployment directory
DEPLOY_DIR="cpanel-deploy"
rm -rf "$DEPLOY_DIR"
mkdir -p "$DEPLOY_DIR"

echo ""
echo "1. Preparing PHP Backend..."
mkdir -p "$DEPLOY_DIR/api"
cp -r backend-php/api "$DEPLOY_DIR/"
cp -r backend-php/config "$DEPLOY_DIR/api/"
cp -r backend-php/middleware "$DEPLOY_DIR/api/"
cp -r backend-php/utils "$DEPLOY_DIR/api/"
cp -r backend-php/database "$DEPLOY_DIR/api/"
cp backend-php/index.php "$DEPLOY_DIR/api/"

# Create .htaccess for API
cat > "$DEPLOY_DIR/api/.htaccess" << 'EOF'
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /api/
  
  # Route all requests to index.php
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^(.*)$ index.php [L,QSA]
</IfModule>

# Security: Prevent direct access to .env only (do not block .php)
<FilesMatch "^\.env">
  <IfModule mod_authz_core.c>
    Require all denied
  </IfModule>
</FilesMatch>
EOF

echo "✓ Backend files prepared"

echo ""
echo "2. Building Vue.js Frontend..."
cd frontend-vue
if [ -f "package.json" ]; then
  npm install
  npm run build
  cd ..
  
  echo "✓ Frontend built"
  
  # Copy frontend build
  if [ -d "frontend-vue/dist" ]; then
    cp -r frontend-vue/dist/* "$DEPLOY_DIR/"
    BUILD_ID="build-$(date -u +%Y%m%d-%H%M%SZ)"
    if [ -f "$DEPLOY_DIR/index.html" ]; then
      python3 -c "
import sys
bid = sys.argv[1]
path = sys.argv[2]
with open(path) as f: c = f.read()
with open(path, 'w') as f: f.write(c.replace('<head>', '<head>\n    <!-- ' + bid + ' -->', 1))
" "$BUILD_ID" "$DEPLOY_DIR/index.html" 2>/dev/null || true
    fi
    echo "✓ Frontend files copied ($BUILD_ID)"
  else
    echo "⚠ Frontend dist folder not found"
  fi
else
  echo "⚠ Frontend not found, skipping..."
  cd ..
fi

echo ""
echo "3. Creating .htaccess for Frontend..."
cat > "$DEPLOY_DIR/.htaccess" << 'EOF'
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  
  # Handle API requests
  RewriteCond %{REQUEST_URI} ^/api/
  RewriteRule ^api/(.*)$ api/index.php [L,QSA]
  
  # Handle frontend routes (SPA)
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^(.*)$ index.html [L]
</IfModule>

# Enable compression
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>

# Browser caching
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType image/jpg "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/gif "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType text/css "access plus 1 month"
  ExpiresByType application/javascript "access plus 1 month"
</IfModule>
EOF

echo "✓ .htaccess files created"

echo ""
echo "4. Creating environment template..."
cat > "$DEPLOY_DIR/api/.env.example" << 'EOF'
# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=yourusername_inventory
DB_USERNAME=yourusername_dbuser
DB_PASSWORD=your_password

# Application Configuration
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# JWT Secret
JWT_SECRET=your-secret-key-change-this-in-production

# CORS
CORS_ALLOWED_ORIGINS=https://yourdomain.com
EOF

echo "✓ Environment template created"

echo ""
echo "5. Creating deployment instructions..."
cat > "$DEPLOY_DIR/DEPLOY_INSTRUCTIONS.txt" << 'EOF'
CPANEL DEPLOYMENT INSTRUCTIONS
==============================

1. DATABASE SETUP:
   - Go to cPanel → MySQL Databases
   - Create database: yourusername_inventory
   - Create database user
   - Add user to database with ALL PRIVILEGES
   - Go to phpMyAdmin
   - Select your database
   - Import: api/database/schema.sql

2. UPLOAD FILES:
   - Upload ALL files from this folder to public_html/
   - Maintain folder structure

3. CONFIGURE ENVIRONMENT:
   - Rename api/.env.example to api/.env
   - Edit api/.env with your database credentials
   - Update APP_URL with your domain
   - Update CORS_ALLOWED_ORIGINS with your domain

4. SET PERMISSIONS:
   - Directories: 755
   - Files: 644
   - api/.env: 600

5. CONFIGURE PHP:
   - Go to cPanel → Select PHP Version
   - Select PHP 8.1 or higher
   - Enable: PDO, PDO_MySQL, OpenSSL, JSON, mbstring

6. TEST:
   - Visit: https://yourdomain.com/api/health
   - Should return: {"status":"ok","message":"Server is running"}
   - Visit: https://yourdomain.com
   - Should load the frontend

7. TROUBLESHOOTING:
   - Check cPanel Error Logs
   - Check PHP Error Logs
   - Verify .htaccess is working
   - Test API endpoints directly
EOF

echo "✓ Instructions created"

echo ""
echo "=========================================="
echo "Deployment package ready!"
echo "=========================================="
echo ""
echo "Location: $DEPLOY_DIR/"
echo ""
echo "Next steps:"
echo "1. Review files in $DEPLOY_DIR/"
echo "2. Upload to cPanel public_html/"
echo "3. Follow instructions in DEPLOY_INSTRUCTIONS.txt"
echo ""
echo "Files to upload:"
echo "  - All files from $DEPLOY_DIR/ to public_html/"
echo ""
