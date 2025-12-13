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

<section style="padding:80px 0;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:calc(100vh - 200px)">
<div class="container">

<?php if (!$order): ?>
<div style="max-width:600px;margin:0 auto">
    <div style="text-align:center;margin-bottom:40px">
        <div style="width:120px;height:120px;background:rgba(255,255,255,0.2);backdrop-filter:blur(10px);border-radius:30px;margin:0 auto 24px;display:flex;align-items:center;justify-content:center">
            <i class="bi bi-search" style="font-size:60px;color:white"></i>
        </div>
        <h1 style="font-size:42px;color:white;margin-bottom:12px;font-weight:800">تتبع طلبك</h1>
        <p style="font-size:18px;color:rgba(255,255,255,0.9)">أدخل رقم الطلب لمعرفة حالته</p>
    </div>
    
    <form method="POST" style="background:white;border-radius:24px;padding:50px;box-shadow:0 20px 60px rgba(0,0,0,0.3)">
        <label style="display:block;margin-bottom:12px;font-weight:700;font-size:16px;color:var(--dark)">رقم الطلب</label>
        <div style="position:relative;margin-bottom:24px">
            <i class="bi bi-box-seam" style="position:absolute;right:18px;top:50%;transform:translateY(-50%);font-size:22px;color:var(--text-light)"></i>
            <input type="text" name="order_id" required placeholder="مثل: ORD-12345" style="width:100%;padding:18px 18px 18px 60px;border:2px solid var(--border);border-radius:14px;font-size:18px;font-weight:600;transition:0.3s" onfocus="this.style.borderColor='var(--primary)';this.style.boxShadow='0 0 0 4px rgba(59,130,246,0.1)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
        </div>
        <button type="submit" style="width:100%;padding:18px;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;border:none;border-radius:14px;font-size:18px;font-weight:700;cursor:pointer;transition:0.3s;box-shadow:0 8px 24px rgba(59,130,246,0.4)" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 32px rgba(59,130,246,0.5)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 8px 24px rgba(59,130,246,0.4)'">
            <i class="bi bi-search"></i> تتبع الطلب
        </button>
    </form>
</div>

<?php else: ?>
<div style="max-width:1000px;margin:0 auto">
    <!-- Order Header -->
    <div style="background:rgba(255,255,255,0.15);backdrop-filter:blur(10px);border-radius:24px;padding:40px;margin-bottom:30px;border:1px solid rgba(255,255,255,0.2)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px">
            <div>
                <p style="color:rgba(255,255,255,0.8);margin-bottom:8px;font-size:14px">رقم الطلب</p>
                <h2 style="font-size:36px;color:white;font-weight:800">#<?php echo escape($order['order_number']); ?></h2>
                <p style="color:rgba(255,255,255,0.7);font-size:14px;margin-top:8px"><i class="bi bi-calendar"></i> <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
            </div>
            <?php
            $status_colors = ['pending' => 'rgba(251,191,36,0.9)', 'processing' => 'rgba(59,130,246,0.9)', 'shipped' => 'rgba(139,92,246,0.9)', 'delivered' => 'rgba(16,185,129,0.9)', 'cancelled' => 'rgba(239,68,68,0.9)'];
            $status_names = ['pending' => 'قيد المراجعة', 'processing' => 'قيد التجهيز', 'shipped' => 'تم الشحن', 'delivered' => 'تم التوصيل', 'cancelled' => 'ملغي'];
            $status_icons = ['pending' => 'clock', 'processing' => 'box', 'shipped' => 'truck', 'delivered' => 'check-circle-fill', 'cancelled' => 'x-circle'];
            $current_status = $order['status'];
            ?>
            <div style="text-align:center">
                <div style="width:100px;height:100px;background:<?php echo $status_colors[$current_status]; ?>;backdrop-filter:blur(10px);border-radius:50%;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 30px rgba(0,0,0,0.3)">
                    <i class="bi bi-<?php echo $status_icons[$current_status]; ?>" style="font-size:48px;color:white"></i>
                </div>
                <span style="padding:10px 24px;background:rgba(255,255,255,0.95);color:var(--dark);border-radius:30px;font-size:15px;font-weight:700;box-shadow:0 4px 12px rgba(0,0,0,0.2)"><?php echo $status_names[$current_status]; ?></span>
            </div>
        </div>
        
        <!-- Progress Timeline -->
        <div style="position:relative;padding:40px 0">
            <div style="position:absolute;top:50%;right:0;left:0;height:3px;background:rgba(255,255,255,0.2);transform:translateY(-50%)"></div>
            <?php
            $statuses = ['pending', 'processing', 'shipped', 'delivered'];
            $current_index = array_search($current_status, $statuses);
            if ($current_index === false) $current_index = 0;
            $progress = ($current_index / (count($statuses) - 1)) * 100;
            ?>
            <div style="position:absolute;top:50%;right:0;width:<?php echo $progress; ?>%;height:3px;background:linear-gradient(90deg,rgba(16,185,129,0.8),rgba(16,185,129,1));transform:translateY(-50%);transition:width 1s"></div>
            
            <div style="display:flex;justify-content:space-between;position:relative">
                <?php foreach ($statuses as $i => $status): 
                    $is_active = $i <= $current_index;
                    $step_icons = ['pending' => 'check', 'processing' => 'box', 'shipped' => 'truck', 'delivered' => 'star-fill'];
                ?>
                <div style="display:flex;flex-direction:column;align-items:center;flex:1">
                    <div style="width:60px;height:60px;border-radius:50%;background:<?php echo $is_active ? 'rgba(16,185,129,0.95)' : 'rgba(255,255,255,0.2)'; ?>;backdrop-filter:blur(10px);display:flex;align-items:center;justify-content:center;color:white;margin-bottom:12px;transition:all 0.5s;box-shadow:0 4px 12px rgba(0,0,0,0.2);border:3px solid <?php echo $is_active ? 'rgba(255,255,255,0.3)' : 'transparent'; ?>">
                        <i class="bi bi-<?php echo $step_icons[$status]; ?>" style="font-size:24px;font-weight:700"></i>
                    </div>
                    <span style="font-size:14px;font-weight:<?php echo $is_active ? '700' : '500'; ?>;color:<?php echo $is_active ? 'white' : 'rgba(255,255,255,0.6)'; ?>;text-align:center"><?php echo $status_names[$status]; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Order Details -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:30px">
        <div style="background:white;border-radius:20px;padding:30px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
            <h3 style="font-size:20px;margin-bottom:20px;color:var(--dark);font-weight:700"><i class="bi bi-geo-alt"></i> معلومات الشحن</h3>
            <div style="background:var(--bg-light);border-radius:12px;padding:20px">
                <p style="margin-bottom:8px;font-weight:700;font-size:16px"><?php echo escape($order['customer_name']); ?></p>
                <p style="color:var(--text-light);margin-bottom:6px;font-size:14px"><i class="bi bi-house"></i> <?php echo escape($order['shipping_address']); ?></p>
                <p style="color:var(--text-light);font-size:14px"><i class="bi bi-telephone"></i> <?php echo escape($order['customer_phone']); ?></p>
            </div>
        </div>
        
        <div style="background:white;border-radius:20px;padding:30px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
            <h3 style="font-size:20px;margin-bottom:20px;color:var(--dark);font-weight:700"><i class="bi bi-wallet2"></i> معلومات الدفع</h3>
            <div style="background:var(--bg-light);border-radius:12px;padding:20px">
                <div style="display:flex;justify-content:space-between;margin-bottom:12px"><span style="color:var(--text-light)">طريقة الدفع:</span><strong><?php echo escape($order['payment_method']); ?></strong></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:12px"><span style="color:var(--text-light)">حالة الدفع:</span><strong style="color:<?php echo $order['payment_status'] === 'paid' ? 'var(--success)' : 'var(--danger)'; ?>"><?php echo $order['payment_status'] === 'paid' ? 'مدفوع' : 'غير مدفوع'; ?></strong></div>
                <div style="display:flex;justify-content:space-between;padding-top:12px;border-top:2px solid var(--border)"><span style="color:var(--text);font-weight:600">الإجمالي:</span><strong style="font-size:24px;color:var(--primary)"><?php echo formatPrice($order['total_amount']); ?></strong></div>
            </div>
        </div>
    </div>
    
    <!-- Order Items -->
    <div style="background:white;border-radius:20px;padding:30px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <h3 style="font-size:22px;margin-bottom:24px;color:var(--dark);font-weight:700"><i class="bi bi-bag-check"></i> منتجات الطلب</h3>
        <div style="display:flex;flex-direction:column;gap:16px">
            <?php foreach ($order_items as $item): ?>
            <div style="display:flex;gap:20px;padding:20px;background:var(--bg-light);border-radius:14px;transition:0.3s" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='var(--bg-light)'">
                <div style="width:90px;height:90px;border-radius:12px;overflow:hidden;background:white;flex-shrink:0">
                    <?php if ($item['image_path']): ?>
                    <img src="<?php echo UPLOAD_URL . '/products/' . $item['image_path']; ?>" style="width:100%;height:100%;object-fit:cover">
                    <?php else: ?>
                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center"><i class="bi bi-image" style="font-size:36px;color:var(--text-light)"></i></div>
                    <?php endif; ?>
                </div>
                <div style="flex:1">
                    <h4 style="font-size:17px;margin-bottom:6px;font-weight:600"><?php echo escape($item['name_' . $lang]); ?></h4>
                    <p style="font-size:14px;color:var(--text-light);margin-bottom:10px">الكمية: <strong><?php echo $item['quantity']; ?></strong> × <?php echo formatPrice($item['unit_price']); ?></p>
                </div>
                <strong style="font-size:22px;color:var(--primary);align-self:center"><?php echo formatPrice($item['total_price']); ?></strong>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

</div>
</section>

<?php include 'includes/footer.php'; ?>