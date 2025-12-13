<?php
require_once 'includes/store-init.php';

$page_title = getSetting('site_name_' . $lang) ?? '3D Store';
$featured_products = $db->fetchAll(
    "SELECT * FROM products WHERE status = 'active' AND is_featured = 1 ORDER BY created_at DESC LIMIT 8"
);

$latest_products = $db->fetchAll(
    "SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT 12"
);

$categories = $db->fetchAll(
    "SELECT * FROM categories WHERE status = 'active' ORDER BY name_ar LIMIT 6"
);

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-text">
                    <span class="hero-badge"><?php echo t('new_arrival'); ?></span>
                    <h1 class="hero-title"><?php echo t('hero_title'); ?></h1>
                    <p class="hero-desc"><?php echo t('hero_desc'); ?></p>
                    <div class="hero-buttons">
                        <a href="/products.php" class="btn btn-primary">
                            <i class="bi bi-shop"></i> <?php echo t('shop_now'); ?>
                        </a>
                        <a href="#categories" class="btn btn-outline">
                            <i class="bi bi-grid"></i> <?php echo t('browse_categories'); ?>
                        </a>
                    </div>
                    <div class="hero-stats">
                        <div class="stat-item">
                            <h3><?php echo $db->fetch("SELECT COUNT(*) as c FROM products WHERE status='active'")['c']; ?>+</h3>
                            <p><?php echo t('products'); ?></p>
                        </div>
                        <div class="stat-item">
                            <h3><?php echo $db->fetch("SELECT COUNT(*) as c FROM orders")['c']; ?>+</h3>
                            <p><?php echo t('orders'); ?></p>
                        </div>
                        <div class="stat-item">
                            <h3>4.9</h3>
                            <p><?php echo t('rating'); ?> <i class="bi bi-star-fill" style="color: #fbbf24;"></i></p>
                        </div>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="hero-3d-container">
                        <model-viewer
                            src="/uploads/models/sample.glb"
                            alt="3D Product"
                            auto-rotate
                            camera-controls
                            shadow-intensity="1"
                            style="width: 100%; height: 100%; --poster-color: transparent;"
                        ></model-viewer>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section" id="categories">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo t('shop_by_category'); ?></h2>
            <p class="section-desc"><?php echo t('category_desc'); ?></p>
        </div>
        
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
            <a href="/products.php?category=<?php echo $category['id']; ?>" class="category-card">
                <div class="category-image">
                    <?php if ($category['image_path']): ?>
                    <img src="<?php echo UPLOAD_URL . '/categories/' . $category['image_path']; ?>" alt="<?php echo escape($category['name_' . $lang]); ?>">
                    <?php else: ?>
                    <div class="category-placeholder"><i class="bi bi-image"></i></div>
                    <?php endif; ?>
                </div>
                <div class="category-info">
                    <h3><?php echo escape($category['name_' . $lang]); ?></h3>
                    <span><?php echo $db->fetch("SELECT COUNT(*) as c FROM products WHERE category_id = ?", [$category['id']])['c']; ?> <?php echo t('products'); ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<?php if (!empty($featured_products)): ?>
<section class="products-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo t('featured_products'); ?></h2>
            <a href="/products.php" class="btn btn-outline-sm"><?php echo t('view_all'); ?> <i class="bi bi-arrow-left"></i></a>
        </div>
        
        <div class="products-grid">
            <?php foreach ($featured_products as $product): ?>
            <?php include 'includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Latest Products -->
<section class="products-section" style="background: rgba(255,255,255,0.02);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo t('latest_products'); ?></h2>
            <a href="/products.php" class="btn btn-outline-sm"><?php echo t('view_all'); ?> <i class="bi bi-arrow-left"></i></a>
        </div>
        
        <div class="products-grid">
            <?php foreach ($latest_products as $product): ?>
            <?php include 'includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="bi bi-truck"></i></div>
                <h3><?php echo t('free_shipping'); ?></h3>
                <p><?php echo t('free_shipping_desc'); ?></p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                <h3><?php echo t('secure_payment'); ?></h3>
                <p><?php echo t('secure_payment_desc'); ?></p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="bi bi-arrow-repeat"></i></div>
                <h3><?php echo t('easy_return'); ?></h3>
                <p><?php echo t('easy_return_desc'); ?></p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="bi bi-headset"></i></div>
                <h3><?php echo t('support_24_7'); ?></h3>
                <p><?php echo t('support_desc'); ?></p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>