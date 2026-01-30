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

// Handle special routes first
if (isset($pathParts[2])) {
    // /api/currencies/active
    if ($pathParts[2] === 'active') {
        try {
            $stmt = $pdo->query("
                SELECT * FROM currencies 
                WHERE is_active = 1 
                ORDER BY is_default DESC, code ASC
            ");
            jsonResponse($stmt->fetchAll());
        } catch (PDOException $e) {
            errorResponse('Failed to fetch active currencies', 500);
        }
        exit;
    }
    
    // /api/currencies/default
    if ($pathParts[2] === 'default') {
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM currencies 
                WHERE is_default = 1 AND is_active = 1 
                LIMIT 1
            ");
            $stmt->execute();
            $currency = $stmt->fetch();
            
            if (!$currency) {
                errorResponse('No default currency found', 404);
            }
            
            jsonResponse($currency);
        } catch (PDOException $e) {
            errorResponse('Failed to fetch default currency', 500);
        }
        exit;
    }
    
    // /api/currencies/exchange-rate/:fromCode/:toCode
    if ($pathParts[2] === 'exchange-rate' && isset($pathParts[3]) && isset($pathParts[4])) {
        $fromCode = strtoupper($pathParts[3]);
        $toCode = strtoupper($pathParts[4]);
        
        if ($fromCode === $toCode) {
            jsonResponse(['rate' => '1']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("SELECT id FROM currencies WHERE code = ?");
            $stmt->execute([$fromCode]);
            $fromCurrency = $stmt->fetch();
            
            $stmt->execute([$toCode]);
            $toCurrency = $stmt->fetch();
            
            if (!$fromCurrency || !$toCurrency) {
                errorResponse('Currency not found', 404);
            }
            
            $stmt = $pdo->prepare("
                SELECT * FROM exchange_rates 
                WHERE from_currency_id = ? AND to_currency_id = ?
                ORDER BY effective_date DESC 
                LIMIT 1
            ");
            $stmt->execute([$fromCurrency['id'], $toCurrency['id']]);
            $exchangeRate = $stmt->fetch();
            
            if (!$exchangeRate) {
                errorResponse('Exchange rate not found', 404);
            }
            
            jsonResponse([
                'rate' => $exchangeRate['rate'],
                'effectiveDate' => $exchangeRate['effective_date'],
                'fromCurrency' => $fromCode,
                'toCurrency' => $toCode
            ]);
        } catch (PDOException $e) {
            errorResponse('Failed to fetch exchange rate', 500);
        }
        exit;
    }
    
    // /api/currencies/exchange-rates/all
    if ($pathParts[2] === 'exchange-rates' && isset($pathParts[3]) && $pathParts[3] === 'all') {
        try {
            $stmt = $pdo->query("
                SELECT er.*, 
                       fc.code as from_code, fc.name as from_name,
                       tc.code as to_code, tc.name as to_name
                FROM exchange_rates er
                LEFT JOIN currencies fc ON er.from_currency_id = fc.id
                LEFT JOIN currencies tc ON er.to_currency_id = tc.id
                ORDER BY er.effective_date DESC
            ");
            $rates = $stmt->fetchAll();
            
            foreach ($rates as &$rate) {
                $rate['fromCurrency'] = ['code' => $rate['from_code'], 'name' => $rate['from_name']];
                $rate['toCurrency'] = ['code' => $rate['to_code'], 'name' => $rate['to_name']];
            }
            
            jsonResponse($rates);
        } catch (PDOException $e) {
            errorResponse('Failed to fetch exchange rates', 500);
        }
        exit;
    }
    
    // /api/currencies/exchange-rate (POST)
    if ($pathParts[2] === 'exchange-rate' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $errors = validateAll([
            'fromCurrencyId' => ['required', 'integer'],
            'toCurrencyId' => ['required', 'integer'],
            'rate' => ['required', 'numeric']
        ], $data);
        
        if ($errors) {
            errorResponse('Validation failed', 400, $errors);
        }
        
        if ($data['fromCurrencyId'] == $data['toCurrencyId']) {
            errorResponse('From and to currencies cannot be the same', 400);
        }
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO exchange_rates (from_currency_id, to_currency_id, rate, effective_date)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                intval($data['fromCurrencyId']),
                intval($data['toCurrencyId']),
                floatval($data['rate']),
                $data['effectiveDate'] ?? date('Y-m-d H:i:s')
            ]);
            
            $rateId = $pdo->lastInsertId();
            $stmt = $pdo->prepare("
                SELECT er.*, 
                       fc.code as from_code, fc.name as from_name,
                       tc.code as to_code, tc.name as to_name
                FROM exchange_rates er
                LEFT JOIN currencies fc ON er.from_currency_id = fc.id
                LEFT JOIN currencies tc ON er.to_currency_id = tc.id
                WHERE er.id = ?
            ");
            $stmt->execute([$rateId]);
            $rate = $stmt->fetch();
            
            $rate['fromCurrency'] = ['code' => $rate['from_code'], 'name' => $rate['from_name']];
            $rate['toCurrency'] = ['code' => $rate['to_code'], 'name' => $rate['to_name']];
            
            jsonResponse($rate, 201);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                errorResponse('Exchange rate for this date already exists', 400);
            }
            errorResponse('Failed to create exchange rate', 500);
        }
        exit;
    }
}

// Handle /api/currencies/:id
if (isset($pathParts[2]) && is_numeric($pathParts[2])) {
    $id = intval($pathParts[2]);
    
    if ($method === 'GET') {
        try {
            $stmt = $pdo->prepare("SELECT * FROM currencies WHERE id = ?");
            $stmt->execute([$id]);
            $currency = $stmt->fetch();
            
            if (!$currency) {
                errorResponse('Currency not found', 404);
            }
            
            // Get latest exchange rates
            $stmt = $pdo->prepare("
                SELECT er.*, tc.code as to_code, tc.name as to_name
                FROM exchange_rates er
                LEFT JOIN currencies tc ON er.to_currency_id = tc.id
                WHERE er.from_currency_id = ?
                ORDER BY er.effective_date DESC
                LIMIT 1
            ");
            $stmt->execute([$id]);
            $rate = $stmt->fetch();
            
            if ($rate) {
                $currency['exchangeRates'] = [[
                    'id' => $rate['id'],
                    'rate' => $rate['rate'],
                    'effectiveDate' => $rate['effective_date'],
                    'toCurrency' => ['code' => $rate['to_code'], 'name' => $rate['to_name']]
                ]];
            }
            
            jsonResponse($currency);
        } catch (PDOException $e) {
            errorResponse('Failed to fetch currency', 500);
        }
    }
    
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            // Check if currency exists
            $stmt = $pdo->prepare("SELECT id FROM currencies WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                errorResponse('Currency not found', 404);
            }
            
            // If setting as default, unset other defaults
            if (isset($data['isDefault']) && $data['isDefault']) {
                $stmt = $pdo->prepare("UPDATE currencies SET is_default = 0 WHERE id != ?");
                $stmt->execute([$id]);
            }
            
            $updates = [];
            $params = [];
            
            if (isset($data['code'])) {
                $updates[] = "code = ?";
                $params[] = strtoupper($data['code']);
            }
            if (isset($data['name'])) {
                $updates[] = "name = ?";
                $params[] = $data['name'];
            }
            if (isset($data['symbol'])) {
                $updates[] = "symbol = ?";
                $params[] = $data['symbol'];
            }
            if (isset($data['isDefault'])) {
                $updates[] = "is_default = ?";
                $params[] = $data['isDefault'] ? 1 : 0;
            }
            if (isset($data['isActive'])) {
                $updates[] = "is_active = ?";
                $params[] = $data['isActive'] ? 1 : 0;
            }
            if (isset($data['decimalPlaces'])) {
                $updates[] = "decimal_places = ?";
                $params[] = intval($data['decimalPlaces']);
            }
            
            if (empty($updates)) {
                errorResponse('No fields to update', 400);
            }
            
            $params[] = $id;
            $sql = "UPDATE currencies SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $stmt = $pdo->prepare("SELECT * FROM currencies WHERE id = ?");
            $stmt->execute([$id]);
            $currency = $stmt->fetch();
            
            jsonResponse($currency);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                errorResponse('Currency code already exists', 400);
            }
            errorResponse('Failed to update currency', 500);
        }
    }
    
    elseif ($method === 'DELETE') {
        try {
            // Check if currency is in use
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE currency_id = ?");
            $stmt->execute([$id]);
            $products = $stmt->fetch();
            
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM sales WHERE currency_id = ?");
            $stmt->execute([$id]);
            $sales = $stmt->fetch();
            
            if (intval($products['count']) > 0 || intval($sales['count']) > 0) {
                errorResponse('Cannot delete currency that is in use. Deactivate it instead.', 400);
            }
            
            $stmt = $pdo->prepare("DELETE FROM currencies WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                errorResponse('Currency not found', 404);
            }
            
            successResponse('Currency deleted successfully');
        } catch (PDOException $e) {
            errorResponse('Failed to delete currency', 500);
        }
    }
}

// Handle /api/currencies (GET all, POST create)
else {
    if ($method === 'GET') {
        try {
            $stmt = $pdo->query("
                SELECT * FROM currencies 
                ORDER BY is_default DESC, code ASC
            ");
            jsonResponse($stmt->fetchAll());
        } catch (PDOException $e) {
            errorResponse('Failed to fetch currencies', 500);
        }
    }
    
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $errors = validateAll([
            'code' => ['required', 'string'],
            'name' => ['required', 'string'],
            'symbol' => ['required', 'string']
        ], $data);
        
        if ($errors) {
            errorResponse('Validation failed', 400, $errors);
        }
        
        if (strlen($data['code']) !== 3) {
            errorResponse('Currency code must be 3 characters', 400);
        }
        
        try {
            // If setting as default, unset other defaults
            if (isset($data['isDefault']) && $data['isDefault']) {
                $stmt = $pdo->prepare("UPDATE currencies SET is_default = 0");
                $stmt->execute();
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO currencies (code, name, symbol, is_default, is_active, decimal_places)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                strtoupper($data['code']),
                $data['name'],
                $data['symbol'],
                isset($data['isDefault']) && $data['isDefault'] ? 1 : 0,
                isset($data['isActive']) ? ($data['isActive'] ? 1 : 0) : 1,
                intval($data['decimalPlaces'] ?? 2)
            ]);
            
            $currencyId = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT * FROM currencies WHERE id = ?");
            $stmt->execute([$currencyId]);
            $currency = $stmt->fetch();
            
            jsonResponse($currency, 201);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                errorResponse('Currency code already exists', 400);
            }
            errorResponse('Failed to create currency', 500);
        }
    }
}
