<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

$page_title = 'التقارير والإحصائيات';
$active_page = 'reports';

// Date filters
$from_date = $_GET['from'] ?? date('Y-m-01');
$to_date = $_GET['to'] ?? date('Y-m-d');

// Sales Stats
$sales_stats = $db->fetch(
    "SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
        SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as total_revenue,
        SUM(CASE WHEN status = 'completed' THEN tax ELSE 0 END) as total_tax
     FROM orders 
     WHERE DATE(created_at) BETWEEN ? AND ?",
    [$from_date, $to_date]
);

// Top Products
$top_products = $db->fetchAll(
    "SELECT p.name_ar, p.sku, SUM(oi.quantity) as total_sold, SUM(oi.total) as revenue
     FROM order_items oi
     JOIN products p ON oi.product_id = p.id
     JOIN orders o ON oi.order_id = o.id
     WHERE o.status = 'completed' AND DATE(o.created_at) BETWEEN ? AND ?
     GROUP BY p.id
     ORDER BY total_sold DESC
     LIMIT 10",
    [$from_date, $to_date]
);

// Daily Sales
$daily_sales = $db->fetchAll(
    "SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue
     FROM orders
     WHERE status = 'completed' AND DATE(created_at) BETWEEN ? AND ?
     GROUP BY DATE(created_at)
     ORDER BY date DESC
     LIMIT 30",
    [$from_date, $to_date]
);

// Low Stock Products
$low_stock = $db->fetchAll(
    "SELECT name_ar, sku, stock_quantity, reorder_level
     FROM products
     WHERE stock_quantity <= reorder_level
     ORDER BY stock_quantity ASC
     LIMIT 10"
);

include __DIR__ . '/../includes/header.php';
?>

<div class="glass-card" style="margin-bottom: 20px;">
    <div class="card-header space-between">
        <h4><i class="bi bi-graph-up"></i> التقارير والإحصائيات</h4>
        <form method="GET" style="display: flex; gap: 10px;">
            <input type="date" name="from" class="form-control" value="<?php echo $from_date; ?>" style="width: auto;">
            <input type="date" name="to" class="form-control" value="<?php echo $to_date; ?>" style="width: auto;">
            <button type="submit" class="btn-primary"><i class="bi bi-filter"></i> تصفية</button>
        </form>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card glass-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.2);"><i class="bi bi-receipt" style="color: #10b981;"></i></div>
        <div class="stat-info">
            <span class="stat-label">إجمالي الطلبات</span>
            <strong class="stat-value"><?php echo number_format($sales_stats['total_orders'] ?? 0); ?></strong>
        </div>
    </div>
    
    <div class="stat-card glass-card">
        <div class="stat-icon" style="background: rgba(59, 130, 246, 0.2);"><i class="bi bi-check-circle" style="color: #3b82f6;"></i></div>
        <div class="stat-info">
            <span class="stat-label">طلبات مكتملة</span>
            <strong class="stat-value"><?php echo number_format($sales_stats['completed_orders'] ?? 0); ?></strong>
        </div>
    </div>
    
    <div class="stat-card glass-card">
        <div class="stat-icon" style="background: rgba(124, 92, 255, 0.2);"><i class="bi bi-cash-stack" style="color: #7c5cff;"></i></div>
        <div class="stat-info">
            <span class="stat-label">إجمالي المبيعات</span>
            <strong class="stat-value"><?php echo formatPrice($sales_stats['total_revenue'] ?? 0); ?></strong>
        </div>
    </div>
    
    <div class="stat-card glass-card">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.2);"><i class="bi bi-percent" style="color: #f59e0b;"></i></div>
        <div class="stat-info">
            <span class="stat-label">الضرائب المحصلة</span>
            <strong class="stat-value"><?php echo formatPrice($sales_stats['total_tax'] ?? 0); ?></strong>
        </div>
    </div>
</div>

<div class="reports-grid">
    <div class="glass-card">
        <div class="card-header"><h4><i class="bi bi-graph-up"></i> المبيعات اليومية</h4></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>التاريخ</th><th>عدد الطلبات</th><th>المبيعات</th></tr></thead>
                    <tbody>
                        <?php foreach ($daily_sales as $day): ?>
                        <tr>
                            <td><?php echo formatDate($day['date'], 'd/m/Y'); ?></td>
                            <td><?php echo number_format($day['orders']); ?></td>
                            <td><strong><?php echo formatPrice($day['revenue']); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="glass-card">
        <div class="card-header"><h4><i class="bi bi-trophy"></i> المنتجات الأكثر مبيعاً</h4></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>المنتج</th><th>الكمية المباعة</th><th>الإيرادات</th></tr></thead>
                    <tbody>
                        <?php foreach ($top_products as $product): ?>
                        <tr>
                            <td><strong><?php echo escape($product['name_ar']); ?></strong><br><small class="text-muted"><?php echo escape($product['sku']); ?></small></td>
                            <td><?php echo number_format($product['total_sold']); ?></td>
                            <td><strong><?php echo formatPrice($product['revenue']); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($low_stock)): ?>
<div class="glass-card" style="margin-top: 20px;">
    <div class="card-header"><h4><i class="bi bi-exclamation-triangle"></i> تنبيه: منتجات ناقصة في المخزون</h4></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>المنتج</th><th>SKU</th><th>الكمية الحالية</th><th>حد إعادة الطلب</th></tr></thead>
                <tbody>
                    <?php foreach ($low_stock as $product): ?>
                    <tr>
                        <td><?php echo escape($product['name_ar']); ?></td>
                        <td><?php echo escape($product['sku']); ?></td>
                        <td><span class="badge badge-danger"><?php echo $product['stock_quantity']; ?></span></td>
                        <td><?php echo $product['reorder_level']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
.stat-card { padding: 24px; display: flex; gap: 20px; align-items: center; }
.stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px; }
.stat-info { flex: 1; }
.stat-label { display: block; color: var(--text-muted); font-size: 14px; margin-bottom: 8px; }
.stat-value { font-size: 28px; color: var(--text-primary); }
.reports-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 20px; }
@media (max-width: 968px) { .reports-grid { grid-template-columns: 1fr; } }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>