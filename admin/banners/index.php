<?php
require_once '../../includes/store-init.php';
if (!isAdmin()) redirect('/admin/login.php');

$page_title = 'إدارة البانرات';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $image = uploadImage($_FILES['image'], 'banners');
                if ($image['success']) {
                    $db->insert('banners', [
                        'title_ar' => $_POST['title_ar'],
                        'title_en' => $_POST['title_en'],
                        'image_path' => '/uploads/banners/' . $image['filename'],
                        'link_url' => $_POST['link_url'],
                        'position' => $_POST['position'],
                        'is_active' => isset($_POST['is_active']) ? 1 : 0,
                        'start_date' => $_POST['start_date'] ?: null,
                        'end_date' => $_POST['end_date'] ?: null,
                        'sort_order' => (int)$_POST['sort_order']
                    ]);
                    $_SESSION['success'] = 'تم إضافة البانر بنجاح';
                }
                break;
                
            case 'delete':
                $banner = $db->fetchOne("SELECT image_path FROM banners WHERE id = ?", [$_POST['id']]);
                if ($banner) {
                    deleteFile(UPLOAD_PATH . '/banners/' . basename($banner['image_path']));
                    $db->delete('banners', ['id' => $_POST['id']]);
                    $_SESSION['success'] = 'تم حذف البانر بنجاح';
                }
                break;
        }
        redirect('/admin/banners/');
    }
}

$banners = $db->fetchAll("SELECT * FROM banners ORDER BY sort_order, id DESC");

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-image"></i> إدارة البانرات</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bannerModal">
            <i class="bi bi-plus-circle"></i> إضافة بانر
        </button>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <?php foreach ($banners as $banner): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm">
                <img src="<?php echo escape($banner['image_path']); ?>" class="card-img-top" style="height:200px;object-fit:cover">
                <div class="card-body">
                    <h5 class="card-title"><?php echo escape($banner['title_ar']); ?></h5>
                    <p class="card-text">
                        <small class="text-muted">
                            <i class="bi bi-link"></i> <?php echo escape($banner['link_url'] ?: 'لا يوجد رابط'); ?>
                        </small>
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-<?php echo $banner['is_active'] ? 'success' : 'secondary'; ?>">
                            <?php echo $banner['is_active'] ? 'مفعّل' : 'معطّل'; ?>
                        </span>
                        <span class="badge bg-info"><?php echo $banner['position']; ?></span>
                    </div>
                    <div class="mt-2">
                        <small><i class="bi bi-cursor"></i> النقرات: <?php echo $banner['clicks']; ?></small>
                    </div>
                </div>
                <div class="card-footer">
                    <form method="POST" class="d-inline" onsubmit="return confirm('هل تريد الحذف؟')">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $banner['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i> حذف
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Banner Modal -->
<div class="modal fade" id="bannerModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="add">
                
                <div class="modal-header">
                    <h5 class="modal-title">إضافة بانر جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">العنوان (عربي)</label>
                        <input type="text" class="form-control" name="title_ar" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">العنوان (English)</label>
                        <input type="text" class="form-control" name="title_en">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">الصورة *</label>
                        <input type="file" class="form-control" name="image" accept="image/*" required>
                        <small class="text-muted">الحجم المثالي: 1920x400 بكسل</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">الرابط</label>
                        <input type="url" class="form-control" name="link_url" placeholder="https://...">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">الموقع</label>
                            <select class="form-select" name="position">
                                <option value="top">أعلى الصفحة</option>
                                <option value="middle">منتصف الصفحة</option>
                                <option value="bottom">أسفل الصفحة</option>
                                <option value="sidebar">الشريط الجانبي</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">الترتيب</label>
                            <input type="number" class="form-control" name="sort_order" value="0">
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">تاريخ البداية</label>
                            <input type="datetime-local" class="form-control" name="start_date">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">تاريخ النهاية</label>
                            <input type="datetime-local" class="form-control" name="end_date">
                        </div>
                    </div>
                    
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="is_active" checked>
                        <label class="form-check-label">مفعّل</label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">إضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>