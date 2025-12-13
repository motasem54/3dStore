<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/init.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

$category = $_GET['category'] ?? 'all';
$search = $_GET['search'] ?? '';

$where = ["status = 'active'"];
$params = [];

if ($category !== 'all') {
    $where[] = "category_id = ?";
    $params[] = (int)$category;
}

if (!empty($search)) {
    $where[] = "(name_ar LIKE ? OR name_en LIKE ? OR sku LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = implode(' AND ', $where);

$products = $db->fetchAll(
    "SELECT id, name_ar, sku, price_ils, image_path 
     FROM products 
     WHERE {$whereClause}
     ORDER BY name_ar 
     LIMIT 100",
    $params
);

foreach ($products as &$product) {
    if ($product['image_path']) {
        $product['image_path'] = UPLOAD_URL . '/products/' . $product['image_path'];
    }
}

echo json_encode($products);
?>