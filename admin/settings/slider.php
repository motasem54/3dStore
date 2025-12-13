<?php
require_once '../../includes/store-init.php';
if (!isAdmin()) redirect('/admin/login.php');

$page_title = 'إدارة Slider الصفحة الرئيسية';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $image = uploadFile($_FILES['image'], 'slider');
        if (!$image) {
            $_SESSION['error'] = 'فشل رفع الصورة';
        } else {
            $db->execute(
                "INSERT INTO homepage_slider (title_ar, title_en, subtitle_ar, subtitle_en, image_path, button_text_ar, button_text_en, button_link, background_color, text_color, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $_POST['title_ar'],
                    $_POST['title_en'],
                    $_POST['subtitle_ar'],
                    $_POST['subtitle_en'],
                    $image,
                    $_POST['button_text_ar'],
                    $_POST['button_text_en'],
                    $_POST['button_link'],
                    $_POST['background_color'],
                    $_POST['text_color'],
                    isset($_POST['is_active']) ? 1 : 0,
                    $_POST['sort_order']
                ]
            );
            $_SESSION['success'] = 'تمت إضافة Slide جديد';
        }
    } elseif ($action === 'edit') {
        $slide = $db->fetchOne("SELECT * FROM homepage_slider WHERE id = ?", [$_POST['id']]);
        
        if ($slide) {
            $image = $slide['image_path'];
            
            if (!empty($_FILES['image']['tmp_name'])) {
                $new_image = uploadFile($_FILES['image'], 'slider');
                if ($new_image) {
                    deleteFile($image, 'slider');
                    $image = $new_image;
                }
            }
            
            $db->execute(
                "UPDATE homepage_slider SET title_ar = ?, title_en = ?, subtitle_ar = ?, subtitle_en = ?, image_path = ?, button_text_ar = ?, button_text_en = ?, button_link = ?, background_color = ?, text_color = ?, is_active = ?, sort_order = ? WHERE id = ?",
                [
                    $_POST['title_ar'],
                    $_POST['title_en'],
                    $_POST['subtitle_ar'],
                    $_POST['subtitle_en'],
                    $image,
                    $_POST['button_text_ar'],
                    $_POST['button_text_en'],
                    $_POST['button_link'],
                    $_POST['background_color'],
                    $_POST['text_color'],
                    isset($_POST['is_active']) ? 1 : 0,
                    $_POST['sort_order'],
                    $_POST['id']
                ]
            );
            $_SESSION['success'] = 'تم تحديث Slide';
        }
    } elseif ($action === 'delete') {
        $slide = $db->fetchOne("SELECT * FROM homepage_slider WHERE id = ?", [$_POST['id']]);
        if ($slide) {
            deleteFile($slide['image_path'], 'slider');
            $db->execute("DELETE FROM homepage_slider WHERE id = ?", [$_POST['id']]);
            $_SESSION['success'] = 'تم حذف Slide';
        }
    } elseif ($action === 'reorder') {
        $order = json_decode($_POST['order'], true);
        foreach ($order as $index => $id) {
            $db->execute("UPDATE homepage_slider SET sort_order = ? WHERE id = ?", [$index, $id]);
        }
        jsonResponse(['success' => true]);
    }
    
    redirect('/admin/settings/slider.php');
}

$slides = $db->fetchAll("SELECT * FROM homepage_slider ORDER BY sort_order, id");

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-images text-primary"></i> إدارة Slider الصفحة الرئيسية</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-circle"></i> إضافة Slide
    </button>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div id="slides-container" class="row g-4">
    <?php foreach ($slides as $slide): ?>
    <div class="col-md-6" data-id="<?php echo $slide['id']; ?>" style="cursor:move">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-0">
                <div class="position-relative" style="height:200px;background:<?php echo $slide['background_color']; ?>;overflow:hidden">
                    <img src="<?php echo UPLOAD_URL . '/slider/' . $slide['image_path']; ?>" style="width:100%;height:100%;object-fit:cover;opacity:0.3">
                    <div class="position-absolute top-50 start-50 translate-middle text-center" style="color:<?php echo $slide['text_color']; ?>;z-index:2;width:80%">
                        <h3 class="fw-bold mb-2"><?php echo escape($slide['title_ar']); ?></h3>
                        <p class="mb-0"><?php echo escape($slide['subtitle_ar']); ?></p>
                    </div>
                    <?php if (!$slide['is_active']): ?>
                    <span class="badge bg-secondary position-absolute top-0 start-0 m-2">غير فعال</span>
                    <?php endif; ?>
                    <span class="badge bg-dark position-absolute top-0 end-0 m-2">#<?php echo $slide['sort_order']; ?></span>
                </div>
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block"><i class="bi bi-link"></i> <?php echo escape($slide['button_link']); ?></small>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="editSlide(<?php echo htmlspecialchars(json_encode($slide)); ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="deleteSlide(<?php echo $slide['id']; ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($slides)): ?>
<div class="text-center py-5">
    <i class="bi bi-images" style="font-size:64px;color:var(--text-light)"></i>
    <p class="text-muted mt-3">لا توجد slides بعد. أضف أول slide!</p>
</div>
<?php endif; ?>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة Slide جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">العنوان (عربي) *</label>
                            <input type="text" name="title_ar" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">العنوان (إنجليزي)</label>
                            <input type="text" name="title_en" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">العنوان الفرعي (عربي)</label>
                            <textarea name="subtitle_ar" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">العنوان الفرعي (إنجليزي)</label>
                            <textarea name="subtitle_en" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">الصورة *</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                            <small class="text-muted">المقاس المثالي: 1920x600px</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">نص الزر (عربي)</label>
                            <input type="text" name="button_text_ar" class="form-control" value="تسوق الآن">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">نص الزر (إنجليزي)</label>
                            <input type="text" name="button_text_en" class="form-control" value="Shop Now">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">رابط الزر</label>
                            <input type="url" name="button_link" class="form-control" value="/products.php">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">لون الخلفية</label>
                            <input type="color" name="background_color" class="form-control form-control-color w-100" value="#3b82f6">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">لون النص</label>
                            <input type="color" name="text_color" class="form-control form-control-color w-100" value="#ffffff">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">الترتيب</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" id="is_active_add" checked>
                                <label class="form-check-label" for="is_active_add">فعال</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل Slide</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body" id="editFormContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
const sortable = new Sortable(document.getElementById('slides-container'), {
    animation: 150,
    handle: '.card',
    onEnd: function() {
        const order = Array.from(document.querySelectorAll('#slides-container > div')).map(el => el.dataset.id);
        fetch('/admin/settings/slider.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=reorder&order=' + JSON.stringify(order)
        });
    }
});

function editSlide(slide) {
    document.getElementById('edit_id').value = slide.id;
    document.getElementById('editFormContent').innerHTML = `
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">العنوان (عربي) *</label>
                <input type="text" name="title_ar" class="form-control" value="${slide.title_ar}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">العنوان (إنجليزي)</label>
                <input type="text" name="title_en" class="form-control" value="${slide.title_en || ''}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">العنوان الفرعي (عربي)</label>
                <textarea name="subtitle_ar" class="form-control" rows="2">${slide.subtitle_ar || ''}</textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">العنوان الفرعي (إنجليزي)</label>
                <textarea name="subtitle_en" class="form-control" rows="2">${slide.subtitle_en || ''}</textarea>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label fw-bold">الصورة</label>
                <div class="mb-2"><img src="<?php echo UPLOAD_URL; ?>/slider/${slide.image_path}" style="max-height:100px"></div>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">نص الزر (عربي)</label>
                <input type="text" name="button_text_ar" class="form-control" value="${slide.button_text_ar || ''}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">نص الزر (إنجليزي)</label>
                <input type="text" name="button_text_en" class="form-control" value="${slide.button_text_en || ''}">
            </div>
            <div class="col-12 mb-3">
                <label class="form-label fw-bold">رابط الزر</label>
                <input type="url" name="button_link" class="form-control" value="${slide.button_link || ''}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">لون الخلفية</label>
                <input type="color" name="background_color" class="form-control form-control-color w-100" value="${slide.background_color}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">لون النص</label>
                <input type="color" name="text_color" class="form-control form-control-color w-100" value="${slide.text_color}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">الترتيب</label>
                <input type="number" name="sort_order" class="form-control" value="${slide.sort_order}">
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active_edit" ${slide.is_active ? 'checked' : ''}>
                    <label class="form-check-label" for="is_active_edit">فعال</label>
                </div>
            </div>
        </div>
    `;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function deleteSlide(id) {
    if (confirm('هل أنت متأكد من حذف هذا Slide؟')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="${id}">`;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>