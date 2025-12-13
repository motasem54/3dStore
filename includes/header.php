<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title . ' - ' . ($settings['site_name_' . $lang] ?? 'متجر'); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>

<!-- Mobile Menu Overlay -->
<div class="mobile-menu-overlay" id="mobileMenuOverlay" onclick="closeMobileMenu()"></div>

<!-- Premium Mobile Menu -->
<div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu-header">
        <div class="mobile-logo">
            <div class="logo-icon"><i class="bi bi-shop"></i></div>
            <span><?php echo $settings['site_name_' . $lang] ?? 'متجري'; ?></span>
        </div>
        <button class="mobile-close" onclick="closeMobileMenu()"><i class="bi bi-x-lg"></i></button>
    </div>
    
    <nav class="mobile-nav">
        <a href="/" class="mobile-nav-link"><i class="bi bi-house-door"></i> <span>الرئيسية</span></a>
        <a href="/products.php" class="mobile-nav-link"><i class="bi bi-grid"></i> <span>المنتجات</span></a>
        <a href="/products.php?type=3d" class="mobile-nav-link"><i class="bi bi-box"></i> <span>منتجات 3D</span></a>
        <a href="/products.php?filter=bestseller" class="mobile-nav-link"><i class="bi bi-fire"></i> <span>الأكثر مبيعاً</span></a>
        <a href="/products.php?filter=sale" class="mobile-nav-link"><i class="bi bi-tag"></i> <span>عروض خاصة</span></a>
        <a href="/track.php" class="mobile-nav-link"><i class="bi bi-box-seam"></i> <span>تتبع طلبك</span></a>
        <?php if (isLoggedIn()): ?>
        <a href="/account/dashboard.php" class="mobile-nav-link"><i class="bi bi-person-circle"></i> <span>حسابي</span></a>
        <a href="/account/logout.php" class="mobile-nav-link" style="color:var(--danger)"><i class="bi bi-box-arrow-right"></i> <span>تسجيل الخروج</span></a>
        <?php else: ?>
        <a href="/account/login.php" class="mobile-nav-link"><i class="bi bi-box-arrow-in-left"></i> <span>تسجيل الدخول</span></a>
        <?php endif; ?>
    </nav>
    
    <div class="mobile-menu-footer">
        <div class="mobile-contact">
            <h4>تواصل معنا</h4>
            <a href="tel:+970599123456" class="contact-link"><i class="bi bi-telephone"></i> <span>+970 599 123 456</span></a>
            <a href="mailto:info@store.ps" class="contact-link"><i class="bi bi-envelope"></i> <span>info@store.ps</span></a>
            <a href="#" class="contact-link"><i class="bi bi-geo-alt"></i> <span>رام الله، فلسطين</span></a>
        </div>
        <div class="mobile-social">
            <a href="#" class="social-btn"><i class="bi bi-facebook"></i></a>
            <a href="#" class="social-btn"><i class="bi bi-instagram"></i></a>
            <a href="#" class="social-btn"><i class="bi bi-whatsapp"></i></a>
            <a href="#" class="social-btn"><i class="bi bi-youtube"></i></a>
        </div>
    </div>
</div>

<!-- Top Bar -->
<div class="top-bar">
    <div class="container">
        <div class="top-bar-content">
            <div class="top-bar-left"><i class="bi bi-truck"></i><span>شحن مجاني على جميع الطلبات فوق 200₪</span></div>
            <div class="top-bar-right">
                <div class="lang-switcher">
                    <a href="?lang=ar" class="<?php echo $lang === 'ar' ? 'active' : ''; ?>">AR</a>
                    <span>|</span>
                    <a href="?lang=en" class="<?php echo $lang === 'en' ? 'active' : ''; ?>">EN</a>
                </div>
                <div class="currency-switcher">
                    <a href="?currency=ILS" class="<?php echo $currency === 'ILS' ? 'active' : ''; ?>">₪</a>
                    <span>|</span>
                    <a href="?currency=USD" class="<?php echo $currency === 'USD' ? 'active' : ''; ?>">$</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Header -->
<header class="main-header">
    <div class="container">
        <div class="header-content">
            <button class="header-icon mobile-toggle" onclick="openMobileMenu()"><i class="bi bi-list"></i></button>
            
            <a href="/" class="logo">
                <div class="logo-icon"><i class="bi bi-shop"></i></div>
                <span><?php echo $settings['site_name_' . $lang] ?? 'متجري'; ?></span>
            </a>
            
            <nav class="main-nav">
                <a href="/" class="nav-link">الرئيسية</a>
                <a href="/products.php" class="nav-link">المنتجات</a>
                <a href="/products.php?type=3d" class="nav-link">منتجات 3D</a>
                <a href="/products.php?filter=bestseller" class="nav-link">الأكثر مبيعاً</a>
                <a href="/track.php" class="nav-link">تتبع طلبك</a>
            </nav>
            
            <div class="header-actions">
                <button class="header-icon"><i class="bi bi-search"></i></button>
                <a href="/wishlist.php" class="header-icon"><i class="bi bi-heart"></i><?php if(isset($_SESSION['wishlist']) && count($_SESSION['wishlist']) > 0): ?><span class="badge"><?php echo count($_SESSION['wishlist']); ?></span><?php endif; ?></a>
                <a href="/compare.php" class="header-icon"><i class="bi bi-arrow-left-right"></i><?php if(isset($_SESSION['compare']) && count($_SESSION['compare']) > 0): ?><span class="badge"><?php echo count($_SESSION['compare']); ?></span><?php endif; ?></a>
                <a href="/cart.php" class="header-icon"><i class="bi bi-cart3"></i><?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?><span class="badge"><?php echo array_sum($_SESSION['cart']); ?></span><?php endif; ?></a>
                <?php if (isLoggedIn()): ?>
                <a href="/account/dashboard.php" class="header-icon"><i class="bi bi-person-circle"></i></a>
                <?php else: ?>
                <a href="/account/login.php" class="header-icon"><i class="bi bi-box-arrow-in-right"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<script>
function openMobileMenu() {
    document.getElementById('mobileMenu').classList.add('active');
    document.getElementById('mobileMenuOverlay').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeMobileMenu() {
    document.getElementById('mobileMenu').classList.remove('active');
    document.getElementById('mobileMenuOverlay').classList.remove('active');
    document.body.style.overflow = '';
}

function addToCart(productId) {
    fetch('/api/cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'add', product_id: productId, quantity: 1})
    }).then(r => r.json()).then(data => {
        if (data.success) {
            alert('تم الإضافة للسلة!');
            location.reload();
        }
    });
}

function addToWishlist(productId) {
    fetch('/api/wishlist.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'add', product_id: productId})
    }).then(r => r.json()).then(data => {
        if (data.success) alert('تم الإضافة للمفضلة!');
    });
}

function addToCompare(productId) {
    fetch('/api/compare.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'add', product_id: productId})
    }).then(r => r.json()).then(data => {
        if (data.success) alert('تم الإضافة للمقارنة!');
    });
}

function quickView(productId) {
    window.location.href = '/product.php?id=' + productId;
}
</script>