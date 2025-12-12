<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($order_id <= 0) exit('Invalid order');

$order = $db->fetch("SELECT * FROM orders WHERE id = ?", [$order_id]);
if (!$order) exit('Order not found');

$items = $db->fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$order_id]);

$store_name = getSetting('site_name_ar', '3D Store');
$store_phone = getSetting('store_phone', '');
$store_address = getSetting('store_address', '');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة #<?php echo escape($order['order_number']); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, 'Tahoma', sans-serif; padding: 40px; }
        .invoice { max-width: 800px; margin: 0 auto; border: 2px solid #333; padding: 30px; }
        .header { text-align: center; border-bottom: 3px solid #333; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .info-row { display: flex; justify-content: space-between; margin: 20px 0; }
        .info-box { flex: 1; }
        .info-box h3 { font-size: 14px; margin-bottom: 8px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table th { background: #f5f5f5; padding: 12px; text-align: right; border: 1px solid #ddd; }
        table td { padding: 10px; border: 1px solid #ddd; }
        .total-row { background: #f9f9f9; font-weight: bold; }
        .footer { margin-top: 40px; text-align: center; padding-top: 20px; border-top: 2px solid #333; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 12px 24px; font-size: 16px; cursor: pointer;">طباعة الفاتورة</button>
    </div>
    
    <div class="invoice">
        <div class="header">
            <h1><?php echo escape($store_name); ?></h1>
            <p><?php echo escape($store_address); ?></p>
            <p>الهاتف: <?php echo escape($store_phone); ?></p>
        </div>
        
        <div class="info-row">
            <div class="info-box">
                <h3>معلومات الفاتورة</h3>
                <p><strong>رقم الطلب:</strong> <?php echo escape($order['order_number']); ?></p>
                <p><strong>التاريخ:</strong> <?php echo formatDate($order['created_at'], 'd/m/Y H:i'); ?></p>
                <p><strong>الحالة:</strong> <?php echo escape($order['status']); ?></p>
            </div>
            <div class="info-box">
                <h3>معلومات العميل</h3>
                <p><strong>الاسم:</strong> <?php echo escape($order['customer_name']); ?></p>
                <p><strong>الهاتف:</strong> <?php echo escape($order['customer_phone']); ?></p>
                <?php if ($order['customer_email']): ?>
                <p><strong>البريد:</strong> <?php echo escape($order['customer_email']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>الكمية</th>
                    <th>السعر</th>
                    <th>المجموع</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo escape($item['product_name']); ?></td>
                    <td><?php echo number_format($item['quantity']); ?></td>
                    <td><?php echo formatPrice($item['unit_price'], $order['currency']); ?></td>
                    <td><?php echo formatPrice($item['total'], $order['currency']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: left;"><strong>المجموع الفرعي:</strong></td>
                    <td><?php echo formatPrice($order['subtotal'], $order['currency']); ?></td>
                </tr>
                <?php if ($order['tax'] > 0): ?>
                <tr>
                    <td colspan="3" style="text-align: left;">الضريبة (<?php echo TAX_RATE; ?>%):</td>
                    <td><?php echo formatPrice($order['tax'], $order['currency']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($order['shipping_cost'] > 0): ?>
                <tr>
                    <td colspan="3" style="text-align: left;">الشحن:</td>
                    <td><?php echo formatPrice($order['shipping_cost'], $order['currency']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($order['discount'] > 0): ?>
                <tr>
                    <td colspan="3" style="text-align: left;">الخصم:</td>
                    <td style="color: red;">- <?php echo formatPrice($order['discount'], $order['currency']); ?></td>
                </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td colspan="3" style="text-align: left; font-size: 18px;">المجموع الكلي:</td>
                    <td style="font-size: 18px;"><?php echo formatPrice($order['total_amount'], $order['currency']); ?></td>
                </tr>
            </tfoot>
        </table>
        
        <div class="footer">
            <p>شكراً لتعاملكم معنا</p>
            <p style="font-size: 12px; color: #666; margin-top: 10px;">تم الطباعة بتاريخ: <?php echo date('d/m/Y H:i'); ?></p>
        </div>
    </div>
</body>
</html>