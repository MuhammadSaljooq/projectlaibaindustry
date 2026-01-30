<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/validation.php';
require_once __DIR__ . '/../middleware/cors.php';

handleCORS();

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

// Helper function to get or create default category
function getOrCreateDefaultCategory($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE name = ? LIMIT 1");
    $stmt->execute(['General']);
    $category = $stmt->fetch();
    
    if (!$category) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute(['General', 'Default category for auto-created products']);
        $categoryId = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        $category = $stmt->fetch();
    }
    
    return $category;
}

// Helper function to generate SKU from product name
function generateSKU($productName) {
    $timestamp = substr(time(), -6);
    $words = explode(' ', $productName);
    $initials = '';
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
    }
    $initials = substr($initials, 0, 3);
    return $initials . '-' . $timestamp;
}

// Helper function to find or create product by name
function findOrCreateProduct($pdo, $productName, $sellingPrice = null) {
    // First, try to find existing product by name (case-insensitive)
    $stmt = $pdo->prepare("SELECT * FROM products WHERE LOWER(name) = LOWER(?) LIMIT 1");
    $stmt->execute([$productName]);
    $product = $stmt->fetch();
    
    if ($product) {
        return $product;
    }
    
    // Product doesn't exist, create it
    $category = getOrCreateDefaultCategory($pdo);
    $sku = generateSKU($productName);
    $costPrice = $sellingPrice ? floatval($sellingPrice) * 0.7 : 0; // Default cost is 70% of selling price
    
    $stmt = $pdo->prepare("
        INSERT INTO products (name, sku, category_id, cost_price, selling_price, stock_quantity, reorder_level)
        VALUES (?, ?, ?, ?, ?, 0, 10)
    ");
    $stmt->execute([
        $productName,
        $sku,
        $category['id'],
        $costPrice,
        $sellingPrice ? floatval($sellingPrice) : null
    ]);
    
    $productId = $pdo->lastInsertId();
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, c.description as category_description
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    // Format category data
    $product['category'] = [
        'id' => $product['category_id'],
        'name' => $product['category_name'],
        'description' => $product['category_description']
    ];
    
    return $product;
}

// Route handling
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Handle /api/products/metrics/inventory
if ($method === 'GET' && isset($pathParts[2]) && $pathParts[2] === 'metrics' && isset($pathParts[3]) && $pathParts[3] === 'inventory') {
    try {
        $stmt = $pdo->query("SELECT * FROM products");
        $products = $stmt->fetchAll();
        
        $totalValue = 0;
        $lowStockCount = 0;
        $totalStockQuantity = 0;
        
        foreach ($products as $product) {
            $totalValue += floatval($product['cost_price']) * intval($product['stock_quantity']);
            $totalStockQuantity += intval($product['stock_quantity']);
            if (intval($product['stock_quantity']) <= intval($product['reorder_level'])) {
                $lowStockCount++;
            }
        }
        
        jsonResponse([
            'totalProducts' => count($products),
            'totalInventoryValue' => $totalValue,
            'lowStockCount' => $lowStockCount,
            'totalStockQuantity' => $totalStockQuantity
        ]);
    } catch (PDOException $e) {
        errorResponse('Failed to fetch inventory metrics', 500);
    }
}

// Handle /api/products/auto-create
elseif ($method === 'POST' && isset($pathParts[2]) && $pathParts[2] === 'auto-create') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $errors = validateAll([
        'name' => ['required', 'string']
    ], $data);
    
    if ($errors) {
        errorResponse('Validation failed', 400, $errors);
    }
    
    try {
        $product = findOrCreateProduct($pdo, $data['name'], $data['sellingPrice'] ?? null);
        jsonResponse($product);
    } catch (PDOException $e) {
        errorResponse('Failed to create product', 500);
    }
}

// Handle /api/products/:id
elseif (isset($pathParts[2]) && is_numeric($pathParts[2])) {
    $id = intval($pathParts[2]);
    
    if ($method === 'GET') {
        try {
            $stmt = $pdo->prepare("
                SELECT p.*, c.name as category_name, c.description as category_description
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                errorResponse('Product not found', 404);
            }
            
            // Format category data
            $product['category'] = [
                'id' => $product['category_id'],
                'name' => $product['category_name'],
                'description' => $product['category_description']
            ];
            
            jsonResponse($product);
        } catch (PDOException $e) {
            errorResponse('Failed to fetch product', 500);
        }
    }
    
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            // Check if product exists
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                errorResponse('Product not found', 404);
            }
            
            // Build update query
            $updates = [];
            $params = [];
            
            if (isset($data['name'])) {
                $updates[] = "name = ?";
                $params[] = $data['name'];
            }
            if (isset($data['sku'])) {
                // Check if SKU exists for another product
                $stmt = $pdo->prepare("SELECT id FROM products WHERE sku = ? AND id != ?");
                $stmt->execute([$data['sku'], $id]);
                if ($stmt->fetch()) {
                    errorResponse('SKU already exists', 409);
                }
                $updates[] = "sku = ?";
                $params[] = $data['sku'];
            }
            if (isset($data['categoryId'])) {
                $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
                $stmt->execute([$data['categoryId']]);
                if (!$stmt->fetch()) {
                    errorResponse('Category not found', 404);
                }
                $updates[] = "category_id = ?";
                $params[] = intval($data['categoryId']);
            }
            if (isset($data['costPrice'])) {
                $updates[] = "cost_price = ?";
                $params[] = floatval($data['costPrice']);
            }
            if (isset($data['sellingPrice'])) {
                $updates[] = "selling_price = ?";
                $params[] = $data['sellingPrice'] ? floatval($data['sellingPrice']) : null;
            }
            if (isset($data['description'])) {
                $updates[] = "description = ?";
                $params[] = $data['description'];
            }
            if (isset($data['stockQuantity'])) {
                $updates[] = "stock_quantity = ?";
                $params[] = intval($data['stockQuantity']);
            }
            if (isset($data['reorderLevel'])) {
                $updates[] = "reorder_level = ?";
                $params[] = intval($data['reorderLevel']);
            }
            
            if (empty($updates)) {
                errorResponse('No fields to update', 400);
            }
            
            $params[] = $id;
            $sql = "UPDATE products SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            // Fetch updated product
            $stmt = $pdo->prepare("
                SELECT p.*, c.name as category_name, c.description as category_description
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $product = $stmt->fetch();
            
            $product['category'] = [
                'id' => $product['category_id'],
                'name' => $product['category_name'],
                'description' => $product['category_description']
            ];
            
            jsonResponse($product);
        } catch (PDOException $e) {
            errorResponse('Failed to update product', 500);
        }
    }
    
    elseif ($method === 'DELETE') {
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                errorResponse('Product not found', 404);
            }
            
            successResponse('Product deleted successfully');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Foreign key constraint
                errorResponse('Cannot delete product: it is referenced in sales', 409);
            }
            errorResponse('Failed to delete product', 500);
        }
    }
}

// Handle /api/products (GET all, POST create)
else {
    if ($method === 'GET') {
        $search = $_GET['search'] ?? null;
        $categoryId = $_GET['categoryId'] ?? null;
        $lowStock = $_GET['lowStock'] ?? null;
        
        try {
            $sql = "
                SELECT p.*, c.name as category_name, c.description as category_description
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE 1=1
            ";
            $params = [];
            
            if ($search) {
                $sql .= " AND (p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)";
                $searchTerm = "%{$search}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if ($categoryId) {
                $sql .= " AND p.category_id = ?";
                $params[] = intval($categoryId);
            }
            
            $sql .= " ORDER BY p.created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll();
            
            // Filter low stock if requested
            if ($lowStock === 'true') {
                $products = array_filter($products, function($p) {
                    return intval($p['stock_quantity']) <= intval($p['reorder_level']);
                });
                $products = array_values($products); // Re-index array
            }
            
            // Format products with category data
            foreach ($products as &$product) {
                $product['category'] = [
                    'id' => $product['category_id'],
                    'name' => $product['category_name'],
                    'description' => $product['category_description']
                ];
            }
            
            jsonResponse($products);
        } catch (PDOException $e) {
            errorResponse('Failed to fetch products', 500);
        }
    }
    
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $errors = validateAll([
            'name' => ['required', 'string']
        ], $data);
        
        if ($errors) {
            errorResponse('Validation failed', 400, $errors);
        }
        
        try {
            $name = $data['name'];
            $sku = $data['sku'] ?? generateSKU($name);
            $categoryId = $data['categoryId'] ?? null;
            $costPrice = $data['costPrice'] ?? ($data['sellingPrice'] ? floatval($data['sellingPrice']) * 0.7 : 0);
            $sellingPrice = $data['sellingPrice'] ?? null;
            $description = $data['description'] ?? null;
            $stockQuantity = intval($data['stockQuantity'] ?? 0);
            $reorderLevel = intval($data['reorderLevel'] ?? 10);
            
            // Check if SKU exists
            $stmt = $pdo->prepare("SELECT id FROM products WHERE sku = ?");
            $stmt->execute([$sku]);
            if ($stmt->fetch()) {
                $sku = generateSKU($name); // Generate new SKU
            }
            
            // Get or create category
            if ($categoryId) {
                $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
                $stmt->execute([$categoryId]);
                if (!$stmt->fetch()) {
                    errorResponse('Category not found', 404);
                }
            } else {
                $category = getOrCreateDefaultCategory($pdo);
                $categoryId = $category['id'];
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO products (name, sku, category_id, cost_price, selling_price, description, stock_quantity, reorder_level)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $name,
                $sku,
                $categoryId,
                $costPrice,
                $sellingPrice,
                $description,
                $stockQuantity,
                $reorderLevel
            ]);
            
            $productId = $pdo->lastInsertId();
            $stmt = $pdo->prepare("
                SELECT p.*, c.name as category_name, c.description as category_description
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id = ?
            ");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            $product['category'] = [
                'id' => $product['category_id'],
                'name' => $product['category_name'],
                'description' => $product['category_description']
            ];
            
            jsonResponse($product, 201);
        } catch (PDOException $e) {
            errorResponse('Failed to create product', 500);
        }
    }
}

// Export helper function for use in other files
if (!function_exists('findOrCreateProduct')) {
    function findOrCreateProductGlobal($pdo, $productName, $sellingPrice = null) {
        return findOrCreateProduct($pdo, $productName, $sellingPrice);
    }
}
