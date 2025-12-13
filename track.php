<?php
require_once 'includes/store-init.php';
$page_title = 'تتبع الطلب';

$order = null;
$order_id = $_GET['order'] ?? $_POST['order_id'] ?? '';

if ($order_id) {
    $order = $db->fetchOne("SELECT * FROM orders WHERE id = ? OR order_number = ?", [$order_id, $order_id]);
    if ($order) {
        $order_items = $db->fetchAll("SELECT oi.*, p.name_ar, p.name_en, p.image_path FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?", [$order['id']]);
    }
}

include 'includes/header.php';
?>

<section style="padding:80px 0;background:var(--bg-light)">
<div class="container">
<h1 style="font-size:32px;margin-bottom:40px;text-align:center"><i class="bi bi-box-seam"></i> تتبع طلبك</h1>

<?php if (!$order): ?>
<div style="max-width:500px;margin:0 auto;background:white;border-radius:16px;padding:40px;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
    <form method="POST" style="text-align:center">
        <i class="bi bi-search" style="font-size:60px;color:var(--primary);display:block;margin-bottom:20px"></i>
        <label style="display:block;margin-bottom:12px;font-weight:600">أدخل رقم الطلب</label>
        <input type="text" name="order_id" required placeholder="مثل: ORD-12345" style="width:100%;padding:14px;border:1px solid var(--border);border-radius:10px;margin-bottom:20px;font-size:16px;text-align:center">
        <button type="submit" style="width:100%;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;padding:14px;border:none;border-radius:10px;font-size:16px;font-weight:600;cursor:pointer"><i class="bi bi-search"></i> تتبع الطلب</button>
    </form>
</div>
<?php else: ?>
<div style="max-width:900px;margin:0 auto">
    <!-- Order Status -->
    <div style="background:white;border-radius:16px;padding:30px;margin-bottom:24px;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px">
            <div>
                <h2 style="font-size:24px;margin-bottom:8px">طلب رقم: <span style="color:var(--primary)"><?php echo escape($order['order_number']); ?></span></h2>
                <p style="color:var(--text-light)">تاريخ الطلب: <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
            </div>
            <?php
            $status_colors = ['pending' => '#fbbf24', 'processing' => '#3b82f6', 'shipped' => '#8b5cf6', 'delivered' => '#10b981', 'cancelled' => '#ef4444'];
            $status_names = ['pending' => 'قيد المراجعة', 'processing' => 'قيد التجهيز', 'shipped' => 'تم الشحن', 'delivered' => 'تم التوصيل', 'cancelled' => 'ملغي'];
            $current_status = $order['status'];
            ?>
            <span style="padding:10px 20px;background:<?php echo $status_colors[$current_status]; ?>;color:white;border-radius:20px;font-weight:600"><?php echo $status_names[$current_status]; ?></span>
        </div>
        
        <!-- Progress Steps -->
        <div style="display:flex;justify-content:space-between;position:relative;margin-bottom:40px">
            <div style="position:absolute;top:20px;right:0;left:0;height:2px;background:var(--border);z-index:0"></div>
            <div style="position:absolute;top:20px;right:0;left:<?php echo $current_status === 'pending' ? '75%' : ($current_status === 'processing' ? '50%' : ($current_status === 'shipped' ? '25%' : '0')); ?>;height:2px;background:var(--success);z-index:0;transition:0.5s"></div>
            
            <?php foreach (['pending', 'processing', 'shipped', 'delivered'] as $status): ?>
            <div style="display:flex;flex-direction:column;align-items:center;position:relative;z-index:1">
                <div style="width:40px;height:40px;border-radius:50%;background:<?php echo array_search($current_status, ['pending', 'processing', 'shipped', 'delivered']) >= array_search($status, ['pending', 'processing', 'shipped', 'delivered']) ? 'var(--success)' : 'var(--bg-light)'; ?>;display:flex;align-items:center;justify-content:center;color:white;margin-bottom:8px"><i class="bi bi-check" style="font-size:20px;font-weight:700"></i></div>
                <span style="font-size:13px;text-align:center;font-weight:600;color:<?php echo array_search($current_status, ['pending', 'processing', 'shipped', 'delivered']) >= array_search($status, ['pending', 'processing', 'shipped', 'delivered']) ? 'var(--success)' : 'var(--text-light)'; ?>"><?php echo $status_names[$status]; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Shipping Info -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;background:var(--bg-light);border-radius:12px;padding:20px">
            <div>
                <h4 style="font-size:16px;margin-bottom:12px;color:var(--text-light)">معلومات الشحن</h4>
                <p style="margin-bottom:4px"><strong><?php echo escape($order['customer_name']); ?></strong></p>
                <p style="font-size:14px;color:var(--text-light)"><?php echo escape($order['shipping_address']); ?></p>
                <p style="font-size:14px;color:var(--text-light)"><?php echo escape($order['customer_phone']); ?></p>
            </div>
            <div>
                <h4 style="font-size:16px;margin-bottom:12px;color:var(--text-light)">معلومات الدفع</h4>
                <p style="margin-bottom:4px"><strong>طريقة الدفع:</strong> <?php echo escape($order['payment_method']); ?></p>
                <p style="margin-bottom:4px"><strong>حالة الدفع:</strong> <span style="color:<?php echo $order['payment_status'] === 'paid' ? 'var(--success)' : 'var(--danger)'; ?>"><?php echo $order['payment_status'] === 'paid' ? 'مدفوع' : 'غير مدفوع'; ?></span></p>
                <p style="font-size:20px;font-weight:700;color:var(--primary);margin-top:8px">الإجمالي: <?php echo formatPrice($order['total_amount']); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Order Items -->
    <div style="background:white;border-radius:16px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
        <h3 style="font-size:20px;margin-bottom:20px">تفاصيل الطلب</h3>
        <?php foreach ($order_items as $item): ?>
        <div style="display:flex;gap:16px;padding:16px;border-bottom:1px solid var(--border);align-items:center">
            <div style="width:80px;height:80px;border-radius:10px;overflow:hidden;background:var(--bg-light)">
                <?php if ($item['image_path']): ?>
                <img src="<?php echo UPLOAD_URL . '/products/' . $item['image_path']; ?>" style="width:100%;height:100%;object-fit:cover">
                <?php else: ?>
                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center"><i class="bi bi-image" style="font-size:28px;color:var(--text-light)"></i></div>
                <?php endif; ?>
            </div>
            <div style="flex:1">
                <h4 style="font-size:16px;margin-bottom:4px"><?php echo escape($item['name_' . $lang]); ?></h4>
                <p style="font-size:14px;color:var(--text-light)">الكمية: <?php echo $item['quantity']; ?> × <?php echo formatPrice($item['unit_price']); ?></p>
            </div>
            <strong style="font-size:18px;color:var(--primary)"><?php echo formatPrice($item['total_price']); ?></strong>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
</div>
</section>

<?php include 'includes/footer.php'; ?>