<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    $_SESSION['error'] = 'معرف المنتج غير صالح';
    header('Location: index.php');
    exit;
}

$product = $db->fetch("SELECT * FROM products WHERE id = ?", [$product_id]);

if (!$product) {
    $_SESSION['error'] = 'المنتج غير موجود';
    header('Location: index.php');
    exit;
}

// Check if product has orders
$has_orders = $db->fetch("SELECT COUNT(*) as count FROM order_items WHERE product_id = ?", [$product_id]);

if ($has_orders['count'] > 0) {
    // Disable instead of delete
    $db->update("UPDATE products SET status = 'inactive' WHERE id = ?", [$product_id]);
    $_SESSION['success'] = 'تم تعطيل المنتج بنجاح (لا يمكن حذفه لأنه موجود في طلبات سابقة)';
} else {
    try {
        // Delete images
        if ($product['image_path'] && file_exists(PRODUCT_IMAGE_PATH . $product['image_path'])) {
            unlink(PRODUCT_IMAGE_PATH . $product['image_path']);
        }
        
        if ($product['model_3d_path'] && file_exists(MODEL_3D_PATH . $product['model_3d_path'])) {
            unlink(MODEL_3D_PATH . $product['model_3d_path']);
        }
        
        // Delete product
        $db->delete("DELETE FROM products WHERE id = ?", [$product_id]);
        
        $_SESSION['success'] = 'تم حذف المنتج بنجاح';
    } catch (Exception $e) {
        $_SESSION['error'] = 'حدث خطأ أثناء الحذف';
    }
}

header('Location: index.php');
exit;
?>