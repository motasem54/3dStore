<?php
require_once '../../includes/store-init.php';
if (!isAdmin()) redirect('/admin/login.php');

$page_title = 'إدارة الشحنات';

$company_id = $_GET['company'] ?? null;
$company = null;

if ($company_id) {
    $company = $db->fetchOne("SELECT * FROM shipping_companies WHERE id = ?", [$company_id]);
    if (!$company) redirect('/admin/shipping/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $shipment_number = 'SHIP-' . date('Ymd') . '-' . rand(1000, 9999);
        $order_ids = $_POST['order_ids'] ?? [];
        
        if (empty($order_ids)) {
            $_SESSION['error'] = 'يجب اختيار طلب واحد على الأقل';
        } else {
            // Calculate totals
            $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
            $orders = $db->fetchAll("SELECT * FROM orders WHERE id IN ($placeholders)", $order_ids);
            
            $total_amount = array_sum(array_column($orders, 'total_amount'));
            $shipping_cost = (float)$_POST['shipping_cost'];
            $commission = (float)$_POST['commission'];
            $net_amount = $total_amount - $shipping_cost - $commission;
            
            // Insert shipment
            $db->execute(
                "INSERT INTO shipping_shipments (company_id, shipment_number, shipment_date, total_orders, total_amount, shipping_cost, company_commission, net_amount, status, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $_POST['company_id'],
                    $shipment_number,
                    $_POST['shipment_date'],
                    count($order_ids),
                    $total_amount,
                    $shipping_cost,
                    $commission,
                    $net_amount,
                    'pending',
                    $_POST['notes'],
                    $_SESSION['user_id']
                ]
            );
            
            $shipment_id = $db->lastInsertId();
            
            // Insert shipment items
            foreach ($order_ids as $order_id) {
                $tracking = 'TRK-' . $shipment_number . '-' . $order_id;
                $db->execute(
                    "INSERT INTO shipment_items (shipment_id, order_id, tracking_number, status) VALUES (?, ?, ?, 'pending')",
                    [$shipment_id, $order_id, $tracking]
                );
                
                // Update order
                $db->execute(
                    "UPDATE orders SET shipping_company_id = ?, tracking_number = ?, status = 'processing' WHERE id = ?",
                    [$_POST['company_id'], $tracking, $order_id]
                );
            }
            
            $_SESSION['success'] = 'تم إنشاء الشحنة بنجاح: ' . $shipment_number;
        }
        redirect('/admin/shipping/shipments.php?company=' . $_POST['company_id']);
    }
}

$where = $company_id ? "WHERE company_id = $company_id" : '';
$shipments = $db->fetchAll("SELECT * FROM shipping_shipments $where ORDER BY created_at DESC");

$pending_orders = $db->fetchAll("
    SELECT o.*, u.name as customer_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.status IN ('pending', 'confirmed') AND o.shipping_company_id IS NULL
    ORDER BY o.created_at DESC
    LIMIT 50
");

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-box-seam text-primary"></i> 
        <?php echo $company ? 'شحنات ' . escape($company['name_ar']) : 'جميع الشحنات'; ?>
    </h2>
    <div>
        <?php if ($company_id): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createShipmentModal">
            <i class="bi bi-plus-circle"></i> إنشاء شحنة جديدة
        </button>
        <?php endif; ?>
        <a href="/admin/shipping/" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-right"></i> عودة
        </a>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>رقم الشحنة</th>
                        <th>الشركة</th>
                        <th>التاريخ</th>
                        <th>عدد الطلبات</th>
                        <th>إجمالي المبلغ</th>
                        <th>مصاريف الشحن</th>
                        <th>عمولة الشركة</th>
                        <th>الصافي</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shipments as $shipment): 
                        $company_data = $db->fetchOne("SELECT name_ar FROM shipping_companies WHERE id = ?", [$shipment['company_id']]);
                    ?>
                    <tr>
                        <td><strong><?php echo $shipment['shipment_number']; ?></strong></td>
                        <td><?php echo escape($company_data['name_ar']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($shipment['shipment_date'])); ?></td>
                        <td><span class="badge bg-info"><?php echo $shipment['total_orders']; ?></span></td>
                        <td><?php echo formatPrice($shipment['total_amount']); ?></td>
                        <td><span class="text-danger"><?php echo formatPrice($shipment['shipping_cost']); ?></span></td>
                        <td><span class="text-warning"><?php echo formatPrice($shipment['company_commission']); ?></span></td>
                        <td><strong class="text-success"><?php echo formatPrice($shipment['net_amount']); ?></strong></td>
                        <td>
                            <?php
                            $status_badges = ['pending' => 'warning', 'shipped' => 'primary', 'delivered' => 'success', 'returned' => 'danger'];
                            $status_names = ['pending' => 'قيد المعالجة', 'shipped' => 'تم الشحن', 'delivered' => 'تم التوصيل', 'returned' => 'مرتجع'];
                            ?>
                            <span class="badge bg-<?php echo $status_badges[$shipment['status']]; ?>"><?php echo $status_names[$shipment['status']]; ?></span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="view-shipment.php?id=<?php echo $shipment['id']; ?>" class="btn btn-outline-primary" title="عرض">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn btn-outline-success" onclick="markDelivered(<?php echo $shipment['id']; ?>)" title="تم التسليم">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Shipment Modal -->
<div class="modal fade" id="createShipmentModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إنشاء شحنة جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="company_id" value="<?php echo $company_id; ?>">
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">تاريخ الشحن</label>
                            <input type="date" name="shipment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">تكلفة الشحن</label>
                            <input type="number" step="0.01" name="shipping_cost" class="form-control" value="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">عمولة الشركة</label>
                            <input type="number" step="0.01" name="commission" class="form-control" value="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <h6 class="mb-3">اختر الطلبات:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th width="50"><input type="checkbox" id="selectAll" onclick="toggleAll(this)"></th>
                                    <th>رقم الطلب</th>
                                    <th>العميل</th>
                                    <th>المبلغ</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_orders as $order): ?>
                                <tr>
                                    <td><input type="checkbox" name="order_ids[]" value="<?php echo $order['id']; ?>" class="order-checkbox"></td>
                                    <td><strong>#<?php echo $order['order_number']; ?></strong></td>
                                    <td><?php echo escape($order['customer_name']); ?></td>
                                    <td><?php echo formatPrice($order['total_amount']); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">إنشاء الشحنة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleAll(checkbox) {
    document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = checkbox.checked);
}

function markDelivered(id) {
    if (confirm('هل تم تسليم هذه الشحنة؟')) {
        fetch('api/update-shipment.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: id, status: 'delivered'})
        }).then(() => location.reload());
    }
}
</script>

<?php include '../includes/footer.php'; ?>