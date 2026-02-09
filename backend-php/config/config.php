<?php
// Try to load Composer autoloader (if using Composer)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Try to load .env file if dotenv is available
    if (class_exists('Dotenv\Dotenv')) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
        } catch (Exception $e) {
            // .env file not found, use defaults
        }
    }
}

// Application configuration
// Priority: .env file > getenv() > defaults
define('APP_ENV', $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN));
define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: 'your-secret-key-change-this-in-production');
define('CORS_ALLOWED_ORIGINS', $_ENV['CORS_ALLOWED_ORIGINS'] ?? getenv('CORS_ALLOWED_ORIGINS') ?: 'https://yourdomain.com');

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set('UTC');

// Performance and timeout settings
ini_set('max_execution_time', '30'); // 30 second script timeout
ini_set('max_input_time', '30'); // 30 second input timeout
ini_set('default_socket_timeout', '10'); // 10 second socket timeout
ini_set('memory_limit', '256M'); // Increase memory limit if needed
