<?php
require_once '../includes/store-init.php';
header('Content-Type: application/json');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 999999;

$offset = ($page - 1) * $per_page;

// Build WHERE clause
$where = ["status = 'active'"];
$params = [];

if ($category > 0) {
    $where[] = "category_id = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $where[] = "(name_ar LIKE ? OR name_en LIKE ? OR description_ar LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($min_price > 0) {
    $where[] = "price >= ?";
    $params[] = $min_price;
}

if ($max_price < 999999) {
    $where[] = "price <= ?";
    $params[] = $max_price;
}

$where_clause = implode(' AND ', $where);

// Sorting
$order_by = match($sort) {
    'price_low' => 'price ASC',
    'price_high' => 'price DESC',
    'name' => 'name_ar ASC',
    'popular' => 'views DESC',
    default => 'created_at DESC'
};

// Get total count
$total = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE $where_clause", $params)['count'];

// Get products
$products = $db->fetchAll(
    "SELECT * FROM products WHERE $where_clause ORDER BY $order_by LIMIT ? OFFSET ?",
    array_merge($params, [$per_page, $offset])
);

// Get additional images for each product
foreach ($products as &$product) {
    $product['images'] = $db->fetchAll(
        "SELECT image_path, is_primary FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order",
        [$product['id']]
    );
    
    // Format price
    $product['price_formatted'] = formatPrice($product['discount_price'] ?: $product['price']);
    $product['original_price_formatted'] = $product['discount_price'] ? formatPrice($product['price']) : null;
    
    // Calculate discount percentage
    if ($product['discount_price']) {
        $product['discount_percentage'] = round((($product['price'] - $product['discount_price']) / $product['price']) * 100);
    } else {
        $product['discount_percentage'] = 0;
    }
    
    // Build full image URL
    if ($product['image_path']) {
        $product['image_url'] = UPLOAD_URL . '/products/' . $product['image_path'];
    } else {
        $product['image_url'] = null;
    }
}

// Response
echo json_encode([
    'success' => true,
    'products' => $products,
    'pagination' => [
        'current_page' => $page,
        'per_page' => $per_page,
        'total' => $total,
        'total_pages' => ceil($total / $per_page),
        'has_more' => ($offset + $per_page) < $total
    ]
], JSON_UNESCAPED_UNICODE);