<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

$page_title = 'إضافة تصنيف جديد';
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
                $data['image_path'] = $result['filename'];
            }
        }
        
        try {
            $db->insert(
                "INSERT INTO categories (name_ar, name_en, slug, description_ar, description_en, image_path, status, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $data['name_ar'], $data['name_en'], $data['slug'],
                    $data['description_ar'], $data['description_en'],
                    $data['image_path'] ?? null, $data['status']
                ]
            );
            $_SESSION['success'] = 'تم إضافة التصنيف بنجاح';
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
        <h4><i class="bi bi-plus-circle"></i> إضافة تصنيف جديد</h4>
        <a href="index.php" class="btn-sm"><i class="bi bi-arrow-right"></i> رجوع</a>
    </div>
    
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="category-form">
            <input type="hidden" name="csrf" value="<?php echo escape($_SESSION['csrf']); ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label>الاسم (عربي) <span class="required">*</span></label>
                    <input type="text" name="name_ar" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>الاسم (إنجليزي) <span class="required">*</span></label>
                    <input type="text" name="name_en" class="form-control" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Slug (URL)</label>
                <input type="text" name="slug" class="form-control" placeholder="category-slug">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>الوصف (عربي)</label>
                    <textarea name="description_ar" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>الوصف (إنجليزي)</label>
                    <textarea name="description_en" class="form-control" rows="3"></textarea>
                </div>
            </div>
            
            <div class="form-group">
                <label>صورة التصنيف</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            
            <div class="form-group">
                <label>الحالة</label>
                <select name="status" class="form-control">
                    <option value="active">نشط</option>
                    <option value="inactive">غير نشط</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary"><i class="bi bi-check-circle"></i> حفظ التصنيف</button>
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