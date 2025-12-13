<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminRole();

$page_title = 'إعدادات المظهر والألوان';
$active_page = 'settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
        $error = 'خطأ في التحقق';
    } else {
        $settings = [
            'primary_color' => sanitizeInput($_POST['primary_color'] ?? '#7c5cff'),
            'secondary_color' => sanitizeInput($_POST['secondary_color'] ?? '#764ba2'),
            'success_color' => sanitizeInput($_POST['success_color'] ?? '#10b981'),
            'danger_color' => sanitizeInput($_POST['danger_color'] ?? '#ef4444'),
        ];
        
        if (!empty($_FILES['logo']['name'])) {
            $result = uploadFile($_FILES['logo'], 'uploads/settings/', ['jpg', 'jpeg', 'png', 'webp', 'svg']);
            if ($result['success']) {
                $settings['site_logo'] = 'uploads/settings/' . $result['filename'];
            }
        }
        
        try {
            foreach ($settings as $key => $value) {
                $existing = $db->fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);
                if ($existing) {
                    $db->query("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?", [$value, $key]);
                } else {
                    $db->query("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)", [$key, $value]);
                }
            }
            $success = 'تم حفظ إعدادات المظهر بنجاح';
        } catch (Exception $e) {
            $error = 'حدث خطأ أثناء الحفظ';
        }
    }
}

$appearance = [];
$keys = ['primary_color', 'secondary_color', 'success_color', 'danger_color', 'site_logo'];
foreach ($keys as $key) {
    $result = $db->fetch("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
    $appearance[$key] = $result['setting_value'] ?? '';
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
        <h4><i class="bi bi-palette"></i> إعدادات المظهر والألوان</h4>
        <a href="index.php" class="btn-sm"><i class="bi bi-arrow-right"></i> رجوع</a>
    </div>
    
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="appearance-form">
            <input type="hidden" name="csrf" value="<?php echo escape($_SESSION['csrf']); ?>">
            
            <div class="form-section">
                <h5>شعار الموقع</h5>
                <div class="logo-upload">
                    <?php if ($appearance['site_logo']): ?>
                    <img src="<?php echo BASE_URL . '/' . $appearance['site_logo']; ?>" alt="Logo" class="current-logo">
                    <?php endif; ?>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                    <small class="text-muted">الصيغ المدعومة: PNG, JPG, SVG (الحجم الموصى به: 200×60 بكسل)</small>
                </div>
            </div>
            
            <div class="form-section">
                <h5>الألوان الرئيسية</h5>
                <div class="colors-grid">
                    <div class="color-picker">
                        <label>اللون الأساسي</label>
                        <input type="color" name="primary_color" value="<?php echo $appearance['primary_color'] ?: '#7c5cff'; ?>">
                        <span class="color-value"><?php echo $appearance['primary_color'] ?: '#7c5cff'; ?></span>
                    </div>
                    <div class="color-picker">
                        <label>اللون الثانوي</label>
                        <input type="color" name="secondary_color" value="<?php echo $appearance['secondary_color'] ?: '#764ba2'; ?>">
                        <span class="color-value"><?php echo $appearance['secondary_color'] ?: '#764ba2'; ?></span>
                    </div>
                    <div class="color-picker">
                        <label>لون النجاح</label>
                        <input type="color" name="success_color" value="<?php echo $appearance['success_color'] ?: '#10b981'; ?>">
                        <span class="color-value"><?php echo $appearance['success_color'] ?: '#10b981'; ?></span>
                    </div>
                    <div class="color-picker">
                        <label>لون الخطر</label>
                        <input type="color" name="danger_color" value="<?php echo $appearance['danger_color'] ?: '#ef4444'; ?>">
                        <span class="color-value"><?php echo $appearance['danger_color'] ?: '#ef4444'; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary"><i class="bi bi-check-circle"></i> حفظ التغييرات</button>
            </div>
        </form>
    </div>
</div>

<style>
.appearance-form { max-width: 800px; }
.form-section { background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; margin-bottom: 20px; }
.form-section h5 { margin: 0 0 16px 0; color: var(--primary); }
.logo-upload { display: flex; flex-direction: column; gap: 12px; }
.current-logo { max-width: 200px; height: auto; background: white; padding: 10px; border-radius: 8px; }
.colors-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; }
.color-picker { display: flex; flex-direction: column; gap: 8px; }
.color-picker label { font-size: 14px; color: var(--text-secondary); }
.color-picker input[type="color"] { width: 100%; height: 50px; border: 2px solid var(--glass-border); border-radius: 8px; cursor: pointer; }
.color-value { font-size: 13px; color: var(--text-muted); text-align: center; }
.form-actions { display: flex; gap: 12px; justify-content: flex-end; }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>