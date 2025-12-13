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
if (!$product) {
    header('Location: index.php');
    exit;
}

$page_title = 'تعديل المنتج: ' . $product['name_ar'];
$active_page = 'products';

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
        
        if (!empty($_FILES['image']['name'])) {
            $result = uploadFile($_FILES['image'], PRODUCT_IMAGE_PATH, ALLOWED_IMAGE_TYPES);
            if ($result['success']) {
                if ($product['image_path'] && file_exists(PRODUCT_IMAGE_PATH . $product['image_path'])) {
                    unlink(PRODUCT_IMAGE_PATH . $product['image_path']);
                }
                $data['image_path'] = $result['filename'];
            }
        }
        
        if (!empty($_FILES['model_3d']['name']) && $data['product_type'] === '3d') {
            $result = uploadFile($_FILES['model_3d'], MODEL_3D_PATH, ALLOWED_3D_TYPES);
            if ($result['success']) {
                if ($product['model_3d_path'] && file_exists(MODEL_3D_PATH . $product['model_3d_path'])) {
                    unlink(MODEL_3D_PATH . $product['model_3d_path']);
                }
                $data['model_3d_path'] = $result['filename'];
            }
        }
        
        try {
            $fields = [];
            $values = [];
            foreach ($data as $key => $value) {
                $fields[] = "{$key} = ?";
                $values[] = $value;
            }
            $values[] = $product_id;
            
            $db->update(
                "UPDATE products SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?",
                $values
            );
            
            $_SESSION['success'] = 'تم تحديث المنتج بنجاح';
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $error = 'حدث خطأ أثناء الحفظ';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<?php if (isset($error)): ?>
<div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="glass-card">
    <div class="card-header space-between">
        <h4><i class="bi bi-pencil"></i> تعديل المنتج</h4>
        <a href="index.php" class="btn-sm"><i class="bi bi-arrow-right"></i> رجوع</a>
    </div>
    
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="product-form">
            <input type="hidden" name="csrf" value="<?php echo escape($_SESSION['csrf']); ?>">
            
            <div class="form-section">
                <h5>المعلومات الأساسية</h5>
                <div class="form-row">
                    <div class="form-group">
                        <label>رمز المنتج (SKU)</label>
                        <input type="text" name="sku" class="form-control" value="<?php echo escape($product['sku']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>نوع المنتج</label>
                        <select name="product_type" class="form-control" id="productType" required>
                            <option value="normal" <?php echo $product['product_type'] === 'normal' ? 'selected' : ''; ?>>عادي</option>
                            <option value="3d" <?php echo $product['product_type'] === '3d' ? 'selected' : ''; ?>>ثلاثي الأبعاد (3D)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>اسم المنتج (عربي)</label>
                        <input type="text" name="name_ar" class="form-control" value="<?php echo escape($product['name_ar']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>اسم المنتج (إنجليزي)</label>
                        <input type="text" name="name_en" class="form-control" value="<?php echo escape($product['name_en']); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Slug (URL)</label>
                    <input type="text" name="slug" class="form-control" value="<?php echo escape($product['slug']); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>الوصف (عربي)</label>
                        <textarea name="description_ar" class="form-control" rows="4"><?php echo escape($product['description_ar']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>الوصف (إنجليزي)</label>
                        <textarea name="description_en" class="form-control" rows="4"><?php echo escape($product['description_en']); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5>الأسعار والمخزون</h5>
                <div class="form-row">
                    <div class="form-group">
                        <label>السعر (شيكل)</label>
                        <input type="number" step="0.01" name="price_ils" class="form-control" value="<?php echo $product['price_ils']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>السعر (دولار)</label>
                        <input type="number" step="0.01" name="price_usd" class="form-control" value="<?php echo $product['price_usd']; ?>">
                    </div>
                    <div class="form-group">
                        <label>سعر التكلفة</label>
                        <input type="number" step="0.01" name="cost_price" class="form-control" value="<?php echo $product['cost_price']; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>الكمية في المخزون</label>
                        <input type="number" name="stock_quantity" class="form-control" value="<?php echo $product['stock_quantity']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>حد إعادة الطلب</label>
                        <input type="number" name="reorder_level" class="form-control" value="<?php echo $product['reorder_level']; ?>">
                    </div>
                    <div class="form-group">
                        <label>التصنيف</label>
                        <select name="category_id" class="form-control">
                            <option value="">بدون تصنيف</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo escape($cat['name_ar']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5>الصور والملفات</h5>
                <?php if ($product['image_path']): ?>
                <div style="margin-bottom: 12px;">
                    <img src="<?php echo UPLOAD_URL . '/products/' . $product['image_path']; ?>" 
                         alt="" style="max-width: 200px; border-radius: 8px;">
                </div>
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>تغيير الصورة</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="form-group" id="model3dGroup" style="<?php echo $product['product_type'] === '3d' ? '' : 'display:none;'; ?>">
                        <label>تغيير ملف 3D</label>
                        <input type="file" name="model_3d" class="form-control" accept=".glb,.gltf">
                        <?php if ($product['model_3d_path']): ?>
                        <small class="text-muted">الملف الحالي: <?php echo escape($product['model_3d_path']); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5>إعدادات أخرى</h5>
                <div class="form-group">
                    <label>الحالة</label>
                    <select name="status" class="form-control">
                        <option value="active" <?php echo $product['status'] === 'active' ? 'selected' : ''; ?>>نشط</option>
                        <option value="inactive" <?php echo $product['status'] === 'inactive' ? 'selected' : ''; ?>>غير نشط</option>
                        <option value="draft" <?php echo $product['status'] === 'draft' ? 'selected' : ''; ?>>مسودة</option>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary"><i class="bi bi-check-circle"></i> حفظ التعديلات</button>
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
.form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.form-group label { font-size: 14px; color: var(--text-secondary); }
.form-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; }
</style>

<script>
document.getElementById('productType').addEventListener('change', function() {
    document.getElementById('model3dGroup').style.display = this.value === '3d' ? 'block' : 'none';
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>