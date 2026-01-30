<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/validation.php';
require_once __DIR__ . '/../middleware/cors.php';

handleCORS();

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Handle /api/tax/:id (PUT)
if (isset($pathParts[2]) && is_numeric($pathParts[2])) {
    $id = intval($pathParts[2]);
    
    if ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $errors = validateAll([
            'defaultRate' => ['required', 'numeric']
        ], $data);
        
        if ($errors) {
            errorResponse('Validation failed', 400, $errors);
        }
        
        $rate = floatval($data['defaultRate']);
        if ($rate < 0 || $rate > 100) {
            errorResponse('Tax rate must be between 0 and 100', 400);
        }
        
        try {
            // Check if tax setting exists
            $stmt = $pdo->prepare("SELECT id FROM tax_settings WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                errorResponse('Tax setting not found', 404);
            }
            
            $stmt = $pdo->prepare("
                UPDATE tax_settings 
                SET default_rate = ?, description = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $rate,
                $data['description'] ?? null,
                $id
            ]);
            
            $stmt = $pdo->prepare("SELECT * FROM tax_settings WHERE id = ?");
            $stmt->execute([$id]);
            $taxSetting = $stmt->fetch();
            
            jsonResponse($taxSetting);
        } catch (PDOException $e) {
            errorResponse('Failed to update tax setting', 500);
        }
    }
}

// Handle /api/tax (GET - get or create default)
else {
    if ($method === 'GET') {
        try {
            $stmt = $pdo->query("SELECT * FROM tax_settings LIMIT 1");
            $taxSetting = $stmt->fetch();
            
            if (!$taxSetting) {
                // Create default tax setting
                $stmt = $pdo->prepare("
                    INSERT INTO tax_settings (default_rate, description)
                    VALUES (0, 'Default tax rate')
                ");
                $stmt->execute();
                
                $taxSettingId = $pdo->lastInsertId();
                $stmt = $pdo->prepare("SELECT * FROM tax_settings WHERE id = ?");
                $stmt->execute([$taxSettingId]);
                $taxSetting = $stmt->fetch();
            }
            
            jsonResponse($taxSetting);
        } catch (PDOException $e) {
            errorResponse('Failed to fetch tax settings', 500);
        }
    }
}
