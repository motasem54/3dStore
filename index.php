<?php
require_once 'includes/store-init.php';
$page_title = 'الرئيسية';

// Get active slider items
$slides = $db->fetchAll("SELECT * FROM homepage_slider WHERE is_active = 1 ORDER BY sort_order LIMIT 5");

// Get settings
$latest_count = (int)getSetting('homepage_latest_count', 8);
$featured_count = (int)getSetting('homepage_featured_count', 8);
$bestseller_count = (int)getSetting('homepage_bestseller_count', 8);

// Get products
$latest_products = $db->fetchAll("SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT $latest_count");
$featured_products = $db->fetchAll("SELECT * FROM products WHERE status = 'active' AND is_featured = 1 ORDER BY created_at DESC LIMIT $featured_count");

include 'includes/header.php';
?>

<!-- Hero Slider -->
<?php if (!empty($slides)): ?>
<section style="padding:0;overflow:hidden">
<div id="heroSlider" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <?php foreach ($slides as $i => $slide): ?>
        <button type="button" data-bs-target="#heroSlider" data-bs-slide-to="<?php echo $i; ?>" <?php echo $i === 0 ? 'class="active"' : ''; ?>></button>
        <?php endforeach; ?>
    </div>
    
    <div class="carousel-inner">
        <?php foreach ($slides as $i => $slide): ?>
        <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>" style="height:600px;background:<?php echo $slide['background_color']; ?>;position:relative">
            <img src="<?php echo UPLOAD_URL . '/slider/' . $slide['image_path']; ?>" style="width:100%;height:100%;object-fit:cover;opacity:0.4;position:absolute;top:0;left:0">
            <div class="container" style="position:relative;z-index:2;height:100%;display:flex;align-items:center">
                <div style="max-width:600px;color:<?php echo $slide['text_color']; ?>">
                    <h1 style="font-size:56px;font-weight:800;margin-bottom:20px;text-shadow:2px 2px 8px rgba(0,0,0,0.3)"><?php echo escape($slide['title_ar']); ?></h1>
                    <p style="font-size:20px;margin-bottom:32px;text-shadow:1px 1px 4px rgba(0,0,0,0.3)"><?php echo escape($slide['subtitle_ar']); ?></p>
                    <?php if ($slide['button_text_ar'] && $slide['button_link']): ?>
                    <a href="<?php echo escape($slide['button_link']); ?>" style="display:inline-block;padding:16px 40px;background:white;color:<?php echo $slide['background_color']; ?>;border-radius:50px;font-size:18px;font-weight:700;text-decoration:none;box-shadow:0 8px 20px rgba(0,0,0,0.2);transition:0.3s" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 12px 30px rgba(0,0,0,0.3)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 8px 20px rgba(0,0,0,0.2)'">
                        <?php echo escape($slide['button_text_ar']); ?> <i class="bi bi-arrow-left"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <button class="carousel-control-prev" type="button" data-bs-target="#heroSlider" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroSlider" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
</div>
</section>
<?php endif; ?>

<!-- Features -->
<section style="padding:60px 0;background:white">
<div class="container">
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:30px">
    <div style="text-align:center;padding:30px;border-radius:16px;background:var(--bg-light);transition:0.3s" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
        <i class="bi bi-truck" style="font-size:48px;color:var(--primary);margin-bottom:16px"></i>
        <h3 style="font-size:20px;font-weight:700;margin-bottom:8px">شحن مجاني</h3>
        <p style="color:var(--text-light);margin:0">للطلبات فوق 200₪</p>
    </div>
    <div style="text-align:center;padding:30px;border-radius:16px;background:var(--bg-light);transition:0.3s" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
        <i class="bi bi-shield-check" style="font-size:48px;color:var(--success);margin-bottom:16px"></i>
        <h3 style="font-size:20px;font-weight:700;margin-bottom:8px">ضمان الجودة</h3>
        <p style="color:var(--text-light);margin:0">استرجاع مجاني 14 يوم</p>
    </div>
    <div style="text-align:center;padding:30px;border-radius:16px;background:var(--bg-light);transition:0.3s" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
        <i class="bi bi-headset" style="font-size:48px;color:var(--info);margin-bottom:16px"></i>
        <h3 style="font-size:20px;font-weight:700;margin-bottom:8px">دعم 24/7</h3>
        <p style="color:var(--text-light);margin:0">فريق جاهز لمساعدتك</p>
    </div>
    <div style="text-align:center;padding:30px;border-radius:16px;background:var(--bg-light);transition:0.3s" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
        <i class="bi bi-box" style="font-size:48px;color:var(--secondary);margin-bottom:16px"></i>
        <h3 style="font-size:20px;font-weight:700;margin-bottom:8px">عرض 3D</h3>
        <p style="color:var(--text-light);margin:0">شاهد المنتجات بتقنية 3D</p>
    </div>
</div>
</div>
</section>

<!-- Latest Products -->
<?php if (!empty($latest_products)): ?>
<section style="padding:80px 0;background:var(--bg-light)">
<div class="container">
<div style="text-align:center;margin-bottom:50px">
    <h2 style="font-size:40px;font-weight:800;margin-bottom:12px">أحدث المنتجات</h2>
    <p style="font-size:18px;color:var(--text-light)">اكتشف أحدث إضافاتنا</p>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:24px">
    <?php foreach ($latest_products as $product): ?>
    <div style="background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);transition:0.3s" onmouseover="this.style.transform='translateY(-8px)';this.style.boxShadow='0 12px 24px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
        <div style="position:relative;height:220px;overflow:hidden;background:#f8f9fa">
            <a href="/product.php?id=<?php echo $product['id']; ?>">
                <img src="<?php echo UPLOAD_URL . '/products/' . $product['image_path']; ?>" style="width:100%;height:100%;object-fit:cover;transition:0.3s" loading="lazy">
            </a>
            <?php if ($product['discount_price']): ?>
            <span style="position:absolute;top:12px;right:12px;background:var(--danger);color:white;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:700">
                -<?php echo round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>%
            </span>
            <?php endif; ?>
            <?php if ($product['enable_3d_view']): ?>
            <span style="position:absolute;top:12px;left:12px;background:var(--secondary);color:white;padding:6px 12px;border-radius:20px;font-size:11px;font-weight:700">
                <i class="bi bi-box"></i> 3D
            </span>
            <?php endif; ?>
        </div>
        <div style="padding:16px">
            <a href="/product.php?id=<?php echo $product['id']; ?>" style="display:block;font-size:15px;font-weight:600;color:var(--dark);margin-bottom:8px;height:40px;overflow:hidden;text-decoration:none"><?php echo escape($product['name_ar']); ?></a>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:12px">
                <span style="font-size:20px;font-weight:700;color:var(--primary)"><?php echo formatPrice($product['discount_price'] ?: $product['price']); ?></span>
                <button onclick="addToCart(<?php echo $product['id']; ?>)" style="padding:8px 16px;background:var(--primary);color:white;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;transition:0.3s" onmouseover="this.style.background='var(--secondary)'" onmouseout="this.style.background='var(--primary)'">
                    أضف
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div style="text-align:center;margin-top:50px">
    <a href="/products.php" style="display:inline-block;padding:14px 40px;background:var(--primary);color:white;border-radius:50px;font-size:16px;font-weight:600;text-decoration:none;transition:0.3s" onmouseover="this.style.background='var(--secondary)'" onmouseout="this.style.background='var(--primary)'">
        عرض جميع المنتجات <i class="bi bi-arrow-left"></i>
    </a>
</div>
</div>
</section>
<?php endif; ?>

<!-- Featured Products -->
<?php if (!empty($featured_products)): ?>
<section style="padding:80px 0;background:white">
<div class="container">
<div style="text-align:center;margin-bottom:50px">
    <h2 style="font-size:40px;font-weight:800;margin-bottom:12px">منتجات مميزة</h2>
    <p style="font-size:18px;color:var(--text-light)">مختارة بعناية لك</p>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:24px">
    <?php foreach ($featured_products as $product): ?>
    <div style="background:white;border:2px solid var(--border);border-radius:16px;overflow:hidden;transition:0.3s" onmouseover="this.style.borderColor='var(--primary)';this.style.transform='translateY(-5px)'" onmouseout="this.style.borderColor='var(--border)';this.style.transform='translateY(0)'">
        <div style="position:relative;height:220px;overflow:hidden;background:#f8f9fa">
            <a href="/product.php?id=<?php echo $product['id']; ?>">
                <img src="<?php echo UPLOAD_URL . '/products/' . $product['image_path']; ?>" style="width:100%;height:100%;object-fit:cover" loading="lazy">
            </a>
            <span style="position:absolute;top:12px;right:12px;background:var(--warning);color:white;padding:6px 12px;border-radius:20px;font-size:11px;font-weight:700">
                <i class="bi bi-star-fill"></i> مميز
            </span>
        </div>
        <div style="padding:16px">
            <a href="/product.php?id=<?php echo $product['id']; ?>" style="display:block;font-size:15px;font-weight:600;color:var(--dark);margin-bottom:8px;height:40px;overflow:hidden;text-decoration:none"><?php echo escape($product['name_ar']); ?></a>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:12px">
                <span style="font-size:20px;font-weight:700;color:var(--primary)"><?php echo formatPrice($product['discount_price'] ?: $product['price']); ?></span>
                <button onclick="addToCart(<?php echo $product['id']; ?>)" style="padding:8px 16px;background:var(--primary);color:white;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600">
                    أضف
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
</div>
</section>
<?php endif; ?>

<script>
function addToCart(id) {
    fetch('/api/cart.php?action=add&product_id=' + id + '&quantity=1', { method: 'POST' })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('تم إضافة المنتج للسلة');
            location.reload();
        } else {
            alert('حدث خطأ');
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>