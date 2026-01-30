<?php
require_once __DIR__ . '/../config/database.php';

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
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    return $product;
}
