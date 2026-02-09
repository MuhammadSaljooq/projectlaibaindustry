# Inventory and Sales Management System

A comprehensive inventory and sales management system with an intuitive UI and reliable database.

## Tech Stack

### Frontend
- **Framework**: React (with Vite)
- **Styling**: Tailwind CSS
- **State Management**: React Context API
- **Form Handling**: React Hook Form with validation
- **Charts**: Recharts (for analytics visualization)

### Backend
- **Framework**: Node.js/Express (primary) OR PHP (alternative)
- **Database**: PostgreSQL (Node.js) OR MySQL/MariaDB (PHP)
- **Database Access**: Prisma (Node.js) OR PDO (PHP)
- **Package Manager**: npm (Node.js) OR Composer (PHP)

> **Note**: The project includes both Node.js/Express (in `backend/`) and PHP (in `backend-php/`) backends. Use Node.js for Render.com, PHP for traditional hosting.

## Project Structure

```
.
├── frontend/          # React frontend application
├── frontend-vue/      # Vue.js frontend application (recommended)
├── backend/           # Node.js/Express backend API (for Render.com)
├── backend-php/       # PHP backend API (for traditional hosting)
├── database/          # Database schema and migrations
└── README.md          # This file
```

## Prerequisites

- PHP 8.1 or higher
- MySQL 8.0 or higher
- Node.js (for frontend development)
- npm or yarn

## Quick Start

### Local Development

**Backend (Node.js - Recommended):**
```bash
cd backend
npm install
npm run dev
```

**Backend (PHP - Alternative):**
```bash
cd backend-php
php -S localhost:8000 -t .
```

**Frontend (React):**
```bash
cd frontend
npm install
npm run dev
```

**Frontend (Vue.js):**
```bash
cd frontend-vue
npm install
npm run dev
```

## Deployment

### Render.com (Recommended - Modern Cloud Platform)
For Render.com deployment (free tier available), see **`DEPLOY_RENDER.md`** for complete step-by-step instructions.

**Features:**
- ✅ Free tier for web services and PostgreSQL
- ✅ Automatic SSL certificates
- ✅ Git-based deployments
- ✅ Uses Node.js backend (already in repo)

### Free Hosting (PHP/MySQL)
For free hosting deployment (InfinityFree, 000webhost, etc.), see **`DEPLOY_FREE_HOSTING.md`** for complete step-by-step instructions.

**Quick Deploy:**
```bash
./DEPLOY_FREE_HOSTING.sh
```

### Paid Hosting (cPanel)
For cPanel deployment, see **`DEPLOY_CPANEL.md`** for complete instructions.

## Features

- ✅ Product inventory tracking
- ✅ Sales transactions with auto-calculations
- ✅ Receivables management
- ✅ Multi-currency support
- ✅ Multi-language support (i18n)
- ✅ Reports and analytics
- ✅ Tax management
- ✅ Category management

## Deployment (automatic)

Pushes to the `main` branch automatically deploy to **laibaindustrysialkot.com** (hoster.pk). See [DEPLOY_HOSTERPK.md](DEPLOY_HOSTERPK.md) for setup; after that, no manual upload is needed.

## License

MIT
