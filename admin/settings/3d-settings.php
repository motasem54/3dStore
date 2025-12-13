<?php
require_once '../../includes/store-init.php';
if (!isAdmin()) redirect('/admin/login.php');

$page_title = 'إعدادات 3D';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    setSetting('meshy_api_key', trim($_POST['meshy_api_key']));
    setSetting('enable_auto_3d', isset($_POST['enable_auto_3d']) ? 1 : 0);
    setSetting('3d_generation_mode', $_POST['3d_generation_mode']);
    
    $_SESSION['success'] = 'تم حفظ إعدادات 3D';
    redirect('/admin/settings/3d-settings.php');
}

$meshy_api_key = getSetting('meshy_api_key', '');
$enable_auto_3d = getSetting('enable_auto_3d', 0);
$generation_mode = getSetting('3d_generation_mode', 'manual');

// Statistics
$total_products = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE image_path IS NOT NULL")['count'];
$with_3d = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE model_3d_status = 'completed'")['count'];
$processing = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE model_3d_status = 'processing'")['count'];
$failed = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE model_3d_status = 'failed'")['count'];

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box text-primary"></i> إعدادات 3D</h2>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-key"></i> إعدادات Meshy AI</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading"><i class="bi bi-info-circle"></i> كيفية الحصول على API Key:</h6>
                        <ol class="mb-0 pe-3">
                            <li>اذهب إلى: <a href="https://www.meshy.ai" target="_blank">meshy.ai</a></li>
                            <li>أنشئ حساب مجاني</li>
                            <li>اذهب لـ <strong>API Keys</strong> من لوحة التحكم</li>
                            <li>انسخ الـ API Key والصقه هنا</li>
                            <li>المجاني: 200 تحويل/شهر</li>
                        </ol>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Meshy AI API Key</label>
                        <input type="text" name="meshy_api_key" class="form-control" value="<?php echo escape($meshy_api_key); ?>" placeholder="msy_xxxxxxxxxxxxx">
                        <small class="text-muted">ابدأ بـ: msy_</small>
                    </div>
                    
                    <div class="form-check form-switch mb-4">
                        <input type="checkbox" name="enable_auto_3d" class="form-check-input" id="enable_auto_3d" <?php echo $enable_auto_3d ? 'checked' : ''; ?> style="width:48px;height:24px">
                        <label class="form-check-label fw-bold" for="enable_auto_3d" style="margin-right:10px">تفعيل التحويل التلقائي لـ 3D</label>
                        <div class="form-text">عند رفع صورة منتج جديد، سيتم تحويلها لـ 3D تلقائياً</div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">وضع التوليد</label>
                        <select name="3d_generation_mode" class="form-select">
                            <option value="manual" <?php echo $generation_mode === 'manual' ? 'selected' : ''; ?>>يدوي - يتطلب موافقة Admin</option>
                            <option value="auto" <?php echo $generation_mode === 'auto' ? 'selected' : ''; ?>>تلقائي - فوري عند رفع الصورة</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-save"></i> حفظ الإعدادات
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> كيف يعمل؟</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3 text-center">
                        <div class="bg-light rounded p-3 mb-2">
                            <i class="bi bi-cloud-upload" style="font-size:2rem;color:var(--primary)"></i>
                        </div>
                        <small class="fw-bold">1. رفع صورة المنتج</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="bg-light rounded p-3 mb-2">
                            <i class="bi bi-send" style="font-size:2rem;color:var(--info)"></i>
                        </div>
                        <small class="fw-bold">2. إرسال لـ Meshy AI</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="bg-light rounded p-3 mb-2">
                            <i class="bi bi-hourglass-split" style="font-size:2rem;color:var(--warning)"></i>
                        </div>
                        <small class="fw-bold">3. المعالجة (2-5 دقائق)</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="bg-light rounded p-3 mb-2">
                            <i class="bi bi-box" style="font-size:2rem;color:var(--success)"></i>
                        </div>
                        <small class="fw-bold">4. تحميل 3D Model</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> الإحصائيات</h5>
            </div>
            <div class="card-body">
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">إجمالي المنتجات</div>
                            <h3 class="mb-0"><?php echo $total_products; ?></h3>
                        </div>
                        <i class="bi bi-box-seam" style="font-size:2rem;color:var(--primary);opacity:0.3"></i>
                    </div>
                </div>
                
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">لديها 3D</div>
                            <h3 class="mb-0 text-success"><?php echo $with_3d; ?></h3>
                        </div>
                        <i class="bi bi-check-circle" style="font-size:2rem;color:var(--success);opacity:0.3"></i>
                    </div>
                </div>
                
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">قيد المعالجة</div>
                            <h3 class="mb-0 text-warning"><?php echo $processing; ?></h3>
                        </div>
                        <i class="bi bi-hourglass-split" style="font-size:2rem;color:var(--warning);opacity:0.3"></i>
                    </div>
                </div>
                
                <div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">فشل التحويل</div>
                            <h3 class="mb-0 text-danger"><?php echo $failed; ?></h3>
                        </div>
                        <i class="bi bi-x-circle" style="font-size:2rem;color:var(--danger);opacity:0.3"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-link"></i> روابط مفيدة</h5>
            </div>
            <div class="card-body">
                <a href="https://www.meshy.ai" target="_blank" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-globe"></i> موقع Meshy AI
                </a>
                <a href="https://docs.meshy.ai" target="_blank" class="btn btn-outline-info w-100 mb-2">
                    <i class="bi bi-file-text"></i> التوثيق
                </a>
                <a href="/admin/products/?filter=no_3d" class="btn btn-outline-warning w-100">
                    <i class="bi bi-box"></i> منتجات بدون 3D
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>