<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminRole();

$page_title = 'إضافة مستخدم جديد';
$active_page = 'users';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
        $error = 'خطأ في التحقق';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = sanitizeInput($_POST['full_name'] ?? '');
        $role = $_POST['role'] ?? 'customer';
        $status = $_POST['status'] ?? 'active';
        
        if (empty($username) || empty($email) || empty($password)) {
            $error = 'يرجى ملء جميع الحقول المطلوبة';
        } elseif (!validateEmail($email)) {
            $error = 'البريد الإلكتروني غير صالح';
        } else {
            $existing = $db->fetch("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
            if ($existing) {
                $error = 'اسم المستخدم أو البريد الإلكتروني موجود مسبقاً';
            } else {
                try {
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $db->insert(
                        "INSERT INTO users (username, email, password, full_name, role, status, created_at) 
                         VALUES (?, ?, ?, ?, ?, ?, NOW())",
                        [$username, $email, $hashed, $full_name, $role, $status]
                    );
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
        <h4><i class="bi bi-person-plus"></i> إضافة مستخدم جديد</h4>
        <a href="index.php" class="btn-sm"><i class="bi bi-arrow-right"></i> رجوع</a>
    </div>
    
    <div class="card-body">
        <form method="POST" class="user-form">
            <input type="hidden" name="csrf" value="<?php echo escape($_SESSION['csrf']); ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label>اسم المستخدم <span class="required">*</span></label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>البريد الإلكتروني <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>الاسم الكامل</label>
                    <input type="text" name="full_name" class="form-control">
                </div>
                <div class="form-group">
                    <label>كلمة المرور <span class="required">*</span></label>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>الدور</label>
                    <select name="role" class="form-control">
                        <option value="customer">عميل</option>
                        <option value="sales">مبيعات</option>
                        <option value="admin">مدير</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>الحالة</label>
                    <select name="status" class="form-control">
                        <option value="active">نشط</option>
                        <option value="inactive">معطل</option>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary"><i class="bi bi-check-circle"></i> حفظ المستخدم</button>
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