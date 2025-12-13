<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id <= 0) {
    $_SESSION['error'] = 'معرف التصنيف غير صالح';
    header('Location: index.php');
    exit;
}

$category = $db->fetch("SELECT * FROM categories WHERE id = ?", [$category_id]);

if (!$category) {
    $_SESSION['error'] = 'التصنيف غير موجود';
    header('Location: index.php');
    exit;
}

$has_products = $db->fetch("SELECT COUNT(*) as count FROM products WHERE category_id = ?", [$category_id]);

if ($has_products['count'] > 0) {
    $_SESSION['error'] = 'لا يمكن حذف التصنيف لأنه يحتوي على منتجات. احذف المنتجات أولاً أو انقلها لتصنيف آخر.';
    header('Location: index.php');
    exit;
}

try {
    if ($category['image_path'] && file_exists('uploads/categories/' . $category['image_path'])) {
        unlink('uploads/categories/' . $category['image_path']);
    }
    
    $db->delete("DELETE FROM categories WHERE id = ?", [$category_id]);
    $_SESSION['success'] = 'تم حذف التصنيف بنجاح';
} catch (Exception $e) {
    $_SESSION['error'] = 'حدث خطأ أثناء الحذف';
}

header('Location: index.php');
exit;
?>