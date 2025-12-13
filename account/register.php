<?php
require_once '../includes/store-init.php';

if (isLoggedIn()) redirect('/account/dashboard.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if ($password !== $confirm) {
        $error = 'كلمات المرور غير متطابقة';
    } elseif (strlen($password) < 6) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    } else {
        $exists = $db->fetchOne("SELECT id FROM customers WHERE email = ?", [$email]);
        if ($exists) {
            $error = 'البريد الإلكتروني مستخدم مسبقاً';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $db->execute("INSERT INTO customers (first_name, last_name, email, phone, password, created_at) VALUES (?, ?, ?, ?, ?, NOW())", [$first_name, $last_name, $email, $phone, $hashed]);
            $success = 'تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول';
        }
    }
}

$page_title = 'إنشاء حساب';
include '../includes/header.php';
?>

<section style="padding:80px 0;background:linear-gradient(135deg,#f093fb,#f5576c);min-height:calc(100vh - 200px);display:flex;align-items:center">
<div class="container">
<div style="max-width:550px;margin:0 auto;background:white;border-radius:24px;padding:50px;box-shadow:0 20px 60px rgba(0,0,0,0.3)">
    <div style="text-align:center;margin-bottom:40px">
        <div style="width:80px;height:80px;background:linear-gradient(135deg,var(--primary),var(--secondary));border-radius:20px;margin:0 auto 20px;display:flex;align-items:center;justify-content:center"><i class="bi bi-person-plus" style="font-size:40px;color:white"></i></div>
        <h1 style="font-size:28px;margin-bottom:8px">إنشاء حساب جديد</h1>
        <p style="color:var(--text-light)">انضم إلينا وابدأ التسوق الآن!</p>
    </div>
    
    <?php if ($error): ?>
    <div style="padding:14px;background:rgba(239,68,68,0.1);border:1px solid var(--danger);border-radius:10px;margin-bottom:20px;color:var(--danger);text-align:center"><i class="bi bi-exclamation-circle"></i> <?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div style="padding:14px;background:rgba(16,185,129,0.1);border:1px solid var(--success);border-radius:10px;margin-bottom:20px;color:var(--success);text-align:center"><i class="bi bi-check-circle"></i> <?php echo $success; ?> <a href="/account/login.php" style="color:var(--success);text-decoration:underline">سجل الدخول هنا</a></div>
    <?php endif; ?>
    
    <form method="POST">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
            <div>
                <label style="display:block;margin-bottom:8px;font-weight:600">الاسم الأول</label>
                <input type="text" name="first_name" required style="width:100%;padding:14px;border:2px solid var(--border);border-radius:10px;font-size:16px;transition:0.3s" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
            </div>
            <div>
                <label style="display:block;margin-bottom:8px;font-weight:600">الاسم الأخير</label>
                <input type="text" name="last_name" required style="width:100%;padding:14px;border:2px solid var(--border);border-radius:10px;font-size:16px;transition:0.3s" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
            </div>
        </div>
        <div style="margin-bottom:20px">
            <label style="display:block;margin-bottom:8px;font-weight:600"><i class="bi bi-envelope"></i> البريد الإلكتروني</label>
            <input type="email" name="email" required style="width:100%;padding:14px;border:2px solid var(--border);border-radius:10px;font-size:16px;transition:0.3s" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'" placeholder="email@example.com">
        </div>
        <div style="margin-bottom:20px">
            <label style="display:block;margin-bottom:8px;font-weight:600"><i class="bi bi-telephone"></i> رقم الجوال</label>
            <input type="tel" name="phone" required style="width:100%;padding:14px;border:2px solid var(--border);border-radius:10px;font-size:16px;transition:0.3s" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'" placeholder="+970 599 123 456">
        </div>
        <div style="margin-bottom:20px">
            <label style="display:block;margin-bottom:8px;font-weight:600"><i class="bi bi-lock"></i> كلمة المرور</label>
            <input type="password" name="password" required style="width:100%;padding:14px;border:2px solid var(--border);border-radius:10px;font-size:16px;transition:0.3s" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'" placeholder="••••••••">
        </div>
        <div style="margin-bottom:24px">
            <label style="display:block;margin-bottom:8px;font-weight:600"><i class="bi bi-lock-fill"></i> تأكيد كلمة المرور</label>
            <input type="password" name="confirm_password" required style="width:100%;padding:14px;border:2px solid var(--border);border-radius:10px;font-size:16px;transition:0.3s" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'" placeholder="••••••••">
        </div>
        <button type="submit" style="width:100%;padding:16px;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;border:none;border-radius:12px;font-size:16px;font-weight:600;cursor:pointer;transition:0.3s;margin-bottom:20px" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="bi bi-person-check"></i> إنشاء الحساب
        </button>
        <div style="text-align:center;padding-top:20px;border-top:1px solid var(--border)">
            <p style="color:var(--text-light);margin-bottom:12px">لديك حساب بالفعل؟</p>
            <a href="/account/login.php" style="color:var(--primary);text-decoration:none;font-weight:600"><i class="bi bi-box-arrow-in-right"></i> تسجيل الدخول</a>
        </div>
    </form>
</div>
</div>
</section>

<?php include '../includes/footer.php'; ?>