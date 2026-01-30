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

// Handle /api/categories/:id
if (isset($pathParts[2]) && is_numeric($pathParts[2])) {
    $id = intval($pathParts[2]);
    
    if ($method === 'GET') {
        try {
            $stmt = $pdo->prepare("
                SELECT c.*, COUNT(p.id) as product_count
                FROM categories c
                LEFT JOIN products p ON c.id = p.category_id
                WHERE c.id = ?
                GROUP BY c.id
            ");
            $stmt->execute([$id]);
            $category = $stmt->fetch();
            
            if (!$category) {
                errorResponse('Category not found', 404);
            }
            
            // Get products for this category
            $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ?");
            $stmt->execute([$id]);
            $category['products'] = $stmt->fetchAll();
            $category['_count'] = ['products' => intval($category['product_count'])];
            
            jsonResponse($category);
        } catch (PDOException $e) {
            errorResponse('Failed to fetch category', 500);
        }
    }
    
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $errors = validateAll([
            'name' => ['string']
        ], $data);
        
        if ($errors) {
            errorResponse('Validation failed', 400, $errors);
        }
        
        try {
            // Check if category exists
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                errorResponse('Category not found', 404);
            }
            
            // Check if name already exists (for another category)
            if (isset($data['name'])) {
                $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
                $stmt->execute([$data['name'], $id]);
                if ($stmt->fetch()) {
                    errorResponse('Category with this name already exists', 409);
                }
            }
            
            $updates = [];
            $params = [];
            
            if (isset($data['name'])) {
                $updates[] = "name = ?";
                $params[] = $data['name'];
            }
            if (isset($data['description'])) {
                $updates[] = "description = ?";
                $params[] = $data['description'];
            }
            
            if (empty($updates)) {
                errorResponse('No fields to update', 400);
            }
            
            $params[] = $id;
            $sql = "UPDATE categories SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $category = $stmt->fetch();
            
            jsonResponse($category);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                errorResponse('Category with this name already exists', 409);
            }
            errorResponse('Failed to update category', 500);
        }
    }
    
    elseif ($method === 'DELETE') {
        try {
            // Check if category has products
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count FROM products WHERE category_id = ?
            ");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            
            if (intval($result['count']) > 0) {
                errorResponse('Cannot delete category with existing products', 400, [
                    'productCount' => intval($result['count'])
                ]);
            }
            
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                errorResponse('Category not found', 404);
            }
            
            successResponse('Category deleted successfully');
        } catch (PDOException $e) {
            errorResponse('Failed to delete category', 500);
        }
    }
}

// Handle /api/categories (GET all, POST create)
else {
    if ($method === 'GET') {
        try {
            $stmt = $pdo->query("
                SELECT c.*, COUNT(p.id) as product_count
                FROM categories c
                LEFT JOIN products p ON c.id = p.category_id
                GROUP BY c.id
                ORDER BY c.name ASC
            ");
            $categories = $stmt->fetchAll();
            
            // Format response
            foreach ($categories as &$category) {
                $category['_count'] = ['products' => intval($category['product_count'])];
            }
            
            jsonResponse($categories);
        } catch (PDOException $e) {
            errorResponse('Failed to fetch categories', 500);
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
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null
            ]);
            
            $categoryId = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$categoryId]);
            $category = $stmt->fetch();
            
            jsonResponse($category, 201);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                errorResponse('Category with this name already exists', 409);
            }
            errorResponse('Failed to create category', 500);
        }
    }
}
