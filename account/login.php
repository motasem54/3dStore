<?php
require_once '../includes/store-init.php';

if (isLoggedIn()) redirect('/account/dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $user = $db->fetchOne("SELECT * FROM customers WHERE email = ? AND status = 'active'", [$email]);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['customer_id'] = $user['id'];
        $_SESSION['customer_name'] = $user['first_name'] . ' ' . $user['last_name'];
        redirect('/account/dashboard.php');
    } else {
        $error = 'بريد إلكتروني أو كلمة مرور غير صحيحة';
    }
}

$page_title = 'تسجيل الدخول';
include '../includes/header.php';
?>

<section style="padding:80px 0;background:linear-gradient(135deg,#667eea,#764ba2);min-height:calc(100vh - 200px);display:flex;align-items:center">
<div class="container">
<div style="max-width:450px;margin:0 auto;background:white;border-radius:24px;padding:50px;box-shadow:0 20px 60px rgba(0,0,0,0.3)">
    <div style="text-align:center;margin-bottom:40px">
        <div style="width:80px;height:80px;background:linear-gradient(135deg,var(--primary),var(--secondary));border-radius:20px;margin:0 auto 20px;display:flex;align-items:center;justify-content:center"><i class="bi bi-person-circle" style="font-size:40px;color:white"></i></div>
        <h1 style="font-size:28px;margin-bottom:8px">مرحباً بعودتك!</h1>
        <p style="color:var(--text-light)">سجل دخولك للمتابعة</p>
    </div>
    
    <?php if ($error): ?>
    <div style="padding:14px;background:rgba(239,68,68,0.1);border:1px solid var(--danger);border-radius:10px;margin-bottom:20px;color:var(--danger);text-align:center"><i class="bi bi-exclamation-circle"></i> <?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div style="margin-bottom:20px">
            <label style="display:block;margin-bottom:8px;font-weight:600"><i class="bi bi-envelope"></i> البريد الإلكتروني</label>
            <input type="email" name="email" required style="width:100%;padding:14px;border:2px solid var(--border);border-radius:10px;font-size:16px;transition:0.3s" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'" placeholder="email@example.com">
        </div>
        <div style="margin-bottom:20px">
            <label style="display:block;margin-bottom:8px;font-weight:600"><i class="bi bi-lock"></i> كلمة المرور</label>
            <input type="password" name="password" required style="width:100%;padding:14px;border:2px solid var(--border);border-radius:10px;font-size:16px;transition:0.3s" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'" placeholder="••••••••">
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer"><input type="checkbox" name="remember"> <span style="font-size:14px">تذكرني</span></label>
            <a href="/account/forgot-password.php" style="font-size:14px;color:var(--primary);text-decoration:none">نسيت كلمة المرور؟</a>
        </div>
        <button type="submit" style="width:100%;padding:16px;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;border:none;border-radius:12px;font-size:16px;font-weight:600;cursor:pointer;transition:0.3s;margin-bottom:20px" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="bi bi-box-arrow-in-right"></i> تسجيل الدخول
        </button>
        <div style="text-align:center;padding-top:20px;border-top:1px solid var(--border)">
            <p style="color:var(--text-light);margin-bottom:12px">ليس لديك حساب؟</p>
            <a href="/account/register.php" style="color:var(--primary);text-decoration:none;font-weight:600"><i class="bi bi-person-plus"></i> إنشاء حساب جديد</a>
        </div>
    </form>
</div>
</div>
</section>

<?php include '../includes/footer.php'; ?>