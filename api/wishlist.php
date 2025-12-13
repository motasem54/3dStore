<?php
require_once '../includes/store-init.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'add':
        $product_id = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        
        if (!$product_id) {
            echo json_encode(['success' => false, 'error' => 'Invalid product']);
            exit;
        }
        
        $existing = $db->fetchOne("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?", [$user_id, $product_id]);
        
        if ($existing) {
            echo json_encode(['success' => false, 'error' => 'Already in wishlist']);
            exit;
        }
        
        $db->execute("INSERT INTO wishlist (user_id, product_id, created_at) VALUES (?, ?, NOW())", [$user_id, $product_id]);
        
        echo json_encode(['success' => true]);
        break;
        
    case 'remove':
        $product_id = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        
        $db->execute("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?", [$user_id, $product_id]);
        
        echo json_encode(['success' => true]);
        break;
        
    case 'list':
        $items = $db->fetchAll("
            SELECT w.*, p.name_ar, p.name_en, p.price, p.discount_price, p.image_path
            FROM wishlist w
            JOIN products p ON w.product_id = p.id
            WHERE w.user_id = ? AND p.status = 'active'
            ORDER BY w.created_at DESC
        ", [$user_id]);
        
        echo json_encode(['success' => true, 'items' => $items]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}