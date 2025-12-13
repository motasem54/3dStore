<?php
require_once '../../includes/store-init.php';
require_once '../../includes/image-processor.php';

if (!isAdmin()) redirect('/admin/login.php');

$page_title = 'إعدادات العلامة المائية';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_settings') {
        $enable = isset($_POST['enable_watermark']) ? 1 : 0;
        setSetting('enable_watermark', $enable);
        setSetting('watermark_position', $_POST['watermark_position']);
        setSetting('watermark_opacity', (int)$_POST['watermark_opacity']);
        
        // Upload watermark image
        if (!empty($_FILES['watermark_image']['tmp_name'])) {
            $filename = uploadFile($_FILES['watermark_image'], 'settings', ['image/png', 'image/jpeg', 'image/gif']);
            if ($filename) {
                // Delete old watermark
                $old_watermark = getSetting('watermark_image', '');
                if ($old_watermark) {
                    deleteFile($old_watermark, 'settings');
                }
                setSetting('watermark_image', $filename);
            }
        }
        
        $_SESSION['success'] = 'تم حفظ إعدادات العلامة المائية';
    } elseif ($action === 'batch_apply') {
        $results = batchApplyWatermark();
        $_SESSION['success'] = "تم تطبيق العلامة على {$results['success']} منتج. فشل: {$results['failed']}";
    }
    
    redirect('/admin/settings/watermark.php');
}

$settings = [
    'enable_watermark' => getSetting('enable_watermark', 0),
    'watermark_image' => getSetting('watermark_image', ''),
    'watermark_position' => getSetting('watermark_position', 'bottom-right'),
    'watermark_opacity' => getSetting('watermark_opacity', 50)
];

$total_products = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE image_path IS NOT NULL")['count'];
$watermarked = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE watermark_applied = 1")['count'];
$pending = $total_products - $watermarked;

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-droplet text-primary"></i> إعدادات العلامة المائية</h2>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-gear"></i> الإعدادات</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="save_settings">
                    
                    <div class="form-check form-switch mb-4">
                        <input type="checkbox" name="enable_watermark" class="form-check-input" id="enable_watermark" <?php echo $settings['enable_watermark'] ? 'checked' : ''; ?> style="width:48px;height:24px">
                        <label class="form-check-label fw-bold" for="enable_watermark" style="margin-right:10px">تفعيل العلامة المائية</label>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">صورة العلامة المائية</label>
                        <?php if ($settings['watermark_image']): ?>
                        <div class="mb-3">
                            <img src="<?php echo UPLOAD_URL . '/settings/' . $settings['watermark_image']; ?>" style="max-height:100px;background:repeating-linear-gradient(45deg,#f0f0f0,#f0f0f0 10px,#fff 10px,#fff 20px);padding:10px;border-radius:8px">
                        </div>
                        <?php endif; ?>
                        <input type="file" name="watermark_image" class="form-control" accept="image/*">
                        <small class="text-muted">يفضل PNG شفاف - الحجم المناسب: 200x60px</small>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">موقع العلامة المائية</label>
                        <div class="row g-2">
                            <div class="col-4">
                                <input type="radio" name="watermark_position" value="top-left" id="pos_tl" class="btn-check" <?php echo $settings['watermark_position'] === 'top-left' ? 'checked' : ''; ?>>
                                <label class="btn btn-outline-primary w-100" for="pos_tl">
                                    <i class="bi bi-box-arrow-up-left"></i> أعلى يسار
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" name="watermark_position" value="center" id="pos_c" class="btn-check" <?php echo $settings['watermark_position'] === 'center' ? 'checked' : ''; ?>>
                                <label class="btn btn-outline-primary w-100" for="pos_c">
                                    <i class="bi bi-plus-circle"></i> الوسط
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" name="watermark_position" value="top-right" id="pos_tr" class="btn-check" <?php echo $settings['watermark_position'] === 'top-right' ? 'checked' : ''; ?>>
                                <label class="btn btn-outline-primary w-100" for="pos_tr">
                                    <i class="bi bi-box-arrow-up-right"></i> أعلى يمين
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" name="watermark_position" value="bottom-left" id="pos_bl" class="btn-check" <?php echo $settings['watermark_position'] === 'bottom-left' ? 'checked' : ''; ?>>
                                <label class="btn btn-outline-primary w-100" for="pos_bl">
                                    <i class="bi bi-box-arrow-down-left"></i> أسفل يسار
                                </label>
                            </div>
                            <div class="col-4"></div>
                            <div class="col-4">
                                <input type="radio" name="watermark_position" value="bottom-right" id="pos_br" class="btn-check" <?php echo $settings['watermark_position'] === 'bottom-right' ? 'checked' : ''; ?>>
                                <label class="btn btn-outline-primary w-100" for="pos_br">
                                    <i class="bi bi-box-arrow-down-right"></i> أسفل يمين
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">الشفافية (Opacity): <span id="opacity-value"><?php echo $settings['watermark_opacity']; ?>%</span></label>
                        <input type="range" name="watermark_opacity" class="form-range" min="10" max="100" value="<?php echo $settings['watermark_opacity']; ?>" oninput="document.getElementById('opacity-value').innerText = this.value + '%'">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-save"></i> حفظ الإعدادات
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> الإحصائيات</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <div class="text-muted small">إجمالي المنتجات</div>
                        <h3 class="mb-0"><?php echo $total_products; ?></h3>
                    </div>
                    <i class="bi bi-box-seam" style="font-size:2rem;color:var(--primary);opacity:0.3"></i>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <div class="text-muted small">تم تطبيق العلامة</div>
                        <h3 class="mb-0 text-success"><?php echo $watermarked; ?></h3>
                    </div>
                    <i class="bi bi-check-circle" style="font-size:2rem;color:var(--success);opacity:0.3"></i>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">بانتظار التطبيق</div>
                        <h3 class="mb-0 text-warning"><?php echo $pending; ?></h3>
                    </div>
                    <i class="bi bi-clock-history" style="font-size:2rem;color:var(--warning);opacity:0.3"></i>
                </div>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-play-circle"></i> إجراءات</h5>
            </div>
            <div class="card-body">
                <?php if ($pending > 0): ?>
                <form method="POST" onsubmit="return confirm('هل أنت متأكد من تطبيق العلامة المائية على جميع المنتجات؟ قد يستغرق بضع دقائق.')">
                    <input type="hidden" name="action" value="batch_apply">
                    <button type="submit" class="btn btn-warning w-100 mb-3">
                        <i class="bi bi-lightning"></i> تطبيق على جميع المنتجات
                    </button>
                </form>
                <?php else: ?>
                <div class="alert alert-success mb-0">
                    <i class="bi bi-check-circle"></i> تم تطبيق العلامة على جميع المنتجات!
                </div>
                <?php endif; ?>
                
                <div class="alert alert-info mb-0">
                    <small>
                        <strong>ملاحظة:</strong> سيتم تطبيق العلامة تلقائياً على الصور الجديدة عند رفعها
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>