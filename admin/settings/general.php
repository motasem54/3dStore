<?php
require_once '../../includes/store-init.php';
if (!isAdmin()) redirect('/admin/login.php');

$page_title = 'الإعدادات العامة';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Language settings
    updateSetting('enable_arabic', isset($_POST['enable_arabic']) ? '1' : '0');
    updateSetting('enable_english', isset($_POST['enable_english']) ? '1' : '0');
    
    // 3D Products settings
    updateSetting('enable_3d_products', isset($_POST['enable_3d_products']) ? '1' : '0');
    updateSetting('show_3d_badge', isset($_POST['show_3d_badge']) ? '1' : '0');
    updateSetting('3d_viewer_auto_rotate', isset($_POST['3d_viewer_auto_rotate']) ? '1' : '0');
    
    // Categories settings
    updateSetting('category_icon_type', $_POST['category_icon_type'] ?? 'image');
    updateSetting('show_categories_home', isset($_POST['show_categories_home']) ? '1' : '0');
    updateSetting('categories_position', $_POST['categories_position'] ?? 'above_products');
    
    // Homepage settings
    updateSetting('show_homepage_slider', isset($_POST['show_homepage_slider']) ? '1' : '0');
    
    $_SESSION['success'] = 'تم حفظ الإعدادات بنجاح';
    redirect('/admin/settings/general.php');
}

$settings = [
    'enable_arabic' => getSetting('enable_arabic', '1'),
    'enable_english' => getSetting('enable_english', '0'),
    'enable_3d_products' => getSetting('enable_3d_products', '1'),
    'show_3d_badge' => getSetting('show_3d_badge', '1'),
    '3d_viewer_auto_rotate' => getSetting('3d_viewer_auto_rotate', '1'),
    'category_icon_type' => getSetting('category_icon_type', 'image'),
    'show_categories_home' => getSetting('show_categories_home', '1'),
    'categories_position' => getSetting('categories_position', 'above_products'),
    'show_homepage_slider' => getSetting('show_homepage_slider', '1')
];

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-gear"></i> الإعدادات العامة</h2>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <form method="POST" class="needs-validation" novalidate>
        <?php echo csrfField(); ?>
        
        <!-- Language Settings -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-translate"></i> إعدادات اللغة</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="enable_arabic" name="enable_arabic" 
                                   <?php echo $settings['enable_arabic'] == '1' ? 'checked' : ''; ?>
                                   onchange="updateLanguageWarning()">
                            <label class="form-check-label" for="enable_arabic">
                                <strong>تفعيل اللغة العربية</strong>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="enable_english" name="enable_english" 
                                   <?php echo $settings['enable_english'] == '1' ? 'checked' : ''; ?>
                                   onchange="updateLanguageWarning()">
                            <label class="form-check-label" for="enable_english">
                                <strong>تفعيل اللغة الإنجليزية</strong>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info" id="languageWarning">
                    <i class="bi bi-info-circle"></i>
                    <span id="languageWarningText"></span>
                </div>
            </div>
        </div>

        <!-- 3D Products Settings -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-box-seam"></i> إعدادات المنتجات ثلاثية الأبعاد</h5>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="enable_3d_products" name="enable_3d_products" 
                           <?php echo $settings['enable_3d_products'] == '1' ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="enable_3d_products">
                        <strong>تفعيل المنتجات ثلاثية الأبعاد</strong>
                        <br><small class="text-muted">عند التعطيل، لن تظهر أي منتجات 3D في الموقع</small>
                    </label>
                </div>
                
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="show_3d_badge" name="show_3d_badge" 
                           <?php echo $settings['show_3d_badge'] == '1' ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="show_3d_badge">
                        <strong>إظهار شارة 3D على المنتجات</strong>
                        <br><small class="text-muted">عرض علامة "3D" على المنتجات التي تحتوي على نماذج ثلاثية الأبعاد</small>
                    </label>
                </div>
                
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="3d_viewer_auto_rotate" name="3d_viewer_auto_rotate" 
                           <?php echo $settings['3d_viewer_auto_rotate'] == '1' ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="3d_viewer_auto_rotate">
                        <strong>دوران تلقائي للنماذج ثلاثية الأبعاد</strong>
                        <br><small class="text-muted">تدوير النموذج تلقائياً عند عرضه</small>
                    </label>
                </div>
            </div>
        </div>

        <!-- Categories Display Settings -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-grid-3x3"></i> إعدادات عرض الأقسام</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label"><strong>نوع عرض الأقسام</strong></label>
                        <select class="form-select" name="category_icon_type">
                            <option value="image" <?php echo $settings['category_icon_type'] == 'image' ? 'selected' : ''; ?>>صورة</option>
                            <option value="icon" <?php echo $settings['category_icon_type'] == 'icon' ? 'selected' : ''; ?>>أيقونة</option>
                        </select>
                        <small class="text-muted">اختر طريقة عرض الأقسام (صورة أو أيقونة)</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label"><strong>موقع الأقسام في الصفحة الرئيسية</strong></label>
                        <select class="form-select" name="categories_position">
                            <option value="above_products" <?php echo $settings['categories_position'] == 'above_products' ? 'selected' : ''; ?>>فوق المنتجات</option>
                            <option value="below_products" <?php echo $settings['categories_position'] == 'below_products' ? 'selected' : ''; ?>>تحت المنتجات</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_categories_home" name="show_categories_home" 
                           <?php echo $settings['show_categories_home'] == '1' ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="show_categories_home">
                        <strong>إظهار الأقسام في الصفحة الرئيسية</strong>
                    </label>
                </div>
            </div>
        </div>

        <!-- Homepage Settings -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-house"></i> إعدادات الصفحة الرئيسية</h5>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="show_homepage_slider" name="show_homepage_slider" 
                           <?php echo $settings['show_homepage_slider'] == '1' ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="show_homepage_slider">
                        <strong>إظهار السلايدر</strong>
                        <br><small class="text-muted">عرض السلايدر في أعلى الصفحة الرئيسية</small>
                    </label>
                </div>
                
                <div class="alert alert-secondary">
                    <i class="bi bi-lightbulb"></i> <strong>نصيحة:</strong> 
                    لترتيب عناصر الصفحة الرئيسية بالكامل، استخدم صفحة 
                    <a href="homepage-layout.php" class="alert-link">ترتيب الصفحة الرئيسية</a>
                </div>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-save"></i> حفظ الإعدادات
            </button>
        </div>
    </form>
</div>

<script>
function updateLanguageWarning() {
    const arabic = document.getElementById('enable_arabic').checked;
    const english = document.getElementById('enable_english').checked;
    const warningText = document.getElementById('languageWarningText');
    
    if (arabic && english) {
        warningText.textContent = '✅ اللغتان مفعلتان: عند إضافة منتج، يجب ملء الحقول بالعربية والإنجليزية';
    } else if (arabic && !english) {
        warningText.textContent = '✅ العربية فقط مفعلة: عند إضافة منتج، املأ الحقول العربية فقط';
    } else if (!arabic && english) {
        warningText.textContent = '✅ الإنجليزية فقط مفعلة: عند إضافة منتج، املأ الحقول الإنجليزية فقط';
    } else {
        warningText.textContent = '⚠️ تحذير: يجب تفعيل لغة واحدة على الأقل!';
        warningText.parentElement.classList.add('alert-danger');
        warningText.parentElement.classList.remove('alert-info');
        return;
    }
    
    warningText.parentElement.classList.remove('alert-danger');
    warningText.parentElement.classList.add('alert-info');
}

// Run on page load
window.addEventListener('DOMContentLoaded', updateLanguageWarning);
</script>

<?php include '../includes/footer.php'; ?>