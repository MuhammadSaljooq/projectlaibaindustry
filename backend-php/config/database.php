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
            // .env file not found or not readable, use defaults
        }
    }
}

// Fallback: Use direct configuration if .env not available
// You can define these constants in a separate config file for cPanel
if (!defined('DB_HOST')) {
    define('DB_HOST', $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost');
    define('DB_PORT', $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306');
    define('DB_DATABASE', $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: 'inventory_sales_db');
    define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: 'root');
    define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '');
}

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $host = defined('DB_HOST') ? DB_HOST : ($_ENV['DB_HOST'] ?? 'localhost');
            $port = defined('DB_PORT') ? DB_PORT : ($_ENV['DB_PORT'] ?? '3306');
            $dbname = defined('DB_DATABASE') ? DB_DATABASE : ($_ENV['DB_DATABASE'] ?? 'inventory_sales_db');
            $username = defined('DB_USERNAME') ? DB_USERNAME : ($_ENV['DB_USERNAME'] ?? 'root');
            $password = defined('DB_PASSWORD') ? DB_PASSWORD : ($_ENV['DB_PASSWORD'] ?? '');
            
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed']));
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Get database connection
function getDB() {
    return Database::getInstance()->getConnection();
}
