<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/validation.php';
require_once __DIR__ . '/../utils/products_helper.php';
require_once __DIR__ . '/../middleware/cors.php';

handleCORS();

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Helper function to calculate sale totals
function calculateSaleTotals($items, $taxRate, $discountAmount = 0, $discountType = 'fixed') {
    $subtotal = 0;
    $totalProfit = 0;
    
    foreach ($items as $item) {
        $itemTotal = floatval($item['sellingPrice']) * intval($item['quantity']);
        $subtotal += $itemTotal;
        $itemCost = floatval($item['costPrice']) * intval($item['quantity']);
        $totalProfit += ($itemTotal - $itemCost);
    }
    
    // Apply discount
    $finalDiscount = 0;
    if ($discountType === 'percentage') {
        $finalDiscount = ($subtotal * floatval($discountAmount)) / 100;
    } else {
        $finalDiscount = floatval($discountAmount);
    }
    
    $subtotalAfterDiscount = $subtotal - $finalDiscount;
    $taxAmount = ($subtotalAfterDiscount * floatval($taxRate)) / 100;
    $totalAmount = $subtotalAfterDiscount + $taxAmount;
    
    return [
        'subtotal' => $subtotal,
        'discountAmount' => $finalDiscount,
        'taxAmount' => $taxAmount,
        'totalAmount' => $totalAmount,
        'totalProfit' => $totalProfit
    ];
}

// Handle /api/sales/:id
if (isset($pathParts[2]) && is_numeric($pathParts[2])) {
    $id = intval($pathParts[2]);
    
    if ($method === 'GET') {
        try {
            $stmt = $pdo->prepare("
                SELECT s.*, 
                       c.code as currency_code, c.name as currency_name, c.symbol as currency_symbol
                FROM sales s
                LEFT JOIN currencies c ON s.currency_id = c.id
                WHERE s.id = ?
            ");
            $stmt->execute([$id]);
            $sale = $stmt->fetch();
            
            if (!$sale) {
                errorResponse('Sale not found', 404);
            }
            
            // Get sales items with products
            $stmt = $pdo->prepare("
                SELECT si.*, 
                       p.name as product_name, p.sku as product_sku,
                       cat.name as category_name
                FROM sales_items si
                LEFT JOIN products p ON si.product_id = p.id
                LEFT JOIN categories cat ON p.category_id = cat.id
                WHERE si.sale_id = ?
            ");
            $stmt->execute([$id]);
            $items = $stmt->fetchAll();
            
            foreach ($items as &$item) {
                $item['product'] = [
                    'id' => $item['product_id'],
                    'name' => $item['product_name'],
                    'sku' => $item['product_sku'],
                    'category' => ['name' => $item['category_name']]
                ];
            }
            
            $sale['salesItems'] = $items;
            if ($sale['currency_id']) {
                $sale['currency'] = [
                    'id' => $sale['currency_id'],
                    'code' => $sale['currency_code'],
                    'name' => $sale['currency_name'],
                    'symbol' => $sale['currency_symbol']
                ];
            }
            
            jsonResponse($sale);
        } catch (PDOException $e) {
            errorResponse('Failed to fetch sale', 500);
        }
    }
    
    elseif ($method === 'DELETE') {
        try {
            $pdo->beginTransaction();
            
            // Get sale with items
            $stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ?");
            $stmt->execute([$id]);
            $sale = $stmt->fetch();
            
            if (!$sale) {
                $pdo->rollBack();
                errorResponse('Sale not found', 404);
            }
            
            // Get sales items
            $stmt = $pdo->prepare("SELECT * FROM sales_items WHERE sale_id = ?");
            $stmt->execute([$id]);
            $items = $stmt->fetchAll();
            
            // Restore stock
            foreach ($items as $item) {
                $stmt = $pdo->prepare("
                    UPDATE products 
                    SET stock_quantity = stock_quantity + ? 
                    WHERE id = ?
                ");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Delete sale (cascade will delete sales items)
            $stmt = $pdo->prepare("DELETE FROM sales WHERE id = ?");
            $stmt->execute([$id]);
            
            $pdo->commit();
            successResponse('Sale deleted successfully');
        } catch (PDOException $e) {
            $pdo->rollBack();
            errorResponse('Failed to delete sale', 500);
        }
    }
}

// Handle /api/sales (GET all, POST create)
else {
    if ($method === 'GET') {
        $startDate = $_GET['startDate'] ?? null;
        $endDate = $_GET['endDate'] ?? null;
        $customerName = $_GET['customerName'] ?? null;
        $customerCode = $_GET['customerCode'] ?? null;
        $invoiceNumber = $_GET['invoiceNumber'] ?? null;
        $search = $_GET['search'] ?? null;
        
        try {
            $sql = "
                SELECT s.*, 
                       c.code as currency_code, c.name as currency_name
                FROM sales s
                LEFT JOIN currencies c ON s.currency_id = c.id
                WHERE 1=1
            ";
            $params = [];
            
            if ($startDate) {
                $sql .= " AND s.date >= ?";
                $params[] = $startDate;
            }
            if ($endDate) {
                $sql .= " AND s.date <= ?";
                $params[] = $endDate . ' 23:59:59';
            }
            if ($customerName) {
                $sql .= " AND s.customer_name LIKE ?";
                $params[] = "%{$customerName}%";
            }
            if ($customerCode) {
                $sql .= " AND s.customer_code LIKE ?";
                $params[] = "%{$customerCode}%";
            }
            if ($invoiceNumber) {
                $sql .= " AND s.invoice_number LIKE ?";
                $params[] = "%{$invoiceNumber}%";
            }
            if ($search) {
                $sql .= " AND (s.customer_name LIKE ? OR s.customer_code LIKE ? OR s.invoice_number LIKE ?)";
                $searchTerm = "%{$search}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $sql .= " ORDER BY s.date DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $sales = $stmt->fetchAll();
            
            // Get sales items for each sale
            foreach ($sales as &$sale) {
                $stmt = $pdo->prepare("
                    SELECT si.*, 
                           p.name as product_name, p.sku as product_sku,
                           cat.name as category_name
                    FROM sales_items si
                    LEFT JOIN products p ON si.product_id = p.id
                    LEFT JOIN categories cat ON p.category_id = cat.id
                    WHERE si.sale_id = ?
                ");
                $stmt->execute([$sale['id']]);
                $items = $stmt->fetchAll();
                
                foreach ($items as &$item) {
                    $item['product'] = [
                        'id' => $item['product_id'],
                        'name' => $item['product_name'],
                        'sku' => $item['product_sku'],
                        'category' => ['name' => $item['category_name']]
                    ];
                }
                
                $sale['salesItems'] = $items;
                if ($sale['currency_id']) {
                    $sale['currency'] = [
                        'id' => $sale['currency_id'],
                        'code' => $sale['currency_code'],
                        'name' => $sale['currency_name']
                    ];
                }
            }
            
            jsonResponse($sales);
        } catch (PDOException $e) {
            errorResponse('Failed to fetch sales', 500);
        }
    }
    
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $errors = validateAll([
            'items' => ['required', 'array'],
            'taxRate' => ['required', 'numeric']
        ], $data);
        
        if ($errors) {
            errorResponse('Validation failed', 400, $errors);
        }
        
        try {
            $pdo->beginTransaction();
            
            $date = $data['date'] ?? date('Y-m-d H:i:s');
            $customerCode = $data['customerCode'] ?? null;
            $customerName = $data['customerName'] ?? null;
            $invoiceNumber = $data['invoiceNumber'] ?? null;
            $items = $data['items'];
            $taxRate = floatval($data['taxRate']);
            $discountAmount = floatval($data['discountAmount'] ?? 0);
            $discountType = $data['discountType'] ?? 'fixed';
            
            // Process items - handle both productId and productName
            $salesItemsData = [];
            $productIds = [];
            
            foreach ($items as $item) {
                $product = null;
                
                // If productName is provided, find or create product
                if (isset($item['productName']) && !empty($item['productName'])) {
                    $product = findOrCreateProduct($pdo, $item['productName'], $item['sellingPrice'] ?? null);
                } 
                // If productId is provided, fetch product
                elseif (isset($item['productId'])) {
                    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                    $stmt->execute([intval($item['productId'])]);
                    $product = $stmt->fetch();
                    
                    if (!$product) {
                        throw new Exception("Product with ID {$item['productId']} not found");
                    }
                } else {
                    throw new Exception("Either productId or productName is required");
                }
                
                $quantity = intval($item['quantity']);
                $sellingPrice = floatval($item['sellingPrice']);
                
                // Check stock availability
                if (intval($product['stock_quantity']) < $quantity) {
                    throw new Exception("Insufficient stock for {$product['name']}. Available: {$product['stock_quantity']}, Requested: {$quantity}");
                }
                
                $costPrice = floatval($product['cost_price']);
                $profit = ($sellingPrice - $costPrice) * $quantity;
                $itemTax = ($sellingPrice * $quantity * $taxRate) / 100;
                
                $salesItemsData[] = [
                    'productId' => $product['id'],
                    'quantity' => $quantity,
                    'costPrice' => $costPrice,
                    'sellingPrice' => $sellingPrice,
                    'profit' => $profit,
                    'taxApplied' => $itemTax
                ];
                
                $productIds[] = $product['id'];
            }
            
            // Calculate totals
            $totals = calculateSaleTotals($salesItemsData, $taxRate, $discountAmount, $discountType);
            
            // Create sale
            $stmt = $pdo->prepare("
                INSERT INTO sales (date, customer_code, customer_name, invoice_number, subtotal, 
                                 discount_amount, tax_amount, total_amount, tax_rate)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $date,
                $customerCode,
                $customerName,
                $invoiceNumber,
                $totals['subtotal'],
                $totals['discountAmount'],
                $totals['taxAmount'],
                $totals['totalAmount'],
                $taxRate
            ]);
            
            $saleId = $pdo->lastInsertId();
            
            // Create sales items and update stock
            foreach ($salesItemsData as $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO sales_items (sale_id, product_id, quantity, cost_price, selling_price, profit, tax_applied)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $saleId,
                    $item['productId'],
                    $item['quantity'],
                    $item['costPrice'],
                    $item['sellingPrice'],
                    $item['profit'],
                    $item['taxApplied']
                ]);
                
                // Update stock
                $stmt = $pdo->prepare("
                    UPDATE products 
                    SET stock_quantity = stock_quantity - ? 
                    WHERE id = ?
                ");
                $stmt->execute([$item['quantity'], $item['productId']]);
            }
            
            $pdo->commit();
            
            // Fetch created sale with items
            $stmt = $pdo->prepare("
                SELECT s.*, 
                       c.code as currency_code, c.name as currency_name
                FROM sales s
                LEFT JOIN currencies c ON s.currency_id = c.id
                WHERE s.id = ?
            ");
            $stmt->execute([$saleId]);
            $sale = $stmt->fetch();
            
            // Get sales items
            $stmt = $pdo->prepare("
                SELECT si.*, 
                       p.name as product_name, p.sku as product_sku,
                       cat.name as category_name
                FROM sales_items si
                LEFT JOIN products p ON si.product_id = p.id
                LEFT JOIN categories cat ON p.category_id = cat.id
                WHERE si.sale_id = ?
            ");
            $stmt->execute([$saleId]);
            $items = $stmt->fetchAll();
            
            foreach ($items as &$item) {
                $item['product'] = [
                    'id' => $item['product_id'],
                    'name' => $item['product_name'],
                    'sku' => $item['product_sku'],
                    'category' => ['name' => $item['category_name']]
                ];
            }
            
            $sale['salesItems'] = $items;
            
            jsonResponse($sale, 201);
        } catch (Exception $e) {
            $pdo->rollBack();
            errorResponse($e->getMessage(), 400);
        } catch (PDOException $e) {
            $pdo->rollBack();
            errorResponse('Failed to create sale', 500);
        }
    }
}
