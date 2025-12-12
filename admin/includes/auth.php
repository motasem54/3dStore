<?php
declare(strict_types=1);

if (empty($_SESSION['admin_user_id'])) {
    header('Location: /admin/login.php');
    exit;
}

// Check if admin or sales
$current_role = $_SESSION['admin_role'] ?? '';
if (!in_array($current_role, ['admin', 'sales'], true)) {
    session_destroy();
    header('Location: /admin/login.php');
    exit;
}

function requireAdminRole() {
    if (($_SESSION['admin_role'] ?? '') !== 'admin') {
        die('غير مصرح لك بالوصول إلى هذه الصفحة');
    }
}
?>