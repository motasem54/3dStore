<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminRole();

$page_title = 'إعدادات البريد الإلكتروني (SMTP)';
$active_page = 'settings';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
        $error = 'خطأ في التحقق';
    } else {
        if (isset($_POST['test_email'])) {
            // Test email
            $test_email = sanitizeInput($_POST['test_email_address'] ?? '');
            if (!empty($test_email) && validateEmail($test_email)) {
                require_once __DIR__ . '/../../includes/email.php';
                $result = sendEmailAdvanced(
                    $test_email,
                    'اختبار SMTP - 3D Store',
                    '<h2>نجح الاختبار!</h2><p>إذا وصلتك هذه الرسالة، فإن إعدادات SMTP تعمل بشكل صحيح.</p>',
                    true
                );
                
                if ($result) {
                    $success = 'تم إرسال بريد الاختبار بنجاح!';
                } else {
                    $error = 'فشل إرسال البريد. يرجى التحقق من الإعدادات.';
                }
            } else {
                $error = 'البريد الإلكتروني غير صالح';
            }
        } else {
            // Save settings
            $smtp_settings = [
                'smtp_host' => sanitizeInput($_POST['smtp_host'] ?? ''),
                'smtp_port' => (int)($_POST['smtp_port'] ?? 587),
                'smtp_username' => sanitizeInput($_POST['smtp_username'] ?? ''),
                'smtp_password' => $_POST['smtp_password'] ?? '',
                'smtp_from_email' => sanitizeInput($_POST['smtp_from_email'] ?? ''),
                'smtp_from_name' => sanitizeInput($_POST['smtp_from_name'] ?? ''),
                'smtp_encryption' => $_POST['smtp_encryption'] ?? 'tls',
            ];
            
            try {
                foreach ($smtp_settings as $key => $value) {
                    $existing = $db->fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);
                    if ($existing) {
                        $db->query(
                            "UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?",
                            [$value, $key]
                        );
                    } else {
                        $db->query(
                            "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)",
                            [$key, $value]
                        );
                    }
                }
                $success = 'تم حفظ إعدادات SMTP بنجاح';
            } catch (Exception $e) {
                $error = 'حدث خطأ أثناء الحفظ';
            }
        }
    }
}

$smtp = [];
$smtp_keys = ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_from_email', 'smtp_from_name', 'smtp_encryption'];
foreach ($smtp_keys as $key) {
    $result = $db->fetch("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
    $smtp[$key] = $result['setting_value'] ?? '';
}

include __DIR__ . '/../includes/header.php';
?>

<?php if (isset($success)): ?>
<div class="alert alert-success">
    <i class="bi bi-check-circle"></i> <?php echo $success; ?>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger">
    <i class="bi bi-exclamation-circle"></i> <?php echo $error; ?>
</div>
<?php endif; ?>

<div class="glass-card">
    <div class="card-header space-between">
        <h4><i class="bi bi-envelope"></i> إعدادات SMTP</h4>
        <a href="index.php" class="btn-sm"><i class="bi bi-arrow-right"></i> رجوع</a>
    </div>
    
    <div class="card-body">
        <form method="POST" class="smtp-form">
            <input type="hidden" name="csrf" value="<?php echo escape($_SESSION['csrf']); ?>">
            
            <div class="form-section">
                <h5>معلومات خادم SMTP</h5>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>خادم SMTP (Host) <span class="required">*</span></label>
                        <input type="text" name="smtp_host" class="form-control" 
                               value="<?php echo escape($smtp['smtp_host']); ?>" 
                               placeholder="smtp.gmail.com">
                        <small class="text-muted">مثال: smtp.gmail.com أو mail.yourdomain.com</small>
                    </div>
                    <div class="form-group">
                        <label>المنفذ (Port) <span class="required">*</span></label>
                        <input type="number" name="smtp_port" class="form-control" 
                               value="<?php echo escape($smtp['smtp_port'] ?: '587'); ?>">
                        <small class="text-muted">587 (TLS) أو 465 (SSL)</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>اسم المستخدم (Email) <span class="required">*</span></label>
                        <input type="email" name="smtp_username" class="form-control" 
                               value="<?php echo escape($smtp['smtp_username']); ?>" 
                               placeholder="your-email@gmail.com">
                    </div>
                    <div class="form-group">
                        <label>كلمة المرور <span class="required">*</span></label>
                        <input type="password" name="smtp_password" class="form-control" 
                               value="<?php echo escape($smtp['smtp_password']); ?>" 
                               placeholder="••••••••">
                        <small class="text-muted">لـ Gmail: استخدم App Password</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>التشفير</label>
                    <select name="smtp_encryption" class="form-control">
                        <option value="tls" <?php echo ($smtp['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS (موصى به)</option>
                        <option value="ssl" <?php echo ($smtp['smtp_encryption'] ?? 'tls') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                        <option value="none" <?php echo ($smtp['smtp_encryption'] ?? 'tls') === 'none' ? 'selected' : ''; ?>>بدون تشفير</option>
                    </select>
                </div>
            </div>
            
            <div class="form-section">
                <h5>معلومات المرسل</h5>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>البريد الإلكتروني للمرسل <span class="required">*</span></label>
                        <input type="email" name="smtp_from_email" class="form-control" 
                               value="<?php echo escape($smtp['smtp_from_email']); ?>" 
                               placeholder="noreply@yourdomain.com">
                        <small class="text-muted">البريد الذي يظهر للعملاء</small>
                    </div>
                    <div class="form-group">
                        <label>اسم المرسل</label>
                        <input type="text" name="smtp_from_name" class="form-control" 
                               value="<?php echo escape($smtp['smtp_from_name'] ?: '3D Store'); ?>" 
                               placeholder="3D Store">
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="bi bi-check-circle"></i> حفظ الإعدادات
                </button>
            </div>
        </form>
        
        <!-- Test Email Section -->
        <div class="form-section" style="margin-top: 30px; border-top: 2px solid var(--glass-border); padding-top: 30px;">
            <h5><i class="bi bi-send"></i> اختبار إرسال البريد</h5>
            <p class="text-muted" style="margin-bottom: 16px;">أرسل بريد اختبار للتأكد من صحة الإعدادات</p>
            
            <form method="POST" style="display: flex; gap: 12px; align-items: flex-end;">
                <input type="hidden" name="csrf" value="<?php echo escape($_SESSION['csrf']); ?>">
                <div class="form-group" style="flex: 1;">
                    <label>البريد الإلكتروني</label>
                    <input type="email" name="test_email_address" class="form-control" 
                           placeholder="test@example.com" required>
                </div>
                <button type="submit" name="test_email" class="btn-primary">
                    <i class="bi bi-send"></i> إرسال اختبار
                </button>
            </form>
        </div>
        
        <!-- Help Section -->
        <div class="help-box">
            <h6><i class="bi bi-info-circle"></i> ملاحظات مهمة</h6>
            <ul>
                <li><strong>Gmail:</strong> يجب تفعيل "التحقق بخطوتين" وإنشاء "App Password" من حسابك</li>
                <li><strong>Outlook/Hotmail:</strong> استخدم smtp-mail.outlook.com والمنفذ 587</li>
                <li><strong>cPanel Email:</strong> استخدم mail.yourdomain.com والمنفذ 587 أو 465</li>
                <li>تأكد من السماح لـ PHP بإرسال البريد من إعدادات الاستضافة</li>
            </ul>
        </div>
    </div>
</div>

<style>
.smtp-form { max-width: 800px; }
.form-section { background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; margin-bottom: 20px; }
.form-section h5 { margin: 0 0 16px 0; color: var(--primary); font-size: 16px; }
.form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 14px; color: var(--text-secondary); }
.form-group small { font-size: 12px; }
.required { color: var(--danger); }
.form-actions { display: flex; gap: 12px; justify-content: flex-end; }
.help-box { margin-top: 30px; padding: 20px; background: rgba(59, 130, 246, 0.1); 
            border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 12px; }
.help-box h6 { margin: 0 0 12px 0; color: var(--info); }
.help-box ul { margin: 0; padding-right: 20px; line-height: 2; }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>