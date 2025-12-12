<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';

if (isAdmin() || isset($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
        $error = 'خطأ في التحقق من الجلسة';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (!empty($username) && !empty($password)) {
            $stmt = $db->query(
                "SELECT * FROM users WHERE username = ? AND role IN ('admin', 'sales') AND status = 'active'",
                [$username]
            );
            $user = $stmt->fetch();
            
            if ($user && verifyPassword($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_user_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_role'] = $user['role'];
                $_SESSION['admin_name'] = $user['full_name'] ?? $user['username'];
                
                header('Location: index.php');
                exit;
            } else {
                $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
            }
        } else {
            $error = 'الرجاء إدخال جميع البيانات';
        }
    }
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - لوحة التحكم</title>
    <link rel="stylesheet" href="assets/css/admin-glass.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="glass-card login-card">
            <div class="login-header">
                <div class="logo-circle">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h1>3D Store</h1>
                <p>لوحة التحكم الإدارية</p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle"></i>
                <?php echo escape($error); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form">
                <input type="hidden" name="csrf" value="<?php echo escape($_SESSION['csrf']); ?>">
                
                <div class="form-group">
                    <label><i class="bi bi-person"></i> اسم المستخدم</label>
                    <input type="text" name="username" class="form-control" required autofocus>
                </div>
                
                <div class="form-group">
                    <label><i class="bi bi-lock"></i> كلمة المرور</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn-primary btn-block">
                    <i class="bi bi-box-arrow-in-right"></i>
                    تسجيل الدخول
                </button>
            </form>
        </div>
        
        <p class="copyright">© 2025 3D Store. جميع الحقوق محفوظة</p>
    </div>
</body>
</html>