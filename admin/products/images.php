<?php
require_once '../../includes/store-init.php';
if (!isAdmin()) redirect('/admin/login.php');

$product_id = $_GET['id'] ?? 0;
$product = $db->fetchOne("SELECT * FROM products WHERE id = ?", [$product_id]);

if (!$product) {
    $_SESSION['error'] = 'المنتج غير موجود';
    redirect('/admin/products/');
}

$page_title = 'إدارة صور المنتج';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete') {
        $image_id = (int)$_POST['image_id'];
        $image = $db->fetchOne("SELECT * FROM product_images WHERE id = ? AND product_id = ?", [$image_id, $product_id]);
        
        if ($image) {
            deleteFile($image['image_path'], 'products');
            $db->execute("DELETE FROM product_images WHERE id = ?", [$image_id]);
            $_SESSION['success'] = 'تم حذف الصورة';
        }
    } elseif ($action === 'set_primary') {
        $image_id = (int)$_POST['image_id'];
        $image = $db->fetchOne("SELECT * FROM product_images WHERE id = ? AND product_id = ?", [$image_id, $product_id]);
        
        if ($image) {
            $db->execute("UPDATE product_images SET is_primary = 0 WHERE product_id = ?", [$product_id]);
            $db->execute("UPDATE product_images SET is_primary = 1 WHERE id = ?", [$image_id]);
            $db->execute("UPDATE products SET image_path = ? WHERE id = ?", [$image['image_path'], $product_id]);
            $_SESSION['success'] = 'تم تعيين الصورة كصورة رئيسية';
        }
    } elseif ($action === 'reorder') {
        $order = json_decode($_POST['order'], true);
        foreach ($order as $index => $image_id) {
            $db->execute("UPDATE product_images SET sort_order = ? WHERE id = ?", [$index, $image_id]);
        }
        jsonResponse(['success' => true]);
    }
    
    redirect("/admin/products/images.php?id=$product_id");
}

$images = $db->fetchAll(
    "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order",
    [$product_id]
);

include '../includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/dropzone.min.css">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-images text-primary"></i> 
        صور المنتج: <?php echo escape($product['name_ar']); ?>
    </h2>
    <a href="/admin/products/edit.php?id=<?php echo $product_id; ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-right"></i> عودة للمنتج
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-cloud-upload"></i> رفع صور جديدة</h5>
            </div>
            <div class="card-body">
                <form action="/api/upload-product-images.php" class="dropzone" id="imageDropzone">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    <div class="dz-message" data-dz-message>
                        <i class="bi bi-cloud-arrow-up" style="font-size:48px;color:var(--primary)"></i>
                        <h5 class="mt-3">اسحب الصور هنا</h5>
                        <p class="text-muted">أو انقر للاختيار</p>
                        <small class="text-muted">يمكنك رفع حتى 10 صور</small>
                    </div>
                </form>
                
                <div class="alert alert-info mt-3 mb-0">
                    <small>
                        <i class="bi bi-info-circle"></i> 
                        <strong>ملاحظة:</strong> سيتم تطبيق العلامة المائية تلقائياً على الصور الجديدة
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-grid-3x3"></i> الصور الحالية (<?php echo count($images); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($images)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-images" style="font-size:64px"></i>
                    <p class="mt-3">لا توجد صور لهذا المنتج</p>
                </div>
                <?php else: ?>
                <div id="sortable-images" class="row g-3">
                    <?php foreach ($images as $image): ?>
                    <div class="col-6 col-md-4" data-id="<?php echo $image['id']; ?>" style="cursor:move">
                        <div class="position-relative" style="border:2px solid <?php echo $image['is_primary'] ? 'var(--primary)' : 'var(--border)'; ?>;border-radius:12px;overflow:hidden">
                            <img src="<?php echo UPLOAD_URL . '/products/' . $image['image_path']; ?>" class="w-100" style="aspect-ratio:1;object-fit:cover">
                            
                            <?php if ($image['is_primary']): ?>
                            <span class="badge bg-primary position-absolute" style="top:8px;right:8px">رئيسية</span>
                            <?php endif; ?>
                            
                            <div class="position-absolute bottom-0 start-0 end-0 p-2" style="background:linear-gradient(transparent,rgba(0,0,0,0.7))">
                                <div class="btn-group w-100" role="group">
                                    <?php if (!$image['is_primary']): ?>
                                    <button class="btn btn-sm btn-light" onclick="setPrimary(<?php echo $image['id']; ?>)" title="تعيين رئيسية">
                                        <i class="bi bi-star"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-danger" onclick="deleteImage(<?php echo $image['id']; ?>)" title="حذف">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="alert alert-warning mt-3 mb-0">
                    <small><i class="bi bi-arrows-move"></i> اسحب الصور لإعادة ترتيبها</small>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/dropzone.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
Dropzone.autoDiscover = false;

const dropzone = new Dropzone('#imageDropzone', {
    url: '/api/upload-product-images.php',
    maxFiles: 10,
    maxFilesize: 5,
    acceptedFiles: 'image/*',
    addRemoveLinks: true,
    dictDefaultMessage: '',
    dictRemoveFile: 'حذف',
    dictCancelUpload: 'إلغاء',
    dictMaxFilesExceeded: 'لا يمكن رفع أكثر من 10 صور',
    sending: function(file, xhr, formData) {
        formData.append('product_id', <?php echo $product_id; ?>);
    },
    success: function(file, response) {
        setTimeout(() => location.reload(), 1000);
    },
    error: function(file, message) {
        alert('خطأ في رفع الصورة: ' + message);
    }
});

// Sortable
const sortable = new Sortable(document.getElementById('sortable-images'), {
    animation: 150,
    ghostClass: 'bg-light',
    onEnd: function() {
        const order = Array.from(document.querySelectorAll('#sortable-images > div')).map(el => el.dataset.id);
        
        fetch('/admin/products/images.php?id=<?php echo $product_id; ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=reorder&order=' + JSON.stringify(order)
        });
    }
});

function setPrimary(id) {
    if (confirm('تعيين هذه الصورة كصورة رئيسية؟')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="action" value="set_primary"><input type="hidden" name="image_id" value="${id}">`;
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteImage(id) {
    if (confirm('هل أنت متأكد من حذف هذه الصورة؟')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="image_id" value="${id}">`;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>