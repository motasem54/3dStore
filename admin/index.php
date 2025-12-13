<?php
require_once '../includes/store-init.php';
if (!isAdmin()) redirect('/admin/login.php');

$page_title = 'لوحة التحكم';

// Statistics
$total_orders = $db->fetchOne("SELECT COUNT(*) as count FROM orders")['count'];
$total_revenue = $db->fetchOne("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'")['total'] ?? 0;
$total_products = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE status = 'active'")['count'];
$total_users = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")['count'];

$pending_orders = $db->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")['count'];
$low_stock = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE stock < 10 AND stock > 0")['count'];
$out_stock = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE stock = 0")['count'];

// Recent Orders
$recent_orders = $db->fetchAll("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");

// Sales Chart Data (Last 7 days)
$sales_chart = $db->fetchAll("
    SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue
    FROM orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");

// Top Products
$top_products = $db->fetchAll("SELECT * FROM top_products LIMIT 5");

include 'includes/header.php';
?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,#667eea,#764ba2);color:white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">إجمالي المبيعات</h6>
                    <i class="bi bi-cash-stack" style="font-size:2rem;opacity:0.3"></i>
                </div>
                <h2 class="mb-0"><?php echo formatPrice($total_revenue); ?></h2>
                <small style="opacity:0.8"><?php echo $total_orders; ?> طلب</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,#f093fb,#f5576c);color:white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">المنتجات</h6>
                    <i class="bi bi-box-seam" style="font-size:2rem;opacity:0.3"></i>
                </div>
                <h2 class="mb-0"><?php echo $total_products; ?></h2>
                <small style="opacity:0.8"><?php echo $low_stock; ?> مخزون منخفض</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,#4facfe,#00f2fe);color:white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">العملاء</h6>
                    <i class="bi bi-people" style="font-size:2rem;opacity:0.3"></i>
                </div>
                <h2 class="mb-0"><?php echo $total_users; ?></h2>
                <small style="opacity:0.8">عميل مسجل</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,#fa709a,#fee140);color:white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">طلبات قيد المعالجة</h6>
                    <i class="bi bi-clock-history" style="font-size:2rem;opacity:0.3"></i>
                </div>
                <h2 class="mb-0"><?php echo $pending_orders; ?></h2>
                <small style="opacity:0.8">بانتظار التنفيذ</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-graph-up text-primary"></i> مبيعات آخر 7 أيام</h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="80"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle text-warning"></i> تنبيهات</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php if ($pending_orders > 0): ?>
                    <a href="orders/" class="list-group-item list-group-item-action border-0">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <span class="badge bg-warning rounded-circle" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center"><?php echo $pending_orders; ?></span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">طلبات جديدة</h6>
                                <small class="text-muted">بحاجة إلى مراجعة</small>
                            </div>
                        </div>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($low_stock > 0): ?>
                    <a href="products/?filter=low_stock" class="list-group-item list-group-item-action border-0">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <span class="badge bg-danger rounded-circle" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center"><?php echo $low_stock; ?></span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">مخزون منخفض</h6>
                                <small class="text-muted">منتجات أقل من 10</small>
                            </div>
                        </div>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($out_stock > 0): ?>
                    <a href="products/?filter=out_stock" class="list-group-item list-group-item-action border-0">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <span class="badge bg-secondary rounded-circle" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center"><?php echo $out_stock; ?></span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">نفذ المخزون</h6>
                                <small class="text-muted">منتجات غير متوفرة</small>
                            </div>
                        </div>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-clock-history text-primary"></i> آخر الطلبات</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>رقم الطلب</th>
                                <th>العميل</th>
                                <th>المبلغ</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td><a href="orders/view.php?id=<?php echo $order['id']; ?>" class="fw-bold text-primary">#<?php echo $order['order_number']; ?></a></td>
                                <td><?php echo escape($order['customer_name']); ?></td>
                                <td><?php echo formatPrice($order['total_amount']); ?></td>
                                <td>
                                    <?php
                                    $status_badges = ['pending' => 'warning', 'processing' => 'info', 'shipped' => 'primary', 'delivered' => 'success', 'cancelled' => 'danger'];
                                    $status_names = ['pending' => 'قيد المراجعة', 'processing' => 'قيد التجهيز', 'shipped' => 'تم الشحن', 'delivered' => 'تم التوصيل', 'cancelled' => 'ملغي'];
                                    ?>
                                    <span class="badge bg-<?php echo $status_badges[$order['status']]; ?>"><?php echo $status_names[$order['status']]; ?></span>
                                </td>
                                <td><small><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-star text-warning"></i> الأكثر مبيعاً</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($top_products as $product): ?>
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <?php if ($product['image_path']): ?>
                                <img src="<?php echo UPLOAD_URL . '/products/' . $product['image_path']; ?>" style="width:50px;height:50px;object-fit:cover;border-radius:8px">
                                <?php else: ?>
                                <div style="width:50px;height:50px;background:#f0f0f0;border-radius:8px;display:flex;align-items:center;justify-content:center"><i class="bi bi-image text-muted"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0"><?php echo escape($product['name_ar']); ?></h6>
                                <small class="text-muted">تم بيع <?php echo $product['total_quantity_sold']; ?> قطعة</small>
                            </div>
                            <div class="fw-bold text-primary"><?php echo formatPrice($product['total_revenue']); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const salesData = <?php echo json_encode($sales_chart); ?>;
const ctx = document.getElementById('salesChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: salesData.map(d => d.date),
        datasets: [{
            label: 'المبيعات',
            data: salesData.map(d => d.revenue),
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: (ctx) => 'المبيعات: ' + ctx.parsed.y.toFixed(2) + ' ₪'
                }
            }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>