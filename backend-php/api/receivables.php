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

// Handle /api/receivables/:id
if (isset($pathParts[2]) && is_numeric($pathParts[2])) {
    $id = intval($pathParts[2]);
    
    if ($method === 'GET') {
        try {
            $stmt = $pdo->prepare("SELECT * FROM receivables WHERE id = ?");
            $stmt->execute([$id]);
            $receivable = $stmt->fetch();
            
            if (!$receivable) {
                errorResponse('Receivable not found', 404);
            }
            
            jsonResponse($receivable);
        } catch (PDOException $e) {
            errorResponse('Failed to fetch receivable', 500);
        }
    }
    
    elseif ($method === 'DELETE') {
        try {
            $stmt = $pdo->prepare("DELETE FROM receivables WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                errorResponse('Receivable not found', 404);
            }
            
            successResponse('Receivable deleted successfully');
        } catch (PDOException $e) {
            errorResponse('Failed to delete receivable', 500);
        }
    }
}

// Handle /api/receivables (GET all, POST create)
else {
    if ($method === 'GET') {
        $startDate = $_GET['startDate'] ?? null;
        $endDate = $_GET['endDate'] ?? null;
        $customerName = $_GET['customerName'] ?? null;
        $customerCode = $_GET['customerCode'] ?? null;
        $invoiceNumber = $_GET['invoiceNumber'] ?? null;
        $search = $_GET['search'] ?? null;
        
        try {
            $sql = "SELECT * FROM receivables WHERE 1=1";
            $params = [];
            
            if ($startDate) {
                $sql .= " AND date >= ?";
                $params[] = $startDate;
            }
            if ($endDate) {
                $sql .= " AND date <= ?";
                $params[] = $endDate . ' 23:59:59';
            }
            if ($customerName) {
                $sql .= " AND customer_name LIKE ?";
                $params[] = "%{$customerName}%";
            }
            if ($customerCode) {
                $sql .= " AND customer_code LIKE ?";
                $params[] = "%{$customerCode}%";
            }
            if ($invoiceNumber) {
                $sql .= " AND invoice_number LIKE ?";
                $params[] = "%{$invoiceNumber}%";
            }
            if ($search) {
                $sql .= " AND (customer_name LIKE ? OR customer_code LIKE ? OR invoice_number LIKE ?)";
                $searchTerm = "%{$search}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $sql .= " ORDER BY date DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $receivables = $stmt->fetchAll();
            
            jsonResponse($receivables);
        } catch (PDOException $e) {
            errorResponse('Failed to fetch receivables', 500);
        }
    }
    
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $errors = validateAll([
            'receivables' => ['required', 'array']
        ], $data);
        
        if ($errors) {
            errorResponse('Validation failed', 400, $errors);
        }
        
        try {
            $receivables = $data['receivables'];
            $created = [];
            
            foreach ($receivables as $rec) {
                $errors = validateAll([
                    'amount' => ['required', 'numeric']
                ], $rec);
                
                if ($errors) {
                    continue; // Skip invalid entries
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO receivables (date, invoice_number, customer_name, customer_code, amount, received)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $rec['date'] ?? date('Y-m-d H:i:s'),
                    $rec['invoiceNumber'] ?? null,
                    $rec['customerName'] ?? null,
                    $rec['customerCode'] ?? null,
                    floatval($rec['amount']),
                    floatval($rec['received'] ?? 0)
                ]);
                
                $created[] = $pdo->lastInsertId();
            }
            
            successResponse("Created " . count($created) . " receivable(s)", ['count' => count($created)], 201);
        } catch (PDOException $e) {
            errorResponse('Failed to create receivables', 500);
        }
    }
}
