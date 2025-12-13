<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/init.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$customer_name = sanitizeInput($_POST['customer_name'] ?? '');
$customer_phone = sanitizeInput($_POST['customer_phone'] ?? '');
$customer_email = sanitizeInput($_POST['customer_email'] ?? '');
$payment_method = $_POST['payment_method'] ?? 'cash';
$notes = sanitizeInput($_POST['notes'] ?? '');
$cart = json_decode($_POST['cart'] ?? '[]', true);

if (empty($customer_name) || empty($customer_phone) || empty($cart)) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$db->beginTransaction();

try {
    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += $item['price_ils'] * $item['quantity'];
    }
    
    $tax = $subtotal * 0.17;
    $total = $subtotal + $tax;
    
    $order_number = 'POS-' . date('Y') . '-' . str_pad((string)rand(1, 99999), 5, '0', STR_PAD_LEFT);
    
    $order_id = $db->insert(
        "INSERT INTO orders 
         (order_number, customer_name, customer_phone, customer_email, 
          subtotal, tax, total_amount, payment_method, payment_status, 
          status, order_type, notes, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'paid', 'completed', 'pos', ?, NOW())",
        [$order_number, $customer_name, $customer_phone, $customer_email, 
         $subtotal, $tax, $total, $payment_method, $notes]
    );
    
    foreach ($cart as $item) {
        $item_total = $item['price_ils'] * $item['quantity'];
        $db->insert(
            "INSERT INTO order_items 
             (order_id, product_id, product_name, product_sku, quantity, unit_price, total) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$order_id, $item['id'], $item['name_ar'], $item['sku'], 
             $item['quantity'], $item['price_ils'], $item_total]
        );
        
        $db->update(
            "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?",
            [$item['quantity'], $item['id']]
        );
    }
    
    $db->commit();
    echo json_encode(['success' => true, 'order_id' => $order_id]);
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>