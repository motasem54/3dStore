<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Secure session
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', '1');
}

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/functions.php';

$db = Database::getInstance();

// CSRF Token
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// Helper functions for admin
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">قيد الانتظار</span>',
        'processing' => '<span class="badge badge-info">قيد المعالجة</span>',
        'shipped' => '<span class="badge badge-primary">تم الشحن</span>',
        'delivered' => '<span class="badge badge-success">تم التسليم</span>',
        'cancelled' => '<span class="badge badge-danger">ملغي</span>',
        'refunded' => '<span class="badge badge-secondary">مسترد</span>',
    ];
    return $badges[$status] ?? '<span class="badge">'. escape($status) .'</span>';
}
?>