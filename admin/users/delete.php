<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminRole();

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id <= 0) {
    $_SESSION['error'] = 'معرف المستخدم غير صالح';
    header('Location: index.php');
    exit;
}

if ($user_id == $_SESSION['admin_user_id']) {
    $_SESSION['error'] = 'لا يمكنك حذف حسابك الخاص';
    header('Location: index.php');
    exit;
}

$user = $db->fetch("SELECT * FROM users WHERE id = ?", [$user_id]);

if (!$user) {
    $_SESSION['error'] = 'المستخدم غير موجود';
    header('Location: index.php');
    exit;
}

// Disable instead of delete to preserve order history
$db->update("UPDATE users SET status = 'inactive', updated_at = NOW() WHERE id = ?", [$user_id]);
$_SESSION['success'] = 'تم تعطيل المستخدم بنجاح';

header('Location: index.php');
exit;
?>