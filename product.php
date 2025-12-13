<?php
require_once 'includes/store-init.php';

$product_id = $_GET['id'] ?? 0;
$product = $db->fetchOne("SELECT * FROM products WHERE id = ? AND status = 'active'", [$product_id]);

if (!$product) {
    redirect('/404.php');
}

// Update views count
$db->execute("UPDATE products SET views = views + 1 WHERE id = ?", [$product_id]);

// Get all images
$images = $db->fetchAll(
    "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order",
    [$product_id]
);

$page_title = $product['name_ar'];
include 'includes/header.php';
?>

<section style="padding:80px 0;background:var(--bg-light)">
<div class="container">

<div class="row g-4">
    <!-- Images & 3D Viewer -->
    <div class="col-lg-6">
        <div style="position:sticky;top:100px">
            
            <?php if ($product['enable_3d_view'] && $product['model_3d_path']): ?>
            <!-- 3D / 2D Toggle -->
            <div class="btn-group w-100 mb-3" role="group">
                <button class="btn btn-outline-primary active" onclick="show2D()" id="btn-2d">
                    <i class="bi bi-image"></i> صور
                </button>
                <button class="btn btn-outline-primary" onclick="show3D()" id="btn-3d">
                    <i class="bi bi-box"></i> عرض 3D
                </button>
            </div>
            <?php endif; ?>
            
            <!-- 2D Images -->
            <div id="images-container">
                <!-- Main Image -->
                <div style="background:white;border-radius:16px;overflow:hidden;margin-bottom:16px;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
                    <img id="main-image" src="<?php echo UPLOAD_URL . '/products/' . $product['image_path']; ?>" style="width:100%;aspect-ratio:1;object-fit:cover;cursor:zoom-in" onclick="openLightbox(this.src)">
                </div>
                
                <!-- Thumbnails -->
                <?php if (count($images) > 1): ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(80px,1fr));gap:12px">
                    <?php foreach ($images as $img): ?>
                    <div style="background:white;border-radius:12px;overflow:hidden;cursor:pointer;border:2px solid transparent;transition:0.3s" onclick="changeImage('<?php echo UPLOAD_URL . '/products/' . $img['image_path']; ?>', this)" class="thumbnail">
                        <img src="<?php echo UPLOAD_URL . '/products/' . $img['image_path']; ?>" style="width:100%;aspect-ratio:1;object-fit:cover">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- 3D Viewer -->
            <?php if ($product['enable_3d_view'] && $product['model_3d_path']): ?>
            <div id="viewer-3d" style="display:none;background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);height:500px" data-model-path="<?php echo UPLOAD_URL . '/models/' . $product['model_3d_path']; ?>"></div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Product Details -->
    <div class="col-lg-6">
        <div style="background:white;border-radius:16px;padding:32px;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
            <h1 style="font-size:32px;font-weight:700;margin-bottom:16px"><?php echo escape($product['name_ar']); ?></h1>
            
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px">
                <div style="color:#fbbf24;display:flex;gap:4px">
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i>
                </div>
                <span style="color:var(--text-light);font-size:14px">(4.5) - <?php echo $product['views']; ?> مشاهدة</span>
            </div>
            
            <div style="display:flex;align-items:baseline;gap:16px;margin-bottom:32px">
                <span style="font-size:42px;font-weight:700;color:var(--primary)"><?php echo formatPrice($product['discount_price'] ?: $product['price']); ?></span>
                <?php if ($product['discount_price']): ?>
                <span style="font-size:24px;color:var(--text-light);text-decoration:line-through"><?php echo formatPrice($product['price']); ?></span>
                <span style="background:var(--danger);color:white;padding:4px 12px;border-radius:20px;font-size:14px;font-weight:700">
                    -<?php echo round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>%
                </span>
                <?php endif; ?>
            </div>
            
            <div style="border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:24px 0;margin-bottom:24px">
                <h3 style="font-size:18px;font-weight:600;margin-bottom:12px">الوصف</h3>
                <p style="color:var(--text);line-height:1.8;margin:0"><?php echo nl2br(escape($product['description_ar'])); ?></p>
            </div>
            
            <div style="display:flex;gap:12px;margin-bottom:24px">
                <div style="flex:0 0 120px">
                    <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" style="width:100%;padding:12px;border:2px solid var(--border);border-radius:12px;text-align:center;font-size:18px;font-weight:600">
                </div>
                <button onclick="addToCart(<?php echo $product_id; ?>)" style="flex:1;padding:12px 24px;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;border:none;border-radius:12px;font-size:18px;font-weight:600;cursor:pointer;transition:0.3s" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(59,130,246,0.4)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">
                    <i class="bi bi-cart-plus"></i> إضافة للسلة
                </button>
            </div>
            
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:24px">
                <button style="padding:12px;background:white;border:2px solid var(--border);border-radius:12px;font-weight:600;cursor:pointer;transition:0.3s" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='inherit'">
                    <i class="bi bi-heart"></i> إضافة للمفضلة
                </button>
                <button style="padding:12px;background:white;border:2px solid var(--border);border-radius:12px;font-weight:600;cursor:pointer;transition:0.3s" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='inherit'">
                    <i class="bi bi-share"></i> مشاركة
                </button>
                <button style="padding:12px;background:white;border:2px solid var(--border);border-radius:12px;font-weight:600;cursor:pointer;transition:0.3s" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='inherit'">
                    <i class="bi bi-flag"></i> إبلاغ
                </button>
            </div>
            
            <div style="background:var(--bg-light);padding:20px;border-radius:12px">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
                    <i class="bi bi-truck" style="font-size:24px;color:var(--primary)"></i>
                    <div>
                        <strong>شحن مجاني</strong>
                        <div style="font-size:13px;color:var(--text-light)">للطلبات فوق 200₪</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:12px">
                    <i class="bi bi-shield-check" style="font-size:24px;color:var(--success)"></i>
                    <div>
                        <strong>ضمان الجودة</strong>
                        <div style="font-size:13px;color:var(--text-light)">استرجاع مجاني خلال 14 يوم</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
</section>

<!-- Lightbox -->
<div id="lightbox" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.9);z-index:9999;align-items:center;justify-content:center" onclick="this.style.display='none'">
    <img id="lightbox-img" style="max-width:90%;max-height:90%;object-fit:contain">
</div>

<script src="/assets/js/model-viewer.js"></script>
<script>
function changeImage(src, thumb) {
    document.getElementById('main-image').src = src;
    document.querySelectorAll('.thumbnail').forEach(t => t.style.borderColor = 'transparent');
    thumb.style.borderColor = 'var(--primary)';
}

function openLightbox(src) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox').style.display = 'flex';
}

function show2D() {
    document.getElementById('images-container').style.display = 'block';
    document.getElementById('viewer-3d').style.display = 'none';
    document.getElementById('btn-2d').classList.add('active');
    document.getElementById('btn-3d').classList.remove('active');
}

function show3D() {
    document.getElementById('images-container').style.display = 'none';
    document.getElementById('viewer-3d').style.display = 'block';
    document.getElementById('btn-2d').classList.remove('active');
    document.getElementById('btn-3d').classList.add('active');
    
    // Initialize 3D viewer if not already
    if (!window.viewer3dInitialized) {
        const container = document.getElementById('viewer-3d');
        const modelPath = container.dataset.modelPath;
        new Product3DViewer(container, modelPath);
        window.viewer3dInitialized = true;
    }
}

function addToCart(productId) {
    const quantity = document.getElementById('quantity').value;
    
    fetch(`/api/cart.php?action=add&product_id=${productId}&quantity=${quantity}`, { method: 'POST' })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('تم إضافة المنتج للسلة بنجاح!');
            location.reload();
        } else {
            alert('حدث خطأ: ' + (data.error || 'غير معروف'));
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>