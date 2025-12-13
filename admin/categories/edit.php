<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($category_id <= 0) {
    header('Location: index.php');
    exit;
}

$category = $db->fetch("SELECT * FROM categories WHERE id = ?", [$category_id]);
if (!$category) {
    header('Location: index.php');
    exit;
}

$page_title = 'تعديل التصنيف: ' . $category['name_ar'];
$active_page = 'categories';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
        $error = 'خطأ في التحقق';
    } else {
        $data = [
            'name_ar' => sanitizeInput($_POST['name_ar'] ?? ''),
            'name_en' => sanitizeInput($_POST['name_en'] ?? ''),
            'slug' => sanitizeInput($_POST['slug'] ?? ''),
            'description_ar' => sanitizeInput($_POST['description_ar'] ?? ''),
            'description_en' => sanitizeInput($_POST['description_en'] ?? ''),
            'status' => $_POST['status'] ?? 'active',
        ];
        
        if (!empty($_FILES['image']['name'])) {
            $result = uploadFile($_FILES['image'], 'uploads/categories/', ALLOWED_IMAGE_TYPES);
            if ($result['success']) {
                if ($category['image_path'] && file_exists('uploads/categories/' . $category['image_path'])) {
                    unlink('uploads/categories/' . $category['image_path']);
                }
                $data['image_path'] = $result['filename'];
            }
        }
        
        try {
            $fields = [];
            $values = [];
            foreach ($data as $key => $value) {
                $fields[] = "{$key} = ?";
                $values[] = $value;
            }
            $values[] = $category_id;
            
            $db->update(
                "UPDATE categories SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?",
                $values
            );
            
            $_SESSION['success'] = 'تم تحديث التصنيف بنجاح';
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
        <h4><i class="bi bi-pencil"></i> تعديل التصنيف</h4>
        <a href="index.php" class="btn-sm"><i class="bi bi-arrow-right"></i> رجوع</a>
    </div>
    
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="category-form">
            <input type="hidden" name="csrf" value="<?php echo escape($_SESSION['csrf']); ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label>الاسم (عربي) <span class="required">*</span></label>
                    <input type="text" name="name_ar" class="form-control" value="<?php echo escape($category['name_ar']); ?>" required>
                </div>
                <div class="form-group">
                    <label>الاسم (إنجليزي) <span class="required">*</span></label>
                    <input type="text" name="name_en" class="form-control" value="<?php echo escape($category['name_en']); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Slug (URL)</label>
                <input type="text" name="slug" class="form-control" value="<?php echo escape($category['slug']); ?>">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>الوصف (عربي)</label>
                    <textarea name="description_ar" class="form-control" rows="3"><?php echo escape($category['description_ar']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>الوصف (إنجليزي)</label>
                    <textarea name="description_en" class="form-control" rows="3"><?php echo escape($category['description_en']); ?></textarea>
                </div>
            </div>
            
            <?php if ($category['image_path']): ?>
            <div style="margin-bottom: 12px;">
                <img src="<?php echo UPLOAD_URL . '/categories/' . $category['image_path']; ?>" 
                     alt="" style="max-width: 200px; border-radius: 8px;">
            </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label>تغيير الصورة</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            
            <div class="form-group">
                <label>الحالة</label>
                <select name="status" class="form-control">
                    <option value="active" <?php echo $category['status'] === 'active' ? 'selected' : ''; ?>>نشط</option>
                    <option value="inactive" <?php echo $category['status'] === 'inactive' ? 'selected' : ''; ?>>غير نشط</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary"><i class="bi bi-check-circle"></i> حفظ التعديلات</button>
                <a href="index.php" class="btn-sm">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<style>
.category-form { max-width: 800px; }
.form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; margin-bottom: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.form-group label { font-size: 14px; color: var(--text-secondary); }
.required { color: var(--danger); }
.form-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>