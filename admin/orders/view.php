<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($order_id <= 0) {
    header('Location: index.php');
    exit;
}

$order = $db->fetch(
    "SELECT o.*, u.username, u.email as user_email 
     FROM orders o 
     LEFT JOIN users u ON o.user_id = u.id 
     WHERE o.id = ?",
    [$order_id]
);

if (!$order) {
    header('Location: index.php');
    exit;
}

// Get order items
$items = $db->fetchAll(
    "SELECT oi.*, p.image_path, p.product_type 
     FROM order_items oi 
     LEFT JOIN products p ON oi.product_id = p.id 
     WHERE oi.order_id = ?",
    [$order_id]
);

// Get status history
$history = $db->fetchAll(
    "SELECT sh.*, u.username 
     FROM order_status_history sh 
     LEFT JOIN users u ON sh.user_id = u.id 
     WHERE sh.order_id = ? 
     ORDER BY sh.created_at DESC",
    [$order_id]
);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (empty($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
        $error = 'خطأ في التحقق';
    } else {
        $new_status = $_POST['status'] ?? '';
        $notes = sanitizeInput($_POST['notes'] ?? '');
        $send_email = isset($_POST['send_email']);
        $send_whatsapp = isset($_POST['send_whatsapp']);
        
        $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
        if (in_array($new_status, $allowed_statuses, true)) {
            $db->beginTransaction();
            try {
                // Update order status
                $db->update(
                    "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?",
                    [$new_status, $order_id]
                );
                
                // Add to history
                $db->insert(
                    "INSERT INTO order_status_history (order_id, status, notes, notified_customer, user_id, created_at) 
                     VALUES (?, ?, ?, ?, ?, NOW())",
                    [$order_id, $new_status, $notes, ($send_email || $send_whatsapp) ? 1 : 0, $_SESSION['admin_user_id']]
                );
                
                // Send email if checked
                if ($send_email && !empty($order['customer_email'])) {
                    $subject = "تحديث حالة طلبك #{$order['order_number']}";
                    $body = "عزيزي {$order['customer_name']},\n\nتم تحديث حالة طلبك إلى: {$new_status}\n\nالملاحظات: {$notes}\n\nشكراً لك";
                    sendEmail($order['customer_email'], $subject, $body);
                }
                
                // Send WhatsApp if checked
                if ($send_whatsapp && !empty($order['customer_phone'])) {
                    // Will implement WhatsApp API later
                    $db->insert(
                        "INSERT INTO whatsapp_logs (phone_number, message, status, created_at) VALUES (?, ?, 'pending', NOW())",
                        [$order['customer_phone'], "تحديث طلب #{$order['order_number']}: {$new_status}"]
                    );
                }
                
                $db->commit();
                $success = 'تم تحديث حالة الطلب بنجاح';
                
                // Refresh order data
                $order = $db->fetch("SELECT * FROM orders WHERE id = ?", [$order_id]);
                $history = $db->fetchAll(
                    "SELECT sh.*, u.username FROM order_status_history sh 
                     LEFT JOIN users u ON sh.user_id = u.id 
                     WHERE sh.order_id = ? ORDER BY sh.created_at DESC",
                    [$order_id]
                );
            } catch (Exception $e) {
                $db->rollback();
                $error = 'حدث خطأ أثناء التحديث';
            }
        }
    }
}

$page_title = 'تفاصيل الطلب #' . $order['order_number'];
$active_page = 'orders';

include __DIR__ . '/../includes/header.php';
?>

<?php if (isset($success)): ?>
<div class="alert alert-success">
    <i class="bi bi-check-circle"></i> <?php echo $success; ?>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger">
    <i class="bi bi-exclamation-circle"></i> <?php echo $error; ?>
</div>
<?php endif; ?>

<div class="order-details-grid">
    <!-- Order Info -->
    <div class="glass-card">
        <div class="card-header space-between">
            <h4><i class="bi bi-receipt"></i> معلومات الطلب</h4>
            <div style="display: flex; gap: 8px;">
                <a href="print.php?id=<?php echo $order_id; ?>" class="btn-sm" target="_blank">
                    <i class="bi bi-printer"></i> طباعة
                </a>
                <a href="index.php" class="btn-sm">
                    <i class="bi bi-arrow-right"></i> رجوع
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>رقم الطلب</label>
                    <strong><?php echo escape($order['order_number']); ?></strong>
                </div>
                <div class="info-item">
                    <label>التاريخ</label>
                    <span><?php echo formatDate($order['created_at'], 'd/m/Y H:i'); ?></span>
                </div>
                <div class="info-item">
                    <label>الحالة</label>
                    <?php echo getStatusBadge($order['status']); ?>
                </div>
                <div class="info-item">
                    <label>نوع الطلب</label>
                    <span class="badge"><?php echo $order['order_type'] === 'online' ? 'أونلاين' : 'POS'; ?></span>
                </div>
            </div>
            
            <hr style="margin: 20px 0; border: 0; border-top: 1px solid var(--glass-border);">
            
            <div class="info-grid">
                <div class="info-item">
                    <label>اسم العميل</label>
                    <strong><?php echo escape($order['customer_name']); ?></strong>
                </div>
                <div class="info-item">
                    <label>الهاتف</label>
                    <span dir="ltr"><?php echo escape($order['customer_phone']); ?></span>
                </div>
                <div class="info-item">
                    <label>البريد الإلكتروني</label>
                    <span><?php echo escape($order['customer_email'] ?? '-'); ?></span>
                </div>
                <div class="info-item">
                    <label>الحساب</label>
                    <span><?php echo escape($order['username'] ?? 'زائر'); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Update Status -->
    <div class="glass-card">
        <div class="card-header">
            <h4><i class="bi bi-arrow-repeat"></i> تحديث حالة الطلب</h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf" value="<?php echo escape($_SESSION['csrf']); ?>">
                
                <div class="form-group">
                    <label>الحالة الجديدة</label>
                    <select name="status" class="form-control" required>
                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>قيد الانتظار</option>
                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>قيد المعالجة</option>
                        <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>تم الشحن</option>
                        <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>تم التسليم</option>
                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>ملغي</option>
                        <option value="refunded" <?php echo $order['status'] === 'refunded' ? 'selected' : ''; ?>>مسترد</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>ملاحظات (اختياري)</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="أضف ملاحظة عن التحديث..."></textarea>
                </div>
                
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="send_email">
                        <span>إرسال إشعار بالبريد الإلكتروني</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="send_whatsapp">
                        <span>إرسال إشعار عبر واتساب</span>
                    </label>
                </div>
                
                <button type="submit" name="update_status" class="btn-primary btn-block">
                    <i class="bi bi-check-circle"></i> تحديث الحالة
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Order Items -->
<div class="glass-card" style="margin-top: 24px;">
    <div class="card-header">
        <h4><i class="bi bi-basket"></i> منتجات الطلب</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>SKU</th>
                        <th>النوع</th>
                        <th>الكمية</th>
                        <th>السعر</th>
                        <th>المجموع</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <?php if ($item['image_path']): ?>
                                <img src="<?php echo UPLOAD_URL . '/products/' . $item['image_path']; ?>" 
                                     alt="" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover;">
                                <?php endif; ?>
                                <strong><?php echo escape($item['product_name']); ?></strong>
                            </div>
                        </td>
                        <td class="text-muted"><?php echo escape($item['product_sku'] ?? '-'); ?></td>
                        <td>
                            <?php if ($item['product_type'] === '3d'): ?>
                                <span class="badge badge-info">3D</span>
                            <?php else: ?>
                                <span class="badge">عادي</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format($item['quantity']); ?></td>
                        <td><?php echo formatPrice($item['unit_price'], $order['currency']); ?></td>
                        <td><strong><?php echo formatPrice($item['total'], $order['currency']); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align: left;"><strong>المجموع الفرعي:</strong></td>
                        <td><strong><?php echo formatPrice($order['subtotal'], $order['currency']); ?></strong></td>
                    </tr>
                    <?php if ($order['tax'] > 0): ?>
                    <tr>
                        <td colspan="5" style="text-align: left;">الضريبة:</td>
                        <td><?php echo formatPrice($order['tax'], $order['currency']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($order['discount'] > 0): ?>
                    <tr>
                        <td colspan="5" style="text-align: left;">الخصم:</td>
                        <td class="text-danger">- <?php echo formatPrice($order['discount'], $order['currency']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($order['shipping_cost'] > 0): ?>
                    <tr>
                        <td colspan="5" style="text-align: left;">الشحن:</td>
                        <td><?php echo formatPrice($order['shipping_cost'], $order['currency']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr style="background: rgba(124, 92, 255, 0.1);">
                        <td colspan="5" style="text-align: left;"><strong style="font-size: 18px;">المجموع الكلي:</strong></td>
                        <td><strong style="font-size: 18px; color: var(--success);"><?php echo formatPrice($order['total_amount'], $order['currency']); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Status History -->
<div class="glass-card" style="margin-top: 24px;">
    <div class="card-header">
        <h4><i class="bi bi-clock-history"></i> سجل تغيير الحالات</h4>
    </div>
    <div class="card-body">
        <?php if (!empty($history)): ?>
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
                    <small class="text-muted">بواسطة: <?php echo escape($h['username'] ?? 'النظام'); ?></small>
                    <?php if ($h['notified_customer']): ?>
                    <span class="badge badge-info" style="margin-right: 8px;">تم إشعار العميل</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-center text-muted">لا يوجد سجل لتغيير الحالات</p>
        <?php endif; ?>
    </div>
</div>

<style>
.order-details-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
.info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
.info-item label { display: block; color: var(--text-muted); font-size: 13px; margin-bottom: 4px; }
.checkbox-group { display: flex; flex-direction: column; gap: 10px; margin: 16px 0; }
.checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
.checkbox-label input { width: 18px; height: 18px; cursor: pointer; }
.alert-success { background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
.timeline { position: relative; padding-right: 30px; }
.timeline::before { content: ''; position: absolute; right: 10px; top: 0; bottom: 0; width: 2px; background: var(--glass-border); }
.timeline-item { position: relative; margin-bottom: 24px; }
.timeline-marker { position: absolute; right: -20px; width: 12px; height: 12px; border-radius: 50%; background: var(--primary); border: 3px solid rgba(255, 255, 255, 0.2); }
.timeline-content { background: rgba(255, 255, 255, 0.05); padding: 16px; border-radius: 12px; }
.timeline-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
@media (max-width: 768px) {
    .order-details-grid { grid-template-columns: 1fr; }
    .info-grid { grid-template-columns: 1fr; }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>