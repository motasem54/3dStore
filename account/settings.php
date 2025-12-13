<?php
require_once '../includes/store-init.php';
$page_title = 'إعدادات الحساب';

if (!isLoggedIn()) {
    redirect('/account/login.php');
}

$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    if (empty($name)) $errors[] = 'الاسم مطلوب';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'بريد إلكتروني غير صحيح';
    
    if ($email !== $user['email']) {
        $existing = $db->fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $user['id']]);
        if ($existing) $errors[] = 'البريد الإلكتروني مستخدم بالفعل';
    }
    
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = 'يجب إدخال كلمة المرور الحالية';
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = 'كلمة المرور الحالية غير صحيحة';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'كلمتا المرور غير متطابقتين';
        }
    }
    
    if (empty($errors)) {
        $update_data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
        ];
        
        if (!empty($new_password)) {
            $update_data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        
        $set_clause = implode(', ', array_map(fn($k) => "$k = ?", array_keys($update_data)));
        $values = array_values($update_data);
        $values[] = $user['id'];
        
        $db->execute("UPDATE users SET $set_clause WHERE id = ?", $values);
        
        $_SESSION['success'] = 'تم تحديث بياناتك بنجاح';
        redirect('/account/settings.php');
    }
}

include '../includes/header.php';
?>

<section style="padding:80px 0;background:var(--bg-light);min-height:calc(100vh - 200px)">
<div class="container" style="max-width:1000px">

<div style="display:grid;grid-template-columns:280px 1fr;gap:30px">
    
    <?php include 'sidebar.php'; ?>
    
    <main>
        <div style="background:white;border-radius:16px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
            <h1 style="font-size:28px;font-weight:700;margin-bottom:24px;display:flex;align-items:center;gap:12px">
                <i class="bi bi-gear" style="color:var(--primary)"></i> إعدادات الحساب
            </h1>
            
            <?php if (isset($_SESSION['success'])): ?>
            <div style="padding:16px;background:rgba(16,185,129,0.1);border-right:4px solid var(--success);border-radius:8px;margin-bottom:24px;color:var(--success)">
                <i class="bi bi-check-circle-fill"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
            <div style="padding:16px;background:rgba(239,68,68,0.1);border-right:4px solid var(--danger);border-radius:8px;margin-bottom:24px;color:var(--danger)">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <ul style="margin:8px 0 0 20px">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <form method="POST" style="display:flex;flex-direction:column;gap:24px">
                
                <div>
                    <h3 style="font-size:18px;font-weight:600;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid var(--border)">المعلومات الشخصية</h3>
                    
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                        <div>
                            <label style="display:block;font-weight:600;margin-bottom:8px;font-size:14px">الاسم الكامل</label>
                            <input type="text" name="name" value="<?php echo escape($user['name']); ?>" required style="width:100%;padding:12px 16px;border:1px solid var(--border);border-radius:10px;font-size:14px;transition:0.3s" onfocus="this.style.borderColor='var(--primary)';this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                        </div>
                        
                        <div>
                            <label style="display:block;font-weight:600;margin-bottom:8px;font-size:14px">البريد الإلكتروني</label>
                            <input type="email" name="email" value="<?php echo escape($user['email']); ?>" required style="width:100%;padding:12px 16px;border:1px solid var(--border);border-radius:10px;font-size:14px;transition:0.3s" onfocus="this.style.borderColor='var(--primary)';this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                        </div>
                    </div>
                    
                    <div style="margin-top:20px">
                        <label style="display:block;font-weight:600;margin-bottom:8px;font-size:14px">رقم الجوال</label>
                        <input type="tel" name="phone" value="<?php echo escape($user['phone'] ?? ''); ?>" style="width:100%;padding:12px 16px;border:1px solid var(--border);border-radius:10px;font-size:14px;transition:0.3s" onfocus="this.style.borderColor='var(--primary)';this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                    </div>
                </div>
                
                <div>
                    <h3 style="font-size:18px;font-weight:600;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid var(--border)">تغيير كلمة المرور</h3>
                    
                    <div style="display:grid;gap:20px">
                        <div>
                            <label style="display:block;font-weight:600;margin-bottom:8px;font-size:14px">كلمة المرور الحالية</label>
                            <input type="password" name="current_password" style="width:100%;padding:12px 16px;border:1px solid var(--border);border-radius:10px;font-size:14px;transition:0.3s" onfocus="this.style.borderColor='var(--primary)';this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                            <small style="color:var(--text-light);font-size:12px;display:block;margin-top:4px">اتركه فارغاً إذا لم ترغب بتغيير كلمة المرور</small>
                        </div>
                        
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                            <div>
                                <label style="display:block;font-weight:600;margin-bottom:8px;font-size:14px">كلمة المرور الجديدة</label>
                                <input type="password" name="new_password" style="width:100%;padding:12px 16px;border:1px solid var(--border);border-radius:10px;font-size:14px;transition:0.3s" onfocus="this.style.borderColor='var(--primary)';this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                            </div>
                            
                            <div>
                                <label style="display:block;font-weight:600;margin-bottom:8px;font-size:14px">تأكيد كلمة المرور</label>
                                <input type="password" name="confirm_password" style="width:100%;padding:12px 16px;border:1px solid var(--border);border-radius:10px;font-size:14px;transition:0.3s" onfocus="this.style.borderColor='var(--primary)';this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" style="padding:14px;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;border:none;border-radius:10px;font-weight:600;font-size:16px;cursor:pointer;transition:0.3s" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(59,130,246,0.4)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">
                    <i class="bi bi-check-circle"></i> حفظ التغييرات
                </button>
            </form>
        </div>
    </main>
    
</div>

</div>
</section>

<style>
@media (max-width: 768px) {
    .container > div { grid-template-columns: 1fr !important; }
}
</style>

<?php include '../includes/footer.php'; ?>