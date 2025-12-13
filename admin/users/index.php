<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminRole();

$page_title = 'إدارة المستخدمين';
$active_page = 'users';

$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? 'all';

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($role_filter !== 'all') {
    $where[] = "role = ?";
    $params[] = $role_filter;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$total = $db->fetch("SELECT COUNT(*) as count FROM users {$whereClause}", $params)['count'] ?? 0;
$totalPages = ceil($total / $perPage);

$users = $db->fetchAll(
    "SELECT * FROM users {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}",
    $params
);

include __DIR__ . '/../includes/header.php';
?>

<div class="glass-card">
    <div class="card-header space-between">
        <h4><i class="bi bi-people"></i> إدارة المستخدمين (<?php echo number_format($total); ?>)</h4>
        <a href="add.php" class="btn-primary"><i class="bi bi-plus-circle"></i> إضافة مستخدم</a>
    </div>
    
    <div class="card-body">
        <form method="GET" class="filters-form">
            <div class="filters-row">
                <div class="form-group">
                    <input type="text" name="search" class="form-control" placeholder="بحث باسم المستخدم، البريد، الاسم الكامل..." value="<?php echo escape($search); ?>">
                </div>
                <div class="form-group">
                    <select name="role" class="form-control">
                        <option value="all">جميع الأدوار</option>
                        <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>مدير</option>
                        <option value="sales" <?php echo $role_filter === 'sales' ? 'selected' : ''; ?>>مبيعات</option>
                        <option value="customer" <?php echo $role_filter === 'customer' ? 'selected' : ''; ?>>عميل</option>
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
                        <th>اسم المستخدم</th>
                        <th>الاسم الكامل</th>
                        <th>البريد الإلكتروني</th>
                        <th>الدور</th>
                        <th>الحالة</th>
                        <th>تاريخ التسجيل</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong><?php echo escape($user['username']); ?></strong></td>
                        <td><?php echo escape($user['full_name']); ?></td>
                        <td><?php echo escape($user['email']); ?></td>
                        <td>
                            <?php if ($user['role'] === 'admin'): ?>
                                <span class="badge badge-danger">مدير</span>
                            <?php elseif ($user['role'] === 'sales'): ?>
                                <span class="badge badge-info">مبيعات</span>
                            <?php else: ?>
                                <span class="badge">عميل</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['status'] === 'active'): ?>
                                <span class="badge badge-success">نشط</span>
                            <?php else: ?>
                                <span class="badge badge-warning">معطل</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted"><?php echo formatDate($user['created_at'], 'd/m/Y'); ?></td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn-icon" title="تعديل">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($user['id'] != $_SESSION['admin_user_id']): ?>
                                <a href="delete.php?id=<?php echo $user['id']; ?>" class="btn-icon" data-confirm="حذف هذا المستخدم؟" title="حذف">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
                            <p>لا يوجد مستخدمين</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&role=<?php echo $role_filter; ?>&search=<?php echo urlencode($search); ?>" class="btn-sm">السابق</a>
            <?php endif; ?>
            <span class="page-info">صفحة <?php echo $page; ?> من <?php echo $totalPages; ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&role=<?php echo $role_filter; ?>&search=<?php echo urlencode($search); ?>" class="btn-sm">التالي</a>
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