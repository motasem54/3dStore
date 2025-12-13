<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title><?php echo escape($page_title ?? '3D Store'); ?></title>
    <meta name="description" content="<?php echo escape(getSetting('site_description_' . $lang) ?? 'Your 3D printing store'); ?>">
    
    <link rel="stylesheet" href="/assets/css/store.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.3.0/model-viewer.min.js"></script>
</head>
<body>
    
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <div class="top-bar-left">
                    <i class="bi bi-truck"></i>
                    <span><?php echo t('free_shipping_desc'); ?></span>
                </div>
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
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <i class="bi bi-list"></i>
                </button>
                
                <a href="/" class="logo">
                    <div class="logo-icon"><i class="bi bi-box-seam"></i></div>
                    <span class="logo-text">3D Store</span>
                </a>
                
                <nav class="main-nav" id="mainNav">
                    <a href="/" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>"><?php echo t('home'); ?></a>
                    <a href="/products.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : ''; ?>"><?php echo t('products'); ?></a>
                    <a href="/categories.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : ''; ?>"><?php echo t('categories'); ?></a>
                    <a href="/track.php" class="nav-link">تتبع طلبك</a>
                </nav>
                
                <div class="header-actions">
                    <button class="header-icon" id="searchToggle">
                        <i class="bi bi-search"></i>
                    </button>
                    
                    <a href="/wishlist.php" class="header-icon">
                        <i class="bi bi-heart"></i>
                        <span class="badge">0</span>
                    </a>
                    
                    <a href="/cart.php" class="header-icon cart-icon">
                        <i class="bi bi-cart3"></i>
                        <span class="badge"><?php echo getCartCount(); ?></span>
                    </a>
                    
                    <?php if (isset($_SESSION['customer_id'])): ?>
                    <a href="/account/" class="header-icon">
                        <i class="bi bi-person-circle"></i>
                    </a>
                    <?php else: ?>
                    <a href="/login.php" class="btn btn-primary-sm"><?php echo t('login'); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>
    
    <!-- Search Overlay -->
    <div class="search-overlay" id="searchOverlay">
        <button class="search-close" id="searchClose">
            <i class="bi bi-x-lg"></i>
        </button>
        <div class="search-container">
            <form action="/products.php" method="GET" class="search-form">
                <input type="text" name="search" placeholder="<?php echo t('search'); ?>" class="search-input" autofocus>
                <button type="submit" class="search-btn">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </div>
    
    <main class="main-content">