#!/bin/bash

# 🆓 Free Hosting Deployment Helper Script
# This script helps prepare your application for free hosting deployment

set -e

echo "🚀 Free Hosting Deployment Helper"
echo "=================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if we're in the project root
if [ ! -d "frontend-vue" ] || [ ! -d "backend-php" ]; then
    echo -e "${RED}❌ Error: Please run this script from the project root directory${NC}"
    exit 1
fi

# Get deployment URL
echo -e "${YELLOW}Enter your free hosting URL (e.g., https://yoursite.infinityfreeapp.com):${NC}"
read -r DEPLOY_URL

if [ -z "$DEPLOY_URL" ]; then
    echo -e "${RED}❌ Error: URL is required${NC}"
    exit 1
fi

# Remove trailing slash
DEPLOY_URL="${DEPLOY_URL%/}"

echo ""
echo -e "${GREEN}📦 Step 1: Building Frontend...${NC}"
cd frontend-vue

# Create .env.production if it doesn't exist
if [ ! -f ".env.production" ]; then
    echo "VITE_API_URL=${DEPLOY_URL}/api" > .env.production
    echo -e "${GREEN}✅ Created .env.production${NC}"
else
    # Update existing .env.production
    if grep -q "VITE_API_URL" .env.production; then
        sed -i.bak "s|VITE_API_URL=.*|VITE_API_URL=${DEPLOY_URL}/api|" .env.production
        echo -e "${GREEN}✅ Updated .env.production${NC}"
    else
        echo "VITE_API_URL=${DEPLOY_URL}/api" >> .env.production
        echo -e "${GREEN}✅ Added VITE_API_URL to .env.production${NC}"
    fi
fi

# Install dependencies if needed
if [ ! -d "node_modules" ]; then
    echo -e "${YELLOW}Installing dependencies...${NC}"
    npm install
fi

# Build frontend
echo -e "${YELLOW}Building frontend for production...${NC}"
npm run build

if [ ! -d "dist" ]; then
    echo -e "${RED}❌ Error: Build failed - dist folder not found${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Frontend built successfully${NC}"
cd ..

# Create deployment package
echo ""
echo -e "${GREEN}📦 Step 2: Creating deployment package...${NC}"

DEPLOY_DIR="free-hosting-deploy"
rm -rf "$DEPLOY_DIR"
mkdir -p "$DEPLOY_DIR"

# Copy frontend build
echo -e "${YELLOW}Copying frontend files...${NC}"
cp -r frontend-vue/dist/* "$DEPLOY_DIR/"

# Copy backend files
echo -e "${YELLOW}Copying backend files...${NC}"
mkdir -p "$DEPLOY_DIR/api"
cp -r backend-php/* "$DEPLOY_DIR/api/"

# Create frontend .htaccess
echo -e "${YELLOW}Creating frontend .htaccess...${NC}"
cat > "$DEPLOY_DIR/.htaccess" << 'EOF'
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  
  # API requests
  RewriteCond %{REQUEST_URI} ^/api/
  RewriteRule ^api/(.*)$ api/index.php [L,QSA]
  
  # Frontend SPA routing
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^(.*)$ index.html [L]
</IfModule>
EOF

# Create backend .htaccess if it doesn't exist
if [ ! -f "$DEPLOY_DIR/api/.htaccess" ]; then
    echo -e "${YELLOW}Creating backend .htaccess...${NC}"
    cat > "$DEPLOY_DIR/api/.htaccess" << 'EOF'
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
AddDefaultCharset UTF-8
EOF
fi

# Create .env.example for backend
echo -e "${YELLOW}Creating backend .env.example...${NC}"
cat > "$DEPLOY_DIR/api/.env.example" << EOF
# Database Configuration
# Get these from your hosting provider's MySQL Databases section
DB_HOST=sqlXXX.infinityfree.com
DB_PORT=3306
DB_DATABASE=epiz_XXXXXX_inventory_sales
DB_USERNAME=epiz_XXXXXX_dbuser
DB_PASSWORD=your_password_here

# Application Configuration
APP_ENV=production
APP_DEBUG=false
APP_URL=${DEPLOY_URL}

# Security
# Generate a secure secret: openssl rand -base64 32
JWT_SECRET=your-random-secret-key-here-min-32-characters

# CORS
CORS_ALLOWED_ORIGINS=${DEPLOY_URL}
EOF

# Create deployment instructions
echo -e "${YELLOW}Creating deployment instructions...${NC}"
cat > "$DEPLOY_DIR/DEPLOY_INSTRUCTIONS.txt" << EOF
🚀 Free Hosting Deployment Instructions
========================================

📋 Prerequisites:
- Free hosting account (InfinityFree, 000webhost, etc.)
- Database created in hosting control panel
- FTP access or File Manager access

📦 Step 1: Database Setup
--------------------------
1. Go to your hosting control panel → MySQL Databases
2. Create a new database (note the name, username, password)
3. Go to phpMyAdmin → Select your database → Import
4. Import: api/database/schema.sql

📦 Step 2: Configure Backend
-----------------------------
1. In File Manager, navigate to: api/
2. Copy .env.example to .env
3. Edit .env and update with YOUR database credentials:
   - DB_HOST (usually sqlXXX.infinityfree.com or localhost)
   - DB_DATABASE (your database name)
   - DB_USERNAME (your database username)
   - DB_PASSWORD (your database password)
   - APP_URL (your website URL: ${DEPLOY_URL})
   - JWT_SECRET (generate with: openssl rand -base64 32)

📦 Step 3: Upload Files
-----------------------
Upload ALL files to your hosting root (htdocs/ or public_html/):

Option A: Using File Manager
- Upload all files maintaining folder structure
- Frontend files go to root
- Backend files go to api/ folder

Option B: Using FTP
- Connect via FTP client
- Upload to htdocs/ (or public_html/)
- Maintain folder structure

📦 Step 4: File Permissions
----------------------------
- Files: 644
- Folders: 755
- .htaccess files should be readable

📦 Step 5: Test
---------------
1. Test API: ${DEPLOY_URL}/api/health
   Should return: {"status":"ok","message":"Server is running"}

2. Test Frontend: ${DEPLOY_URL}
   Should load your application

📦 Step 6: Verify
-----------------
- Create a product
- Create a sale
- Check data persists in database

🆘 Troubleshooting
-------------------
See DEPLOY_FREE_HOSTING.md for detailed troubleshooting guide.

✅ Your app will be live at: ${DEPLOY_URL}
EOF

echo ""
echo -e "${GREEN}✅ Deployment package created in: $DEPLOY_DIR/${NC}"
echo ""
echo -e "${YELLOW}📝 Next Steps:${NC}"
echo "1. Review: $DEPLOY_DIR/DEPLOY_INSTRUCTIONS.txt"
echo "2. Upload contents of $DEPLOY_DIR/ to your hosting"
echo "3. Configure database and .env file"
echo "4. Test your deployment"
echo ""
echo -e "${GREEN}🎉 Ready for deployment!${NC}"
