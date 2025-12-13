<?php
require_once '../../includes/store-init.php';
if (!isAdmin()) redirect('/admin/login.php');

$page_title = 'إعدادات المظهر';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = [];
    
    // Handle file uploads
    if (!empty($_FILES['site_logo']['tmp_name'])) {
        $logo = uploadFile($_FILES['site_logo'], 'settings');
        if ($logo) $updates['site_logo'] = $logo;
    }
    
    if (!empty($_FILES['site_favicon']['tmp_name'])) {
        $favicon = uploadFile($_FILES['site_favicon'], 'settings');
        if ($favicon) $updates['site_favicon'] = $favicon;
    }
    
    // Text settings
    $text_fields = ['site_name', 'primary_color', 'secondary_color', 'success_color', 'danger_color', 'homepage_latest', 'homepage_bestsellers', 'homepage_3d'];
    foreach ($text_fields as $field) {
        if (isset($_POST[$field])) {
            $updates[$field] = $_POST[$field];
        }
    }
    
    // Update database
    foreach ($updates as $key => $value) {
        $db->execute(
            "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?",
            [$key, $value, $value]
        );
    }
    
    $_SESSION['success'] = 'تم تحديث إعدادات المظهر بنجاح';
    redirect('/admin/settings/appearance.php');
}

// Get current settings
$settings_data = $db->fetchAll("SELECT setting_key, setting_value FROM site_settings");
$settings = [];
foreach ($settings_data as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-palette text-primary"></i> إعدادات المظهر</h2>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="row g-4">
        
        <!-- General Settings -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-gear text-primary"></i> إعدادات عامة</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم المتجر</label>
                        <input type="text" name="site_name" class="form-control" value="<?php echo escape($settings['site_name'] ?? '3D Store'); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">شعار المتجر</label>
                        <?php if (!empty($settings['site_logo'])): ?>
                        <div class="mb-2">
                            <img src="<?php echo UPLOAD_URL . '/settings/' . $settings['site_logo']; ?>" style="max-height:80px" class="img-thumbnail">
                        </div>
                        <?php endif; ?>
                        <input type="file" name="site_logo" class="form-control" accept="image/*">
                        <small class="text-muted">المقاس المثالي: 200x60 بكسل</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">أيقونة المتجر (Favicon)</label>
                        <?php if (!empty($settings['site_favicon'])): ?>
                        <div class="mb-2">
                            <img src="<?php echo UPLOAD_URL . '/settings/' . $settings['site_favicon']; ?>" style="max-height:32px" class="img-thumbnail">
                        </div>
                        <?php endif; ?>
                        <input type="file" name="site_favicon" class="form-control" accept="image/*">
                        <small class="text-muted">المقاس المثالي: 32x32 بكسل</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Colors -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-palette-fill text-primary"></i> الألوان</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">اللون الأساسي (Primary)</label>
                        <div class="input-group">
                            <input type="color" name="primary_color" class="form-control form-control-color" value="<?php echo $settings['primary_color'] ?? '#3b82f6'; ?>">
                            <input type="text" class="form-control" value="<?php echo $settings['primary_color'] ?? '#3b82f6'; ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">اللون الثانوي (Secondary)</label>
                        <div class="input-group">
                            <input type="color" name="secondary_color" class="form-control form-control-color" value="<?php echo $settings['secondary_color'] ?? '#8b5cf6'; ?>">
                            <input type="text" class="form-control" value="<?php echo $settings['secondary_color'] ?? '#8b5cf6'; ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">لون النجاح (Success)</label>
                        <div class="input-group">
                            <input type="color" name="success_color" class="form-control form-control-color" value="<?php echo $settings['success_color'] ?? '#10b981'; ?>">
                            <input type="text" class="form-control" value="<?php echo $settings['success_color'] ?? '#10b981'; ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">لون الخطر (Danger)</label>
                        <div class="input-group">
                            <input type="color" name="danger_color" class="form-control form-control-color" value="<?php echo $settings['danger_color'] ?? '#ef4444'; ?>">
                            <input type="text" class="form-control" value="<?php echo $settings['danger_color'] ?? '#ef4444'; ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mb-0">
                        <small><i class="bi bi-info-circle"></i> سيتم تطبيق الألوان على كامل الموقع</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Homepage Display -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-house text-primary"></i> إعدادات الصفحة الرئيسية</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">عدد أحدث المنتجات</label>
                            <input type="number" name="homepage_latest" class="form-control" min="4" max="20" value="<?php echo $settings['homepage_latest'] ?? 10; ?>">
                            <small class="text-muted">من 4 إلى 20 منتج</small>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">عدد الأكثر مبيعاً</label>
                            <input type="number" name="homepage_bestsellers" class="form-control" min="4" max="16" value="<?php echo $settings['homepage_bestsellers'] ?? 8; ?>">
                            <small class="text-muted">من 4 إلى 16 منتج</small>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">عدد منتجات 3D</label>
                            <input type="number" name="homepage_3d" class="form-control" min="4" max="12" value="<?php echo $settings['homepage_3d'] ?? 4; ?>">
                            <small class="text-muted">من 4 إلى 12 منتج</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    
    <div class="text-center mt-4">
        <button type="submit" class="btn btn-primary btn-lg px-5">
            <i class="bi bi-save"></i> حفظ جميع التغييرات
        </button>
    </div>
</form>

<script>
document.querySelectorAll('input[type="color"]').forEach(input => {
    input.addEventListener('change', function() {
        this.nextElementSibling.value = this.value;
    });
});
</script>

<?php include '../includes/footer.php'; ?>