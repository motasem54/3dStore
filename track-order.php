<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/init.php';

$page_title = 'تتبع الطلب';
$order = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_number = sanitizeInput($_POST['order_number'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    
    if (!empty($order_number) && !empty($phone)) {
        $order = $db->fetch(
            "SELECT * FROM orders WHERE order_number = ? AND customer_phone = ?",
            [$order_number, $phone]
        );
        
        if ($order) {
            $items = $db->fetchAll(
                "SELECT oi.*, p.image_path FROM order_items oi 
                 LEFT JOIN products p ON oi.product_id = p.id 
                 WHERE oi.order_id = ?",
                [$order['id']]
            );
            
            $history = $db->fetchAll(
                "SELECT * FROM order_status_history 
                 WHERE order_id = ? 
                 ORDER BY created_at ASC",
                [$order['id']]
            );
        } else {
            $error = 'لم يتم العثور على الطلب. تأكد من رقم الطلب ورقم الهاتف.';
        }
    } else {
        $error = 'يرجى إدخال رقم الطلب ورقم الهاتف';
    }
}

include __DIR__ . '/includes/header-public.php';
?>

<div class="track-order-page">
    <div class="container">
        <div class="glass-card" style="max-width: 600px; margin: 40px auto;">
            <div class="card-header">
                <h2 style="text-align: center;"><i class="bi bi-search"></i> تتبع طلبك</h2>
            </div>
            
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!$order): ?>
                <form method="POST" class="track-form">
                    <div class="form-group">
                        <label>رقم الطلب</label>
                        <input type="text" name="order_number" class="form-control" 
                               placeholder="مثال: ORD-2024-00001" required>
                    </div>
                    
                    <div class="form-group">
                        <label>رقم الهاتف</label>
                        <input type="text" name="phone" class="form-control" 
                               placeholder="رقم الهاتف المسجل في الطلب" required>
                    </div>
                    
                    <button type="submit" class="btn-primary btn-block">
                        <i class="bi bi-search"></i> تتبع الطلب
                    </button>
                </form>
                <?php else: ?>
                <!-- Order Found -->
                <div class="order-info">
                    <div class="info-row">
                        <span>رقم الطلب:</span>
                        <strong><?php echo escape($order['order_number']); ?></strong>
                    </div>
                    <div class="info-row">
                        <span>التاريخ:</span>
                        <span><?php echo formatDate($order['created_at'], 'd/m/Y H:i'); ?></span>
                    </div>
                    <div class="info-row">
                        <span>الحالة:</span>
                        <?php echo getStatusBadge($order['status']); ?>
                    </div>
                    <div class="info-row">
                        <span>المبلغ الإجمالي:</span>
                        <strong style="color: var(--success);"><?php echo formatPrice($order['total_amount'], $order['currency']); ?></strong>
                    </div>
                </div>
                
                <hr style="margin: 25px 0; border: 0; border-top: 1px solid var(--glass-border);">
                
                <h5 style="margin-bottom: 15px;">سجل الطلب</h5>
                <div class="timeline">
                    <?php foreach ($history as $h): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <?php echo getStatusBadge($h['status']); ?>
                                <span class="text-muted" style="font-size: 13px;"><?php echo formatDate($h['created_at'], 'd/m/Y H:i'); ?></span>
                            </div>
                            <?php if ($h['notes']): ?>
                            <p class="text-muted" style="margin: 8px 0 0 0;"><?php echo escape($h['notes']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <hr style="margin: 25px 0; border: 0; border-top: 1px solid var(--glass-border);">
                
                <h5 style="margin-bottom: 15px;">المنتجات</h5>
                <?php foreach ($items as $item): ?>
                <div class="product-item">
                    <?php if ($item['image_path']): ?>
                    <img src="<?php echo UPLOAD_URL . '/products/' . $item['image_path']; ?>" alt="">
                    <?php endif; ?>
                    <div class="product-details">
                        <strong><?php echo escape($item['product_name']); ?></strong>
                        <span class="text-muted">الكمية: <?php echo $item['quantity']; ?> × <?php echo formatPrice($item['unit_price'], $order['currency']); ?></span>
                    </div>
                    <strong><?php echo formatPrice($item['total'], $order['currency']); ?></strong>
                </div>
                <?php endforeach; ?>
                
                <a href="?" class="btn-sm btn-block" style="margin-top: 20px;">
                    <i class="bi bi-arrow-right"></i> تتبع طلب آخر
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.track-order-page { min-height: calc(100vh - 200px); padding: 40px 20px; }
.track-form .form-group { margin-bottom: 20px; }
.order-info { background: rgba(255,255,255,0.05); padding: 20px; border-radius: 10px; margin-bottom: 20px; }
.info-row { display: flex; justify-content: space-between; margin-bottom: 12px; }
.timeline { position: relative; padding-right: 30px; }
.timeline::before { content: ''; position: absolute; right: 10px; top: 0; bottom: 0; width: 2px; background: var(--glass-border); }
.timeline-item { position: relative; margin-bottom: 24px; }
.timeline-marker { position: absolute; right: -20px; width: 12px; height: 12px; border-radius: 50%; background: var(--primary); border: 3px solid rgba(255, 255, 255, 0.2); }
.timeline-content { background: rgba(255, 255, 255, 0.05); padding: 16px; border-radius: 12px; }
.timeline-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.product-item { display: flex; gap: 12px; align-items: center; padding: 12px; background: rgba(255,255,255,0.05); border-radius: 8px; margin-bottom: 10px; }
.product-item img { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; }
.product-details { flex: 1; display: flex; flex-direction: column; gap: 4px; }
</style>

<?php include __DIR__ . '/includes/footer-public.php'; ?>