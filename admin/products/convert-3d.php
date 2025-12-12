<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) {
    header('Location: index.php');
    exit;
}

$product = $db->fetch("SELECT * FROM products WHERE id = ?", [$product_id]);
if (!$product || empty($product['image_path'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'تحويل إلى 3D - ' . $product['name_ar'];
$active_page = 'products';

// Handle conversion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['convert'])) {
    if (empty($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
        $error = 'خطأ في التحقق';
    } else {
        // Here we would integrate with Image-to-3D API (Meshy AI or similar)
        // For now, we'll just simulate the process
        
        $api_key = getSetting('image_to_3d_api_key', '');
        if (empty($api_key)) {
            $error = 'لم يتم تعيين API Key لتحويل الصور إلى 3D. يرجى إضافته في الإعدادات.';
        } else {
            // Simulate API call
            // In real implementation, you would:
            // 1. Upload image to API
            // 2. Wait for processing (could take 40-90 seconds)
            // 3. Download generated GLB file
            // 4. Save to MODEL_3D_PATH
            
            $success = 'تم إرسال الصورة للتحويل. سيتم التحويل في الخلفية وإشعارك عند الانتهاء.';
            
            // Update product type to 3d
            $db->update(
                "UPDATE products SET product_type = '3d' WHERE id = ?",
                [$product_id]
            );
        }
    }
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
        <h4><i class="bi bi-magic"></i> تحويل صورة إلى نموذج 3D</h4>
        <a href="index.php" class="btn-sm"><i class="bi bi-arrow-right"></i> رجوع</a>
    </div>
    
    <div class="card-body">
        <div class="convert-3d-container">
            <div class="product-preview">
                <h5><?php echo escape($product['name_ar']); ?></h5>
                <p class="text-muted">SKU: <?php echo escape($product['sku']); ?></p>
                
                <div class="image-preview">
                    <img src="<?php echo UPLOAD_URL . '/products/' . $product['image_path']; ?>" alt="">
                </div>
            </div>
            
            <div class="conversion-info">
                <div class="info-box">
                    <i class="bi bi-info-circle" style="font-size: 48px; color: var(--info);"></i>
                    <h4>ماذا يحدث عند التحويل؟</h4>
                    <ul style="text-align: right; margin: 20px 0; line-height: 2;">
                        <li>سيتم إرسال صورة المنتج إلى Meshy AI</li>
                        <li>التحويل يستغرق حوالي 40-90 ثانية</li>
                        <li>سيتم إنشاء نموذج GLB ثلاثي الأبعاد</li>
                        <li>سيتم حفظ النموذج تلقائياً في المنتج</li>
                        <li>يمكنك عرض المنتج بشكل 3D في المتجر</li>
                    </ul>
                    
                    <form method="POST">
                        <input type="hidden" name="csrf" value="<?php echo escape($_SESSION['csrf']); ?>">
                        <button type="submit" name="convert" class="btn-primary btn-block btn-lg">
                            <i class="bi bi-magic"></i>
                            بدء التحويل إلى 3D
                        </button>
                    </form>
                    
                    <p class="text-muted" style="margin-top: 16px; font-size: 13px; text-align: center;">
                        ملاحظة: يتطلب التحويل API Key من Meshy AI أو خدمة مماثلة
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.convert-3d-container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
.product-preview { text-align: center; }
.image-preview { margin: 20px 0; padding: 20px; background: rgba(255,255,255,0.05); border-radius: 12px; }
.image-preview img { max-width: 100%; height: auto; border-radius: 8px; }
.conversion-info { display: flex; align-items: center; }
.info-box { background: rgba(255,255,255,0.05); padding: 30px; border-radius: 16px; text-align: center; }
.info-box h4 { margin: 16px 0 20px; }
.btn-lg { padding: 16px 32px; font-size: 16px; }
@media (max-width: 768px) {
    .convert-3d-container { grid-template-columns: 1fr; }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>