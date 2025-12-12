<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

$page_title = 'إدارة الطلبات';
$active_page = 'orders';

// Filter
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$where = [];
$params = [];

if ($status_filter !== 'all') {
    $where[] = "o.status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $where[] = "(o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_phone LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$total = $db->fetch("SELECT COUNT(*) as count FROM orders o {$whereClause}", $params)['count'] ?? 0;
$totalPages = ceil($total / $perPage);

// Get orders
$orders = $db->fetchAll(
    "SELECT o.*, u.username 
     FROM orders o 
     LEFT JOIN users u ON o.user_id = u.id 
     {$whereClause}
     ORDER BY o.created_at DESC 
     LIMIT {$perPage} OFFSET {$offset}",
    $params
);

include __DIR__ . '/../includes/header.php';
?>

<div class="glass-card">
    <div class="card-header space-between">
        <h4><i class="bi bi-receipt"></i> إدارة الطلبات (<?php echo number_format($total); ?>)</h4>
        <div style="display: flex; gap: 10px;">
            <a href="export.php" class="btn-sm"><i class="bi bi-download"></i> تصدير Excel</a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" class="filters-form">
            <div class="filters-row">
                <div class="form-group">
                    <input type="text" name="search" class="form-control" placeholder="بحث برقم الطلب أو اسم العميل..." value="<?php echo escape($search); ?>">
                </div>
                
                <div class="form-group">
                    <select name="status" class="form-control">
                        <option value="all">جميع الحالات</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>قيد الانتظار</option>
                        <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>قيد المعالجة</option>
                        <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>تم الشحن</option>
                        <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>تم التسليم</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>ملغي</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-primary"><i class="bi bi-search"></i> بحث</button>
                <a href="index.php" class="btn-sm"><i class="bi bi-x-circle"></i> إعادة تعيين</a>
            </div>
        </form>
        
        <div class="table-responsive" style="margin-top: 20px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>العميل</th>
                        <th>الهاتف</th>
                        <th>المبلغ</th>
                        <th>الحالة</th>
                        <th>الدفع</th>
                        <th>التاريخ</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong><?php echo escape($order['order_number']); ?></strong></td>
                        <td><?php echo escape($order['customer_name']); ?></td>
                        <td class="text-muted" dir="ltr"><?php echo escape($order['customer_phone']); ?></td>
                        <td><?php echo formatPrice($order['total_amount'], $order['currency']); ?></td>
                        <td><?php echo getStatusBadge($order['status']); ?></td>
                        <td>
                            <?php 
                            $payment_badges = [
                                'pending' => '<span class="badge badge-warning">معلق</span>',
                                'paid' => '<span class="badge badge-success">مدفوع</span>',
                                'failed' => '<span class="badge badge-danger">فشل</span>',
                                'refunded' => '<span class="badge badge-secondary">مسترد</span>',
                            ];
                            echo $payment_badges[$order['payment_status']] ?? '';
                            ?>
                        </td>
                        <td class="text-muted"><?php echo formatDate($order['created_at'], 'd/m/Y H:i'); ?></td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <a href="view.php?id=<?php echo $order['id']; ?>" class="btn-icon" title="عرض التفاصيل">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="print.php?id=<?php echo $order['id']; ?>" class="btn-icon" title="طباعة" target="_blank">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
                            <p>لا توجد طلبات</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>" class="btn-sm">السابق</a>
            <?php endif; ?>
            
            <span class="page-info">صفحة <?php echo $page; ?> من <?php echo $totalPages; ?></span>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>" class="btn-sm">التالي</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.filters-form { margin-bottom: 20px; }
.filters-row { display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; }
.filters-row .form-group { flex: 1; min-width: 200px; }
.pagination { display: flex; gap: 12px; align-items: center; justify-content: center; margin-top: 20px; }
.page-info { color: var(--text-muted); font-size: 14px; }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>