# Vue.js Frontend - Inventory & Sales Management System

This is the Vue.js version of the frontend application.

## Prerequisites

- Node.js (v18 or higher)
- npm or yarn

## Installation

```bash
cd frontend-vue
npm install
```

## Development

```bash
npm run dev
```

The app will be available at `http://localhost:5173`

## Build

```bash
npm run build
```

## Project Structure

```
frontend-vue/
├── src/
│   ├── components/     # Vue components
│   ├── pages/         # Page components
│   ├── stores/        # Pinia stores
│   ├── router/        # Vue Router configuration
│   ├── utils/         # Utility functions
│   ├── i18n/          # Internationalization
│   ├── App.vue        # Root component
│   └── main.js        # Entry point
├── index.html
├── vite.config.js
└── package.json
```

## Key Differences from React Version

- **Framework**: Vue 3 with Composition API
- **State Management**: Pinia (instead of Context API)
- **Routing**: Vue Router (instead of React Router)
- **Icons**: lucide-vue-next (instead of lucide-react)
- **Internationalization**: vue-i18n (instead of react-i18next)

## Features

- ✅ All pages converted to Vue
- ✅ Pinia stores for state management
- ✅ Vue Router for navigation
- ✅ Vue i18n for internationalization
- ✅ Tailwind CSS styling
- ✅ Responsive design
- ✅ Excel-like data entry (Sales, Receivables)

## Environment Variables

Create a `.env` file:

```
VITE_API_URL=http://localhost:8000/api
```

## Notes

- The backend API remains the same (PHP/MySQL)
- All API endpoints are compatible
- Same functionality as React version
