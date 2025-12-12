<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

$page_title = 'إضافة منتج جديد';
$active_page = 'products-add';

$categories = $db->fetchAll("SELECT id, name_ar FROM categories WHERE status = 'active' ORDER BY name_ar");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
        $error = 'خطأ في التحقق';
    } else {
        $data = [
            'sku' => sanitizeInput($_POST['sku'] ?? ''),
            'name_ar' => sanitizeInput($_POST['name_ar'] ?? ''),
            'name_en' => sanitizeInput($_POST['name_en'] ?? ''),
            'slug' => sanitizeInput($_POST['slug'] ?? ''),
            'description_ar' => sanitizeInput($_POST['description_ar'] ?? ''),
            'description_en' => sanitizeInput($_POST['description_en'] ?? ''),
            'product_type' => $_POST['product_type'] ?? 'normal',
            'category_id' => isset($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'price_ils' => (float)($_POST['price_ils'] ?? 0),
            'price_usd' => (float)($_POST['price_usd'] ?? 0),
            'cost_price' => (float)($_POST['cost_price'] ?? 0),
            'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
            'reorder_level' => (int)($_POST['reorder_level'] ?? 10),
            'status' => $_POST['status'] ?? 'active',
        ];
        
        // Upload image
        if (!empty($_FILES['image']['name'])) {
            $result = uploadFile($_FILES['image'], PRODUCT_IMAGE_PATH, ALLOWED_IMAGE_TYPES);
            if ($result['success']) {
                $data['image_path'] = $result['filename'];
            } else {
                $error = implode(', ', $result['errors']);
            }
        }
        
        // Upload 3D model
        if (!empty($_FILES['model_3d']['name']) && $data['product_type'] === '3d') {
            $result = uploadFile($_FILES['model_3d'], MODEL_3D_PATH, ALLOWED_3D_TYPES);
            if ($result['success']) {
                $data['model_3d_path'] = $result['filename'];
            } else {
                $error = implode(', ', $result['errors']);
            }
        }
        
        if (!isset($error)) {
            try {
                $id = $db->insert(
                    "INSERT INTO products 
                     (sku, name_ar, name_en, slug, description_ar, description_en, product_type, 
                      category_id, price_ils, price_usd, cost_price, image_path, model_3d_path, 
                      stock_quantity, reorder_level, status, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                    [
                        $data['sku'], $data['name_ar'], $data['name_en'], $data['slug'],
                        $data['description_ar'], $data['description_en'], $data['product_type'],
                        $data['category_id'], $data['price_ils'], $data['price_usd'], $data['cost_price'],
                        $data['image_path'] ?? null, $data['model_3d_path'] ?? null,
                        $data['stock_quantity'], $data['reorder_level'], $data['status']
                    ]
                );
                header('Location: index.php');
                exit;
            } catch (Exception $e) {
                $error = 'حدث خطأ أثناء الحفظ';
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<?php if (isset($error)): ?>
<div class="alert alert-danger">
    <i class="bi bi-exclamation-circle"></i> <?php echo $error; ?>
</div>
<?php endif; ?>

<div class="glass-card">
    <div class="card-header space-between">
        <h4><i class="bi bi-plus-circle"></i> إضافة منتج جديد</h4>
        <a href="index.php" class="btn-sm"><i class="bi bi-arrow-right"></i> رجوع</a>
    </div>
    
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="product-form">
            <input type="hidden" name="csrf" value="<?php echo escape($_SESSION['csrf']); ?>">
            
            <div class="form-section">
                <h5>المعلومات الأساسية</h5>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>رمز المنتج (SKU) <span class="required">*</span></label>
                        <input type="text" name="sku" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>نوع المنتج <span class="required">*</span></label>
                        <select name="product_type" class="form-control" id="productType" required>
                            <option value="normal">عادي</option>
                            <option value="3d">ثلاثي الأبعاد (3D)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>اسم المنتج (عربي) <span class="required">*</span></label>
                        <input type="text" name="name_ar" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>اسم المنتج (إنجليزي) <span class="required">*</span></label>
                        <input type="text" name="name_en" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Slug (URL)</label>
                    <input type="text" name="slug" class="form-control" placeholder="product-name-slug">
                    <small class="text-muted">سيتم إنشاؤه تلقائياً إذا ترك فارغاً</small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>الوصف (عربي)</label>
                        <textarea name="description_ar" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label>الوصف (إنجليزي)</label>
                        <textarea name="description_en" class="form-control" rows="4"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5>الأسعار والمخزون</h5>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>السعر (شيكل) <span class="required">*</span></label>
                        <input type="number" step="0.01" name="price_ils" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>السعر (دولار)</label>
                        <input type="number" step="0.01" name="price_usd" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>سعر التكلفة</label>
                        <input type="number" step="0.01" name="cost_price" class="form-control">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>الكمية في المخزون <span class="required">*</span></label>
                        <input type="number" name="stock_quantity" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>حد إعادة الطلب</label>
                        <input type="number" name="reorder_level" class="form-control" value="10">
                    </div>
                    <div class="form-group">
                        <label>التصنيف</label>
                        <select name="category_id" class="form-control">
                            <option value="">بدون تصنيف</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo escape($cat['name_ar']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5>الصور والملفات</h5>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>صورة المنتج</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">الصيغ المدعومة: JPG, PNG, WEBP</small>
                    </div>
                    <div class="form-group" id="model3dGroup" style="display: none;">
                        <label>ملف 3D (.glb / .gltf)</label>
                        <input type="file" name="model_3d" class="form-control" accept=".glb,.gltf">
                        <small class="text-muted">رفع نموذج ثلاثي الأبعاد</small>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5>إعدادات أخرى</h5>
                
                <div class="form-group">
                    <label>الحالة</label>
                    <select name="status" class="form-control">
                        <option value="active">نشط</option>
                        <option value="inactive">غير نشط</option>
                        <option value="draft">مسودة</option>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary"><i class="bi bi-check-circle"></i> حفظ المنتج</button>
                <a href="index.php" class="btn-sm">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<style>
.product-form { max-width: 900px; }
.form-section { background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; margin-bottom: 20px; }
.form-section h5 { margin: 0 0 16px 0; color: var(--primary); font-size: 16px; }
.form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 14px; color: var(--text-secondary); }
.required { color: var(--danger); }
.form-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; }
</style>

<script>
document.getElementById('productType').addEventListener('change', function() {
    document.getElementById('model3dGroup').style.display = this.value === '3d' ? 'block' : 'none';
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>