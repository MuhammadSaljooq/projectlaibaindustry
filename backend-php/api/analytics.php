<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../middleware/cors.php';

handleCORS();

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

// Handle /api/analytics/dashboard
if (isset($pathParts[2]) && $pathParts[2] === 'dashboard') {
    try {
        // Get date range (default to last 30 days)
        $endDate = date('Y-m-d H:i:s');
        $startDate = date('Y-m-d H:i:s', strtotime('-30 days'));
        
        // Total sales
        $stmt = $pdo->prepare("
            SELECT SUM(total_amount) as total_sales
            FROM sales
            WHERE date >= ? AND date <= ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $salesResult = $stmt->fetch();
        $totalSales = floatval($salesResult['total_sales'] ?? 0);
        
        // Total profit
        $stmt = $pdo->prepare("
            SELECT SUM(profit) as total_profit
            FROM sales_items si
            INNER JOIN sales s ON si.sale_id = s.id
            WHERE s.date >= ? AND s.date <= ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $profitResult = $stmt->fetch();
        $totalProfit = floatval($profitResult['total_profit'] ?? 0);
        
        // Inventory metrics
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as total_products,
                SUM(cost_price * stock_quantity) as total_inventory_value,
                SUM(CASE WHEN stock_quantity <= reorder_level THEN 1 ELSE 0 END) as low_stock_count
            FROM products
        ");
        $inventory = $stmt->fetch();
        
        // Recent sales count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM sales
            WHERE date >= ? AND date <= ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $salesCount = $stmt->fetch();
        
        jsonResponse([
            'totalSales' => $totalSales,
            'totalProfit' => $totalProfit,
            'totalInventoryValue' => floatval($inventory['total_inventory_value'] ?? 0),
            'lowStockCount' => intval($inventory['low_stock_count'] ?? 0),
            'recentSalesCount' => intval($salesCount['count'] ?? 0),
            'totalProducts' => intval($inventory['total_products'] ?? 0)
        ]);
    } catch (PDOException $e) {
        errorResponse('Failed to fetch dashboard data', 500);
    }
}

// Handle /api/analytics/sales-summary
elseif (isset($pathParts[2]) && $pathParts[2] === 'sales-summary') {
    try {
        $period = $_GET['period'] ?? 'daily';
        $startDate = $_GET['startDate'] ?? null;
        $endDate = $_GET['endDate'] ?? null;
        
        if (!$startDate && !$endDate) {
            $endDate = date('Y-m-d');
            switch ($period) {
                case 'daily':
                    $startDate = date('Y-m-d', strtotime('-7 days'));
                    break;
                case 'weekly':
                    $startDate = date('Y-m-d', strtotime('-28 days'));
                    break;
                case 'monthly':
                    $startDate = date('Y-m-d', strtotime('-6 months'));
                    break;
            }
        }
        
        $stmt = $pdo->prepare("
            SELECT s.*, SUM(si.profit) as sale_profit
            FROM sales s
            LEFT JOIN sales_items si ON s.id = si.sale_id
            WHERE s.date >= ? AND s.date <= ?
            GROUP BY s.id
            ORDER BY s.date ASC
        ");
        $stmt->execute([$startDate, $endDate]);
        $sales = $stmt->fetchAll();
        
        // Group by period
        $grouped = [];
        
        foreach ($sales as $sale) {
            $saleDate = new DateTime($sale['date']);
            $key = '';
            
            switch ($period) {
                case 'daily':
                    $key = $saleDate->format('Y-m-d');
                    break;
                case 'weekly':
                    $weekStart = clone $saleDate;
                    $weekStart->modify('-' . $saleDate->format('w') . ' days');
                    $key = $weekStart->format('Y-m-d');
                    break;
                case 'monthly':
                    $key = $saleDate->format('Y-m');
                    break;
            }
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'period' => $key,
                    'totalSales' => 0,
                    'totalProfit' => 0,
                    'transactionCount' => 0
                ];
            }
            
            $grouped[$key]['totalSales'] += floatval($sale['total_amount']);
            $grouped[$key]['totalProfit'] += floatval($sale['sale_profit'] ?? 0);
            $grouped[$key]['transactionCount'] += 1;
        }
        
        jsonResponse(array_values($grouped));
    } catch (PDOException $e) {
        errorResponse('Failed to fetch sales summary', 500);
    }
}

// Handle /api/analytics/top-products
elseif (isset($pathParts[2]) && $pathParts[2] === 'top-products') {
    try {
        $limit = intval($_GET['limit'] ?? 10);
        
        $stmt = $pdo->query("
            SELECT 
                si.product_id,
                SUM(si.quantity) as total_quantity,
                COUNT(si.id) as times_sold
            FROM sales_items si
            GROUP BY si.product_id
            ORDER BY total_quantity DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $topItems = $stmt->fetchAll();
        
        $productIds = array_column($topItems, 'product_id');
        if (empty($productIds)) {
            jsonResponse([]);
            exit;
        }
        
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id IN ($placeholders)
        ");
        $stmt->execute($productIds);
        $products = $stmt->fetchAll();
        
        $productsById = [];
        foreach ($products as $product) {
            $productsById[$product['id']] = $product;
            $productsById[$product['id']]['category'] = ['name' => $product['category_name']];
        }
        
        $topProducts = [];
        foreach ($topItems as $item) {
            if (isset($productsById[$item['product_id']])) {
                $topProducts[] = [
                    'product' => $productsById[$item['product_id']],
                    'totalQuantitySold' => intval($item['total_quantity']),
                    'timesSold' => intval($item['times_sold'])
                ];
            }
        }
        
        jsonResponse($topProducts);
    } catch (PDOException $e) {
        errorResponse('Failed to fetch top products', 500);
    }
}

// Handle /api/analytics/profit-margins
elseif (isset($pathParts[2]) && $pathParts[2] === 'profit-margins') {
    try {
        $stmt = $pdo->query("
            SELECT 
                c.name as category_name,
                SUM(si.profit) as total_profit,
                SUM(si.selling_price * si.quantity) as total_sales,
                SUM(si.quantity) as item_count
            FROM sales_items si
            INNER JOIN products p ON si.product_id = p.id
            INNER JOIN categories c ON p.category_id = c.id
            GROUP BY c.id, c.name
        ");
        $categoryData = $stmt->fetchAll();
        
        $margins = [];
        foreach ($categoryData as $cat) {
            $totalSales = floatval($cat['total_sales']);
            $totalProfit = floatval($cat['total_profit']);
            $margin = $totalSales > 0 ? ($totalProfit / $totalSales) * 100 : 0;
            
            $margins[] = [
                'category' => $cat['category_name'],
                'totalProfit' => $totalProfit,
                'totalSales' => $totalSales,
                'itemCount' => intval($cat['item_count']),
                'margin' => $margin
            ];
        }
        
        jsonResponse($margins);
    } catch (PDOException $e) {
        errorResponse('Failed to fetch profit margins', 500);
    }
}

else {
    errorResponse('Analytics endpoint not found', 404);
}
