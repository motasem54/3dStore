<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

$from_date = $_GET['from'] ?? date('Y-m-01');
$to_date = $_GET['to'] ?? date('Y-m-d');

$orders = $db->fetchAll(
    "SELECT o.*, u.username 
     FROM orders o 
     LEFT JOIN users u ON o.user_id = u.id 
     WHERE DATE(o.created_at) BETWEEN ? AND ? 
     ORDER BY o.created_at DESC",
    [$from_date, $to_date]
);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="orders_' . $from_date . '_to_' . $to_date . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');
fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM

// Headers
fputcsv($output, [
    'رقم الطلب',
    'اسم العميل',
    'الهاتف',
    'البريد الإلكتروني',
    'المبلغ الإجمالي',
    'العملة',
    'الحالة',
    'حالة الدفع',
    'نوع الطلب',
    'التاريخ',
    'المستخدم'
]);

foreach ($orders as $order) {
    fputcsv($output, [
        $order['order_number'],
        $order['customer_name'],
        $order['customer_phone'],
        $order['customer_email'] ?? '',
        $order['total_amount'],
        $order['currency'],
        $order['status'],
        $order['payment_status'],
        $order['order_type'],
        date('Y-m-d H:i', strtotime($order['created_at'])),
        $order['username'] ?? 'زائر'
    ]);
}

fclose($output);
exit;
?>