<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../includes/email.php';

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    $_SESSION['error'] = 'رقم الطلب غير صالح';
    header('Location: index.php');
    exit;
}

$order = $db->fetch("SELECT * FROM orders WHERE id = ?", [$order_id]);
if (!$order) {
    $_SESSION['error'] = 'الطلب غير موجود';
    header('Location: index.php');
    exit;
}

if (empty($order['customer_email'])) {
    $_SESSION['error'] = 'العميل لا يملك بريد إلكتروني';
    header('Location: view.php?id=' . $order_id);
    exit;
}

$items = $db->fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$order_id]);

// Generate email HTML
$email_body = generateInvoiceEmailHTML($order, $items);

// Send email
$subject = "فاتورة طلبك #{$order['order_number']} - 3D Store";
$result = sendEmailAdvanced($order['customer_email'], $subject, $email_body, true);

if ($result) {
    $_SESSION['success'] = 'تم إرسال الفاتورة بنجاح إلى ' . $order['customer_email'];
} else {
    $_SESSION['error'] = 'فشل إرسال الفاتورة. يرجى التحقق من إعدادات SMTP';
}

header('Location: view.php?id=' . $order_id);
exit;
?>