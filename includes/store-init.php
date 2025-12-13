<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../admin/includes/config.php';
require_once __DIR__ . '/../admin/includes/db.php';
require_once __DIR__ . '/../admin/includes/functions.php';

// Language
$lang = $_SESSION['lang'] ?? $_COOKIE['lang'] ?? 'ar';
if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
    setcookie('lang', $lang, time() + (86400 * 365), '/');
}

// Currency
$currency = $_SESSION['currency'] ?? $_COOKIE['currency'] ?? 'ILS';
if (isset($_GET['currency']) && in_array($_GET['currency'], ['ILS', 'USD'])) {
    $currency = $_GET['currency'];
    $_SESSION['currency'] = $currency;
    setcookie('currency', $currency, time() + (86400 * 365), '/');
}

// Translation function
function t($key) {
    global $lang;
    static $translations = null;
    
    if ($translations === null) {
        $translations = [
            'ar' => [
                'home' => 'الرئيسية',
                'products' => 'المنتجات',
                'categories' => 'التصنيفات',
                'cart' => 'السلة',
                'account' => 'حسابي',
                'login' => 'تسجيل الدخول',
                'register' => 'تسجيل جديد',
                'logout' => 'تسجيل الخروج',
                'search' => 'بحث...',
                'new_arrival' => 'وصل حديثاً',
                'hero_title' => 'استكشف عالم الطباعة ثلاثية الأبعاد',
                'hero_desc' => 'منتجات مبتكرة بتقنية 3D بجودة عالية وأسعار منافسة',
                'shop_now' => 'تسوق الآن',
                'browse_categories' => 'تصفح التصنيفات',
                'rating' => 'تقييم',
                'orders' => 'طلب',
                'shop_by_category' => 'تسوق حسب التصنيف',
                'category_desc' => 'اختر من بين تشكيلة واسعة من التصنيفات',
                'featured_products' => 'منتجات مميزة',
                'latest_products' => 'أحدث المنتجات',
                'view_all' => 'عرض الكل',
                'add_to_cart' => 'إضافة للسلة',
                'quick_view' => 'عرض سريع',
                'free_shipping' => 'شحن مجاني',
                'free_shipping_desc' => 'على جميع الطلبات فوق 200₪',
                'secure_payment' => 'دفع آمن',
                'secure_payment_desc' => 'معاملات مشفرة 100%',
                'easy_return' => 'إرجاع سهل',
                'easy_return_desc' => 'استرجاع خلال 14 يوم',
                'support_24_7' => 'دعم 24/7',
                'support_desc' => 'فريق جاهز لمساعدتك',
                'copyright' => 'جميع الحقوق محفوظة',
            ],
            'en' => [
                'home' => 'Home',
                'products' => 'Products',
                'categories' => 'Categories',
                'cart' => 'Cart',
                'account' => 'Account',
                'login' => 'Login',
                'register' => 'Register',
                'logout' => 'Logout',
                'search' => 'Search...',
                'new_arrival' => 'New Arrival',
                'hero_title' => 'Discover The World of 3D Printing',
                'hero_desc' => 'Innovative 3D products with high quality and competitive prices',
                'shop_now' => 'Shop Now',
                'browse_categories' => 'Browse Categories',
                'rating' => 'Rating',
                'orders' => 'Orders',
                'shop_by_category' => 'Shop By Category',
                'category_desc' => 'Choose from a wide range of categories',
                'featured_products' => 'Featured Products',
                'latest_products' => 'Latest Products',
                'view_all' => 'View All',
                'add_to_cart' => 'Add to Cart',
                'quick_view' => 'Quick View',
                'free_shipping' => 'Free Shipping',
                'free_shipping_desc' => 'On all orders above 200₪',
                'secure_payment' => 'Secure Payment',
                'secure_payment_desc' => '100% encrypted transactions',
                'easy_return' => 'Easy Return',
                'easy_return_desc' => 'Return within 14 days',
                'support_24_7' => '24/7 Support',
                'support_desc' => 'Team ready to help you',
                'copyright' => 'All rights reserved',
            ]
        ];
    }
    
    return $translations[$lang][$key] ?? $key;
}

// Format Price
function formatPrice($price) {
    global $currency;
    $symbol = $currency === 'ILS' ? '₪' : '$';
    return number_format($price, 2) . ' ' . $symbol;
}

// Get setting
function getSetting($key) {
    global $db;
    static $cache = [];
    
    if (!isset($cache[$key])) {
        $result = $db->fetch("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
        $cache[$key] = $result['setting_value'] ?? null;
    }
    
    return $cache[$key];
}

// Cart functions
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function getCartCount() {
    return array_sum(array_column($_SESSION['cart'], 'quantity'));
}

function getCartTotal() {
    global $db, $currency;
    $total = 0;
    
    foreach ($_SESSION['cart'] as $item) {
        $product = $db->fetch("SELECT * FROM products WHERE id = ?", [$item['product_id']]);
        if ($product) {
            $price = $currency === 'ILS' ? $product['price_ils'] : $product['price_usd'];
            $total += $price * $item['quantity'];
        }
    }
    
    return $total;
}