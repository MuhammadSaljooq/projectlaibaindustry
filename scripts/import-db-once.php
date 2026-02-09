<?php
/**
 * One-time database import. Run from site root: https://yourdomain.com/run_import_once.php
 * DELETE this file after use.
 */
header('Content-Type: application/json');
$envFile = __DIR__ . '/api/.env';
$schemaFile = __DIR__ . '/api/database/schema.sql';

if (!is_readable($envFile)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'api/.env not found']);
    exit;
}
if (!is_readable($schemaFile)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'api/database/schema.sql not found']);
    exit;
}

$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (preg_match('/^([A-Za-z_][A-Za-z0-9_]*)=(.*)$/', $line, $m)) {
        $env[$m[1]] = trim($m[2], " \t\"'");
    }
}

$host = $env['DB_HOST'] ?? 'localhost';
$port = $env['DB_PORT'] ?? '3306';
$dbname = $env['DB_DATABASE'] ?? '';
$user = $env['DB_USERNAME'] ?? '';
$pass = $env['DB_PASSWORD'] ?? '';

if (!$dbname || !$user) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'DB_DATABASE or DB_USERNAME missing in .env']);
    exit;
}

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Connection: ' . $e->getMessage()]);
    exit;
}

$sql = file_get_contents($schemaFile);
if ($sql === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not read schema.sql']);
    exit;
}

try {
    $pdo->exec($sql);
    echo json_encode(['ok' => true, 'message' => 'Schema imported. DELETE run_import_once.php now.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
