<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminRole();

$page_title = 'الإعدادات العامة';
$active_page = 'settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
        $error = 'خطأ في التحقق';
    } else {
        $settings = [
            'site_name_ar' => sanitizeInput($_POST['site_name_ar'] ?? ''),
            'site_name_en' => sanitizeInput($_POST['site_name_en'] ?? ''),
            'store_phone' => sanitizeInput($_POST['store_phone'] ?? ''),
            'store_address' => sanitizeInput($_POST['store_address'] ?? ''),
            'default_language' => $_POST['default_language'] ?? 'ar',
            'default_currency' => $_POST['default_currency'] ?? 'ILS',
            'currency_rate_usd_to_ils' => (float)($_POST['currency_rate_usd_to_ils'] ?? 3.60),
            'tax_rate' => (float)($_POST['tax_rate'] ?? 17),
        ];
        
        try {
            foreach ($settings as $key => $value) {
                $db->query(
                    "UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?",
                    [$value, $key]
                );
            }
            $success = 'تم حفظ الإعدادات بنجاح';
        } catch (Exception $e) {
            $error = 'حدث خطأ أثناء الحفظ';
        }
    }
}

$current_settings = [];
$settings_rows = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
foreach ($settings_rows as $row) {
    $current_settings[$row['setting_key']] = $row['setting_value'];
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

<div class="settings-grid">
    <!-- Quick Links -->
    <div class="glass-card">
        <div class="card-header">
            <h4><i class="bi bi-grid"></i> إعدادات سريعة</h4>
        </div>
        <div class="card-body">
            <div class="quick-links">
                <a href="smtp.php" class="quick-link">
                    <i class="bi bi-envelope"></i>
                    <span>إعدادات البريد الإلكتروني</span>
                </a>
                <a href="appearance.php" class="quick-link">
                    <i class="bi bi-palette"></i>
                    <span>المظهر والألوان</span>
                </a>
                <a href="whatsapp.php" class="quick-link">
                    <i class="bi bi-whatsapp"></i>
                    <span>WhatsApp API</span>
                </a>
                <a href="chatbot.php" class="quick-link">
                    <i class="bi bi-robot"></i>
                    <span>ChatBot Settings</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- General Settings -->
    <div class="glass-card">
        <div class="card-header">
            <h4><i class="bi bi-gear"></i> الإعدادات العامة</h4>
        </div>
        <div class="card-body">
            <form method="POST" class="settings-form">
                <input type="hidden" name="csrf" value="<?php echo escape($_SESSION['csrf']); ?>">
                
                <div class="form-section">
                    <h5>معلومات المتجر</h5>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>اسم المتجر (عربي)</label>
                            <input type="text" name="site_name_ar" class="form-control" 
                                   value="<?php echo escape($current_settings['site_name_ar'] ?? '3D Store'); ?>">
                        </div>
                        <div class="form-group">
                            <label>اسم المتجر (English)</label>
                            <input type="text" name="site_name_en" class="form-control" 
                                   value="<?php echo escape($current_settings['site_name_en'] ?? '3D Store'); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>رقم الهاتف</label>
                            <input type="text" name="store_phone" class="form-control" 
                                   value="<?php echo escape($current_settings['store_phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>العنوان</label>
                            <input type="text" name="store_address" class="form-control" 
                                   value="<?php echo escape($current_settings['store_address'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h5>اللغة والعملة</h5>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>اللغة الافتراضية</label>
                            <select name="default_language" class="form-control">
                                <option value="ar" <?php echo ($current_settings['default_language'] ?? 'ar') === 'ar' ? 'selected' : ''; ?>>العربية</option>
                                <option value="en" <?php echo ($current_settings['default_language'] ?? 'ar') === 'en' ? 'selected' : ''; ?>>English</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>العملة الافتراضية</label>
                            <select name="default_currency" class="form-control">
                                <option value="ILS" <?php echo ($current_settings['default_currency'] ?? 'ILS') === 'ILS' ? 'selected' : ''; ?>>شيكل (₪)</option>
                                <option value="USD" <?php echo ($current_settings['default_currency'] ?? 'ILS') === 'USD' ? 'selected' : ''; ?>>دولار ($)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>سعر التحويل (دولار إلى شيكل)</label>
                            <input type="number" step="0.01" name="currency_rate_usd_to_ils" class="form-control" 
                                   value="<?php echo escape($current_settings['currency_rate_usd_to_ils'] ?? '3.60'); ?>">
                        </div>
                        <div class="form-group">
                            <label>نسبة الضريبة (%)</label>
                            <input type="number" step="0.01" name="tax_rate" class="form-control" 
                                   value="<?php echo escape($current_settings['tax_rate'] ?? '17'); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check-circle"></i> حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.settings-grid { display: grid; grid-template-columns: 300px 1fr; gap: 20px; }
.quick-links { display: flex; flex-direction: column; gap: 10px; }
.quick-link { display: flex; align-items: center; gap: 12px; padding: 14px 16px; 
              background: rgba(255,255,255,0.05); border-radius: 10px; 
              text-decoration: none; transition: all 0.3s; }
.quick-link:hover { background: rgba(255,255,255,0.1); transform: translateX(-4px); }
.quick-link i { font-size: 20px; }
.settings-form { max-width: 800px; }
.form-section { background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; margin-bottom: 20px; }
.form-section h5 { margin: 0 0 16px 0; color: var(--primary); font-size: 16px; }
.form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 14px; color: var(--text-secondary); }
.form-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; }
@media (max-width: 968px) { .settings-grid { grid-template-columns: 1fr; } }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>