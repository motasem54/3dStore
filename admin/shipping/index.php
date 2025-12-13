<?php
require_once '../../includes/store-init.php';
if (!isAdmin()) redirect('/admin/login.php');

$page_title = 'شركات الشحن';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $db->execute(
            "INSERT INTO shipping_companies (name, name_ar, contact_person, phone, email, address, website, tracking_url, is_active, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $_POST['name'],
                $_POST['name_ar'],
                $_POST['contact_person'],
                $_POST['phone'],
                $_POST['email'],
                $_POST['address'],
                $_POST['website'],
                $_POST['tracking_url'],
                isset($_POST['is_active']) ? 1 : 0,
                $_POST['notes']
            ]
        );
        $_SESSION['success'] = 'تمت إضافة شركة الشحن بنجاح';
    } elseif ($_POST['action'] === 'edit') {
        $db->execute(
            "UPDATE shipping_companies SET name = ?, name_ar = ?, contact_person = ?, phone = ?, email = ?, address = ?, website = ?, tracking_url = ?, is_active = ?, notes = ? WHERE id = ?",
            [
                $_POST['name'],
                $_POST['name_ar'],
                $_POST['contact_person'],
                $_POST['phone'],
                $_POST['email'],
                $_POST['address'],
                $_POST['website'],
                $_POST['tracking_url'],
                isset($_POST['is_active']) ? 1 : 0,
                $_POST['notes'],
                $_POST['id']
            ]
        );
        $_SESSION['success'] = 'تم تحديث بيانات شركة الشحن';
    } elseif ($_POST['action'] === 'delete') {
        $db->execute("DELETE FROM shipping_companies WHERE id = ?", [$_POST['id']]);
        $_SESSION['success'] = 'تم حذف شركة الشحن';
    }
    redirect('/admin/shipping/');
}

$companies = $db->fetchAll("SELECT * FROM shipping_companies ORDER BY sort_order, name_ar");

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-truck text-primary"></i> شركات الشحن</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-circle"></i> إضافة شركة
    </button>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>الشركة</th>
                        <th>مسؤول الاتصال</th>
                        <th>الهاتف</th>
                        <th>البريد</th>
                        <th>عدد الشحنات</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($companies as $company): 
                        $shipments_count = $db->fetchOne("SELECT COUNT(*) as count FROM shipping_shipments WHERE company_id = ?", [$company['id']])['count'];
                        $total_amount = $db->fetchOne("SELECT SUM(net_amount) as total FROM shipping_shipments WHERE company_id = ?", [$company['id']])['total'] ?? 0;
                    ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?php echo escape($company['name_ar']); ?></div>
                            <small class="text-muted"><?php echo escape($company['name']); ?></small>
                        </td>
                        <td><?php echo escape($company['contact_person']); ?></td>
                        <td><?php echo escape($company['phone']); ?></td>
                        <td><?php echo escape($company['email']); ?></td>
                        <td>
                            <span class="badge bg-info"><?php echo $shipments_count; ?> شحنة</span>
                            <br><small class="text-muted"><?php echo formatPrice($total_amount); ?></small>
                        </td>
                        <td>
                            <?php if ($company['is_active']): ?>
                            <span class="badge bg-success">فعال</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">غير فعال</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="shipments.php?company=<?php echo $company['id']; ?>" class="btn btn-outline-info" title="الشحنات">
                                    <i class="bi bi-box-seam"></i>
                                </a>
                                <button class="btn btn-outline-primary" onclick="editCompany(<?php echo htmlspecialchars(json_encode($company)); ?>)" title="تعديل">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteCompany(<?php echo $company['id']; ?>)" title="حذف">
                                    <i class="bi bi-trash"></i>
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

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة شركة شحن</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الاسم (عربي) *</label>
                            <input type="text" name="name_ar" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الاسم (إنجليزي) *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">مسؤول الاتصال</label>
                            <input type="text" name="contact_person" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="tel" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الموقع الإلكتروني</label>
                            <input type="url" name="website" class="form-control">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">العنوان</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">رابط التتبع</label>
                            <input type="url" name="tracking_url" class="form-control" placeholder="https://example.com/track?id={tracking}">
                            <small class="text-muted">استخدم {tracking} لرقم التتبع</small>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" id="is_active_add" checked>
                                <label class="form-check-label" for="is_active_add">فعال</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل شركة الشحن</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <!-- Same fields as add modal -->
                <div class="modal-body" id="editFormContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCompany(company) {
    document.getElementById('edit_id').value = company.id;
    const content = `
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">الاسم (عربي) *</label>
                <input type="text" name="name_ar" class="form-control" value="${company.name_ar}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">الاسم (إنجليزي) *</label>
                <input type="text" name="name" class="form-control" value="${company.name}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">مسؤول الاتصال</label>
                <input type="text" name="contact_person" class="form-control" value="${company.contact_person || ''}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">رقم الهاتف</label>
                <input type="tel" name="phone" class="form-control" value="${company.phone || ''}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-control" value="${company.email || ''}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">الموقع الإلكتروني</label>
                <input type="url" name="website" class="form-control" value="${company.website || ''}">
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">العنوان</label>
                <textarea name="address" class="form-control" rows="2">${company.address || ''}</textarea>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">رابط التتبع</label>
                <input type="url" name="tracking_url" class="form-control" value="${company.tracking_url || ''}">
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">ملاحظات</label>
                <textarea name="notes" class="form-control" rows="3">${company.notes || ''}</textarea>
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active_edit" ${company.is_active ? 'checked' : ''}>
                    <label class="form-check-label" for="is_active_edit">فعال</label>
                </div>
            </div>
        </div>
    `;
    document.getElementById('editFormContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function deleteCompany(id) {
    if (confirm('هل أنت متأكد من حذف هذه الشركة؟')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="${id}">`;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>