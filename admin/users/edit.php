<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminRole();

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0) {
    header('Location: index.php');
    exit;
}

$user = $db->fetch("SELECT * FROM users WHERE id = ?", [$user_id]);
if (!$user) {
    header('Location: index.php');
    exit;
}

$page_title = 'تعديل المستخدم: ' . $user['username'];
$active_page = 'users';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
        $error = 'خطأ في التحقق';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $full_name = sanitizeInput($_POST['full_name'] ?? '');
        $role = $_POST['role'] ?? 'customer';
        $status = $_POST['status'] ?? 'active';
        $new_password = $_POST['new_password'] ?? '';
        
        if (empty($username) || empty($email)) {
            $error = 'يرجى ملء جميع الحقول المطلوبة';
        } elseif (!validateEmail($email)) {
            $error = 'البريد الإلكتروني غير صالح';
        } else {
            $existing = $db->fetch(
                "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?",
                [$username, $email, $user_id]
            );
            
            if ($existing) {
                $error = 'اسم المستخدم أو البريد الإلكتروني موجود مسبقاً';
            } else {
                try {
                    if (!empty($new_password)) {
                        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
                        $db->update(
                            "UPDATE users SET username = ?, email = ?, password = ?, full_name = ?, role = ?, status = ?, updated_at = NOW() WHERE id = ?",
                            [$username, $email, $hashed, $full_name, $role, $status, $user_id]
                        );
                    } else {
                        $db->update(
                            "UPDATE users SET username = ?, email = ?, full_name = ?, role = ?, status = ?, updated_at = NOW() WHERE id = ?",
                            [$username, $email, $full_name, $role, $status, $user_id]
                        );
                    }
                    
                    $_SESSION['success'] = 'تم تحديث المستخدم بنجاح';
                    header('Location: index.php');
                    exit;
                } catch (Exception $e) {
                    $error = 'حدث خطأ أثناء الحفظ';
                }
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<?php if (isset($error)): ?>
<div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="glass-card">
    <div class="card-header space-between">
        <h4><i class="bi bi-pencil"></i> تعديل المستخدم</h4>
        <a href="index.php" class="btn-sm"><i class="bi bi-arrow-right"></i> رجوع</a>
    </div>
    
    <div class="card-body">
        <form method="POST" class="user-form">
            <input type="hidden" name="csrf" value="<?php echo escape($_SESSION['csrf']); ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label>اسم المستخدم <span class="required">*</span></label>
                    <input type="text" name="username" class="form-control" value="<?php echo escape($user['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label>البريد الإلكتروني <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control" value="<?php echo escape($user['email']); ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>الاسم الكامل</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo escape($user['full_name']); ?>">
                </div>
                <div class="form-group">
                    <label>كلمة المرور الجديدة</label>
                    <input type="password" name="new_password" class="form-control" placeholder="اتركه فارغاً إذا كنت لا تريد تغييره">
                    <small class="text-muted">اتركه فارغاً للاحتفاظ بكلمة المرور الحالية</small>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>الدور</label>
                    <select name="role" class="form-control" <?php echo $user['id'] == $_SESSION['admin_user_id'] ? 'disabled' : ''; ?>>
                        <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : ''; ?>>عميل</option>
                        <option value="sales" <?php echo $user['role'] === 'sales' ? 'selected' : ''; ?>>مبيعات</option>
                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>مدير</option>
                    </select>
                    <?php if ($user['id'] == $_SESSION['admin_user_id']): ?>
                    <small class="text-muted">لا يمكنك تغيير دورك الخاص</small>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>الحالة</label>
                    <select name="status" class="form-control">
                        <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>نشط</option>
                        <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>معطل</option>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary"><i class="bi bi-check-circle"></i> حفظ التعديلات</button>
                <a href="index.php" class="btn-sm">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<style>
.user-form { max-width: 800px; }
.form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; margin-bottom: 20px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 14px; color: var(--text-secondary); }
.required { color: var(--danger); }
.form-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>