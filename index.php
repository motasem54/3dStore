<?php
require_once 'includes/store-init.php';
$page_title = $settings['site_name_' . $lang] ?? 'متجر إلكتروني';
include 'includes/header.php';
?>

<!-- Hero Slider -->
<section class="hero-slider">
    <div class="swiper heroSwiper">
        <div class="swiper-wrapper">
            <?php
            $slides = $db->fetchAll("SELECT * FROM sliders WHERE status = 'active' ORDER BY sort_order LIMIT 5");
            if (empty($slides)) {
                $slides = [
                    ['title_ar' => 'عروض الصيف الحصرية', 'description_ar' => 'خصم يصل إلى 50% على جميع المنتجات', 'button_text_ar' => 'تسوق الآن', 'button_link' => '/products.php', 'background' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
                    ['title_ar' => 'مجموعة جديدة 2025', 'description_ar' => 'اكتشف أحدث المنتجات لهذا الموسم', 'button_text_ar' => 'استكشف الآن', 'button_link' => '/products.php', 'background' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'],
                    ['title_ar' => 'شحن مجاني', 'description_ar' => 'على جميع الطلبات فوق 200 شيكل', 'button_text_ar' => 'اطلب الآن', 'button_link' => '/products.php', 'background' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)']
                ];
            }
            foreach ($slides as $slide):
            ?>
            <div class="swiper-slide" style="background: <?php echo $slide['background'] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'; ?>;">
                <div class="slide-content">
                    <h1 class="slide-title"><?php echo escape($slide['title_' . $lang]); ?></h1>
                    <p class="slide-desc"><?php echo escape($slide['description_' . $lang]); ?></p>
                    <a href="<?php echo $slide['button_link'] ?? '/products.php'; ?>" class="slide-btn"><?php echo escape($slide['button_text_' . $lang] ?? 'تسوق الآن'); ?> <i class="bi bi-arrow-left"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-pagination"></div>
    </div>
</section>

<!-- Flash Deals Slider -->
<section class="item-slider">
    <div class="container">
        <h2 class="section-title">عروض سريعة <i class="bi bi-lightning-fill" style="color: #fbbf24;"></i></h2>
        <div class="swiper itemSwiper">
            <div class="swiper-wrapper">
                <?php
                $flash_deals = $db->fetchAll("SELECT * FROM products WHERE sale_price_ils > 0 AND status = 'active' ORDER BY RAND() LIMIT 8");
                foreach ($flash_deals as $product):
                    $price = $currency === 'ILS' ? $product['price_ils'] : $product['price_usd'];
                    $sale_price = $currency === 'ILS' ? $product['sale_price_ils'] : $product['sale_price_usd'];
                    $discount = $sale_price > 0 ? round((1 - $sale_price / $price) * 100) : 0;
                ?>
                <div class="swiper-slide">
                    <div class="item-card">
                        <div class="item-image" style="background: linear-gradient(135deg, #fa709a, #fee140);">
                            <?php if ($product['image_path']): ?>
                            <img src="<?php echo UPLOAD_URL . '/products/' . $product['image_path']; ?>" style="width:100%;height:100%;object-fit:cover">
                            <?php endif; ?>
                            <?php if ($discount > 0): ?><span class="item-badge">-<?php echo $discount; ?>%</span><?php endif; ?>
                            <div class="item-actions">
                                <button class="action-btn" onclick="addToWishlist(<?php echo $product['id']; ?>)"><i class="bi bi-heart"></i></button>
                                <button class="action-btn" onclick="quickView(<?php echo $product['id']; ?>)"><i class="bi bi-eye"></i></button>
                                <button class="action-btn" onclick="addToCompare(<?php echo $product['id']; ?>)"><i class="bi bi-arrow-left-right"></i></button>
                            </div>
                        </div>
                        <div class="item-info">
                            <div class="item-title"><?php echo escape($product['name_' . $lang]); ?></div>
                            <div class="item-meta">
                                <span style="color: #fbbf24;"><i class="bi bi-star-fill"></i> 4.8</span>
                                <span style="color: var(--success);"><i class="bi bi-check-circle-fill"></i> متوفر</span>
                            </div>
                            <div class="item-footer">
                                <div class="price-wrapper">
                                    <span class="item-price"><?php echo formatPrice($sale_price > 0 ? $sale_price : $price); ?></span>
                                    <?php if ($sale_price > 0): ?><span class="old-price"><?php echo formatPrice($price); ?></span><?php endif; ?>
                                </div>
                                <button class="btn-cart" onclick="addToCart(<?php echo $product['id']; ?>)"><i class="bi bi-cart-plus"></i> إضافة</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Categories -->
<section class="categories-section">
    <div class="container">
        <h2 class="section-title">تسوق حسب الفئة</h2>
        <div class="categories-grid">
            <?php
            $categories = $db->fetchAll("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active' WHERE c.status = 'active' GROUP BY c.id ORDER BY c.sort_order LIMIT 6");
            $cat_icons = ['bi-phone', 'bi-watch', 'bi-bag', 'bi-controller', 'bi-house-door', 'bi-gift'];
            foreach ($categories as $i => $cat):
            ?>
            <a href="/products.php?category=<?php echo $cat['id']; ?>" class="cat-card">
                <div class="cat-icon"><i class="bi <?php echo $cat_icons[$i % 6]; ?>"></i></div>
                <div class="cat-name"><?php echo escape($cat['name_' . $lang]); ?></div>
                <div class="cat-count"><?php echo $cat['product_count']; ?> منتج</div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 3D Products Section -->
<section class="products-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 80px 0;">
    <div class="container">
        <h2 class="section-title" style="color: white; margin-bottom: 16px;">منتجات ثلاثية الأبعاد <i class="bi bi-box" style="color: white;"></i></h2>
        <p style="text-align: center; color: rgba(255,255,255,0.9); margin-bottom: 40px; font-size: 18px;">شاهد المنتجات بتقنية 3D التفاعلية</p>
        <div class="products-grid">
            <?php
            $products_3d = $db->fetchAll("SELECT * FROM products WHERE type = '3d' AND status = 'active' AND enable_3d_view = 1 ORDER BY created_at DESC LIMIT 4");
            foreach ($products_3d as $product):
                $price = $currency === 'ILS' ? $product['price_ils'] : $product['price_usd'];
            ?>
            <div class="product-card" style="background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);">
                <div class="product-image" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                    <?php if ($product['image_path']): ?>
                    <img src="<?php echo UPLOAD_URL . '/products/' . $product['image_path']; ?>" style="width:100%;height:100%;object-fit:cover">
                    <?php endif; ?>
                    <span class="product-badge" style="background: rgba(255,255,255,0.95); color: var(--primary);"><i class="bi bi-box"></i> 3D</span>
                    <div class="product-actions">
                        <button class="action-btn" onclick="addToWishlist(<?php echo $product['id']; ?>)"><i class="bi bi-heart"></i></button>
                        <button class="action-btn" onclick="quickView(<?php echo $product['id']; ?>)"><i class="bi bi-eye"></i></button>
                        <button class="action-btn" onclick="addToCompare(<?php echo $product['id']; ?>)"><i class="bi bi-arrow-left-right"></i></button>
                    </div>
                </div>
                <div class="product-info">
                    <a href="/product.php?id=<?php echo $product['id']; ?>" class="product-title"><?php echo escape($product['name_' . $lang]); ?></a>
                    <div class="product-rating">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i>
                    </div>
                    <div class="product-footer">
                        <div class="price-wrapper"><span class="product-price"><?php echo formatPrice($price); ?></span></div>
                        <button class="btn-add" onclick="addToCart(<?php echo $product['id']; ?>)"><i class="bi bi-cart-plus"></i></button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align: center; margin-top: 40px;">
            <a href="/products.php?type=3d" style="display: inline-block; padding: 14px 32px; background: white; color: var(--primary); border-radius: 50px; text-decoration: none; font-weight: 600; transition: 0.3s;">شاهد جميع منتجات 3D <i class="bi bi-arrow-left"></i></a>
        </div>
    </div>
</section>

<!-- Bestsellers -->
<section class="products-section">
    <div class="container">
        <h2 class="section-title">الأكثر مبيعاً <i class="bi bi-fire" style="color: #ef4444;"></i></h2>
        <div class="products-grid">
            <?php
            $bestsellers = $db->fetchAll("SELECT * FROM products WHERE status = 'active' ORDER BY views DESC LIMIT 4");
            foreach ($bestsellers as $product):
                $price = $currency === 'ILS' ? $product['price_ils'] : $product['price_usd'];
                $sale_price = $currency === 'ILS' ? $product['sale_price_ils'] : $product['sale_price_usd'];
                $discount = $sale_price > 0 ? round((1 - $sale_price / $price) * 100) : 0;
            ?>
            <div class="product-card">
                <div class="product-image" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                    <?php if ($product['image_path']): ?>
                    <img src="<?php echo UPLOAD_URL . '/products/' . $product['image_path']; ?>" style="width:100%;height:100%;object-fit:cover">
                    <?php endif; ?>
                    <?php if ($discount > 0): ?><span class="product-badge badge-sale">-<?php echo $discount; ?>%</span><?php endif; ?>
                    <div class="product-actions">
                        <button class="action-btn" onclick="addToWishlist(<?php echo $product['id']; ?>)"><i class="bi bi-heart"></i></button>
                        <button class="action-btn" onclick="quickView(<?php echo $product['id']; ?>)"><i class="bi bi-eye"></i></button>
                        <button class="action-btn" onclick="addToCompare(<?php echo $product['id']; ?>)"><i class="bi bi-arrow-left-right"></i></button>
                    </div>
                </div>
                <div class="product-info">
                    <a href="/product.php?id=<?php echo $product['id']; ?>" class="product-title"><?php echo escape($product['name_' . $lang]); ?></a>
                    <div class="product-rating">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i>
                    </div>
                    <div class="product-footer">
                        <div class="price-wrapper">
                            <span class="product-price"><?php echo formatPrice($sale_price > 0 ? $sale_price : $price); ?></span>
                            <?php if ($sale_price > 0): ?><span class="old-price"><?php echo formatPrice($price); ?></span><?php endif; ?>
                        </div>
                        <button class="btn-add" onclick="addToCart(<?php echo $product['id']; ?>)"><i class="bi bi-cart-plus"></i></button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features -->
<section class="features-section">
    <div class="container">
        <div class="features-grid">
            <div class="feature-card"><div class="feature-icon"><i class="bi bi-truck"></i></div><div class="feature-title">شحن مجاني</div><div class="feature-desc">على جميع الطلبات فوق 200₪</div></div>
            <div class="feature-card"><div class="feature-icon"><i class="bi bi-shield-check"></i></div><div class="feature-title">دفع آمن</div><div class="feature-desc">معاملات مشفرة 100%</div></div>
            <div class="feature-card"><div class="feature-icon"><i class="bi bi-arrow-repeat"></i></div><div class="feature-title">إرجاع مجاني</div><div class="feature-desc">استرجاع خلال 14 يوم</div></div>
            <div class="feature-card"><div class="feature-icon"><i class="bi bi-headset"></i></div><div class="feature-title">دعم 24/7</div><div class="feature-desc">فريق جاهز لمساعدتك</div></div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>