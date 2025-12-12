<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'لوحة التحكم الرئيسية';
$active_page = 'dashboard';

// احصائيات
$stats = [
    'total_products' => (int)$db->query("SELECT COUNT(*) as count FROM products")->fetch()['count'],
    'total_orders' => (int)$db->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'],
    'pending_orders' => (int)$db->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch()['count'],
    'total_customers' => (int)$db->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch()['count'],
    'total_revenue' => (float)($db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE payment_status = 'paid'")->fetch()['total'] ?? 0),
    'low_stock' => (int)$db->query("SELECT COUNT(*) as count FROM products WHERE stock_quantity <= reorder_level AND track_inventory = 1")->fetch()['count'],
];

// آخر الطلبات
$recent_orders = $db->fetchAll(
    "SELECT o.*, u.username FROM orders o 
     LEFT JOIN users u ON o.user_id = u.id 
     ORDER BY o.created_at DESC LIMIT 6"
);

include 'includes/header.php';
?>

<div class="dashboard-grid">
    <!-- KPI Cards -->
    <div class="kpi-row">
        <div class="glass-card kpi-card kpi-primary">
            <div class="kpi-icon"><i class="bi bi-box-seam"></i></div>
            <div class="kpi-content">
                <h3><?php echo number_format($stats['total_products']); ?></h3>
                <p>إجمالي المنتجات</p>
            </div>
        </div>
        
        <div class="glass-card kpi-card kpi-success">
            <div class="kpi-icon"><i class="bi bi-cart-check"></i></div>
            <div class="kpi-content">
                <h3><?php echo number_format($stats['total_orders']); ?></h3>
                <p>إجمالي الطلبات</p>
            </div>
        </div>
        
        <div class="glass-card kpi-card kpi-warning">
            <div class="kpi-icon"><i class="bi bi-clock-history"></i></div>
            <div class="kpi-content">
                <h3><?php echo number_format($stats['pending_orders']); ?></h3>
                <p>طلبات قيد الانتظار</p>
            </div>
        </div>
        
        <div class="glass-card kpi-card kpi-info">
            <div class="kpi-icon"><i class="bi bi-people"></i></div>
            <div class="kpi-content">
                <h3><?php echo number_format($stats['total_customers']); ?></h3>
                <p>العملاء المسجلين</p>
            </div>
        </div>
    </div>
    
    <!-- Revenue & Alerts -->
    <div class="row-2-cols">
        <div class="glass-card">
            <div class="card-header">
                <h4><i class="bi bi-graph-up"></i> إجمالي المبيعات</h4>
            </div>
            <div class="card-body text-center">
                <div class="revenue-amount"><?php echo formatPrice($stats['total_revenue']); ?></div>
                <p class="text-muted">الإيرادات الإجمالية</p>
            </div>
        </div>
        
        <div class="glass-card">
            <div class="card-header">
                <h4><i class="bi bi-exclamation-triangle"></i> تنبيهات المخزون</h4>
            </div>
            <div class="card-body text-center">
                <div class="alert-number"><?php echo number_format($stats['low_stock']); ?></div>
                <p class="text-muted">منتج يحتاج إعادة طلب</p>
                <?php if ($stats['low_stock'] > 0): ?>
                <a href="inventory/" class="btn-warning btn-sm">عرض التفاصيل</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="glass-card">
        <div class="card-header space-between">
            <h4><i class="bi bi-receipt"></i> آخر الطلبات</h4>
            <a href="orders/" class="btn-sm">عرض الكل</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>العميل</th>
                            <th>المبلغ</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td><strong><?php echo escape($order['order_number']); ?></strong></td>
                            <td><?php echo escape($order['customer_name']); ?></td>
                            <td><?php echo formatPrice($order['total_amount'], $order['currency']); ?></td>
                            <td><?php echo getStatusBadge($order['status']); ?></td>
                            <td class="text-muted"><?php echo formatDate($order['created_at'], 'd/m/Y'); ?></td>
                            <td>
                                <a href="orders/view.php?id=<?php echo $order['id']; ?>" class="btn-icon" title="عرض">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recent_orders)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">لا توجد طلبات</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>