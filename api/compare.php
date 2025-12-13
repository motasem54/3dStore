<?php
require_once '../includes/store-init.php';
header('Content-Type: application/json');

if (!isset($_SESSION['compare'])) {
    $_SESSION['compare'] = [];
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        $product_id = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        
        if (!$product_id) {
            echo json_encode(['success' => false, 'error' => 'Invalid product']);
            exit;
        }
        
        if (count($_SESSION['compare']) >= 4) {
            echo json_encode(['success' => false, 'error' => 'Maximum 4 products']);
            exit;
        }
        
        if (in_array($product_id, $_SESSION['compare'])) {
            echo json_encode(['success' => false, 'error' => 'Already in compare']);
            exit;
        }
        
        $_SESSION['compare'][] = $product_id;
        
        echo json_encode(['success' => true, 'count' => count($_SESSION['compare'])]);
        break;
        
    case 'remove':
        $product_id = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        
        $_SESSION['compare'] = array_values(array_filter($_SESSION['compare'], fn($id) => $id != $product_id));
        
        echo json_encode(['success' => true, 'count' => count($_SESSION['compare'])]);
        break;
        
    case 'clear':
        $_SESSION['compare'] = [];
        
        echo json_encode(['success' => true]);
        break;
        
    case 'list':
        $ids = $_SESSION['compare'];
        $products = [];
        
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $products = $db->fetchAll("SELECT * FROM products WHERE id IN ($placeholders) AND status = 'active'", $ids);
        }
        
        echo json_encode(['success' => true, 'products' => $products]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}