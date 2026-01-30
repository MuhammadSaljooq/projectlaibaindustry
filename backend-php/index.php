<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/middleware/cors.php';
require_once __DIR__ . '/utils/response.php';

// Handle CORS
handleCORS();

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Health check endpoint
if ($path === '/api/health' || $path === '/api/health/') {
    jsonResponse(['status' => 'ok', 'message' => 'Server is running']);
}

// Get the request path
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

// Remove query string
$requestUri = strtok($requestUri, '?');

// Handle different base paths (for cPanel deployment)
$basePath = dirname($scriptName);
if ($basePath === '/api' || $basePath === '/') {
    $basePath = '';
} else {
    $basePath = str_replace('/index.php', '', $basePath);
}

// Remove base path from request URI
if ($basePath && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

$path = parse_url($requestUri, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Route to appropriate API file
if (count($pathParts) >= 1 && $pathParts[0] === 'api') {
    $route = $pathParts[1] ?? '';
    
    switch ($route) {
        case 'products':
            require_once __DIR__ . '/api/products.php';
            break;
        case 'categories':
            require_once __DIR__ . '/api/categories.php';
            break;
        case 'sales':
            require_once __DIR__ . '/api/sales.php';
            break;
        case 'receivables':
            require_once __DIR__ . '/api/receivables.php';
            break;
        case 'tax':
            require_once __DIR__ . '/api/tax.php';
            break;
        case 'currencies':
            require_once __DIR__ . '/api/currencies.php';
            break;
        case 'analytics':
            require_once __DIR__ . '/api/analytics.php';
            break;
        case 'health':
            jsonResponse(['status' => 'ok', 'message' => 'Server is running']);
            break;
        default:
            errorResponse('Route not found', 404);
    }
} elseif ($path === '/api' || $path === '/api/') {
    // API root
    jsonResponse(['message' => 'Inventory & Sales Management API']);
} elseif (count($pathParts) >= 1 && $pathParts[0] !== 'api') {
    // Direct route access (for cPanel when files are in /api/)
    $route = $pathParts[0];
    
    switch ($route) {
        case 'products':
            require_once __DIR__ . '/api/products.php';
            break;
        case 'categories':
            require_once __DIR__ . '/api/categories.php';
            break;
        case 'sales':
            require_once __DIR__ . '/api/sales.php';
            break;
        case 'receivables':
            require_once __DIR__ . '/api/receivables.php';
            break;
        case 'tax':
            require_once __DIR__ . '/api/tax.php';
            break;
        case 'currencies':
            require_once __DIR__ . '/api/currencies.php';
            break;
        case 'analytics':
            require_once __DIR__ . '/api/analytics.php';
            break;
        case 'health':
            jsonResponse(['status' => 'ok', 'message' => 'Server is running']);
            break;
        default:
            errorResponse('Route not found', 404);
    }
} else {
    errorResponse('Route not found', 404);
}
