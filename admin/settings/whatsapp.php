<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminRole();

$page_title = 'إعدادات WhatsApp API';
$active_page = 'settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
        $error = 'خطأ في التحقق';
    } else {
        $whatsapp_settings = [
            'whatsapp_enabled' => isset($_POST['whatsapp_enabled']) ? '1' : '0',
            'whatsapp_api_url' => sanitizeInput($_POST['whatsapp_api_url'] ?? ''),
            'whatsapp_api_token' => $_POST['whatsapp_api_token'] ?? '',
            'whatsapp_phone_number' => sanitizeInput($_POST['whatsapp_phone_number'] ?? ''),
        ];
        
        try {
            foreach ($whatsapp_settings as $key => $value) {
                $existing = $db->fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);
                if ($existing) {
                    $db->query("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?", [$value, $key]);
                } else {
                    $db->query("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)", [$key, $value]);
                }
            }
            $success = 'تم حفظ إعدادات WhatsApp بنجاح';
        } catch (Exception $e) {
            $error = 'حدث خطأ أثناء الحفظ';
        }
    }
}

$whatsapp = [];
$keys = ['whatsapp_enabled', 'whatsapp_api_url', 'whatsapp_api_token', 'whatsapp_phone_number'];
foreach ($keys as $key) {
    $result = $db->fetch("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
    $whatsapp[$key] = $result['setting_value'] ?? '';
}

include __DIR__ . '/../includes/header.php';
?>

<?php if (isset($success)): ?>
<div class="alert alert-success"><i class="bi bi-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>
<?php if (isset($error)): ?>
<div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="glass-card">
    <div class="card-header space-between">
        <h4><i class="bi bi-whatsapp"></i> إعدادات WhatsApp API</h4>
        <a href="index.php" class="btn-sm"><i class="bi bi-arrow-right"></i> رجوع</a>
    </div>
    
    <div class="card-body">
        <form method="POST" class="settings-form">
            <input type="hidden" name="csrf" value="<?php echo escape($_SESSION['csrf']); ?>">
            
            <div class="form-section">
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="whatsapp_enabled" <?php echo $whatsapp['whatsapp_enabled'] === '1' ? 'checked' : ''; ?>>
                        <span>تفعيل إرسال إشعارات WhatsApp</span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label>رقم WhatsApp Business <span class="required">*</span></label>
                    <input type="text" name="whatsapp_phone_number" class="form-control" 
                           value="<?php echo escape($whatsapp['whatsapp_phone_number']); ?>" 
                           placeholder="+972501234567">
                    <small class="text-muted">رقم الهاتف بصيغة دولية</small>
                </div>
                
                <div class="form-group">
                    <label>API URL <span class="required">*</span></label>
                    <input type="url" name="whatsapp_api_url" class="form-control" 
                           value="<?php echo escape($whatsapp['whatsapp_api_url']); ?>" 
                           placeholder="https://api.whatsapp.com/send">
                    <small class="text-muted">رابط API الخاص بالخدمة (مثل: Twilio, GreenAPI, WATI)</small>
                </div>
                
                <div class="form-group">
                    <label>API Token <span class="required">*</span></label>
                    <input type="text" name="whatsapp_api_token" class="form-control" 
                           value="<?php echo escape($whatsapp['whatsapp_api_token']); ?>" 
                           placeholder="your-api-token-here">
                    <small class="text-muted">مفتاح API من لوحة تحكم الخدمة</small>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="bi bi-check-circle"></i> حفظ الإعدادات
                </button>
            </div>
        </form>
        
        <div class="help-box">
            <h6><i class="bi bi-info-circle"></i> خدمات WhatsApp API الموصى بها</h6>
            <ul>
                <li><strong>Twilio:</strong> خدمة احترافية، تدعم العربية، سعر مناسب</li>
                <li><strong>GreenAPI:</strong> خدمة روسية رخيصة، واجهة بسيطة</li>
                <li><strong>WATI:</strong> مخصصة للتجارة الإلكترونية، سهلة الربط</li>
                <li><strong>WhatsApp Business API:</strong> الخدمة الرسمية من Meta (تتطلب موافقة)</li>
            </ul>
        </div>
    </div>
</div>

<style>
.settings-form { max-width: 800px; }
.form-section { background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; margin-bottom: 20px; }
.form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.form-group label { font-size: 14px; color: var(--text-secondary); }
.form-group small { font-size: 12px; }
.required { color: var(--danger); }
.checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
.checkbox-label input { width: 18px; height: 18px; cursor: pointer; }
.form-actions { display: flex; gap: 12px; justify-content: flex-end; }
.help-box { margin-top: 30px; padding: 20px; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 12px; }
.help-box h6 { margin: 0 0 12px 0; color: var(--info); }
.help-box ul { margin: 0; padding-right: 20px; line-height: 2; }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>