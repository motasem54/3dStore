<?php
/**
 * 3D Store - Main Entry Point
 * Multi-language E-commerce with 3D Products
 */

// Start session
session_start();

// Load configuration
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/security.php';

// Get current language
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : DEFAULT_LANG;

// Load language file
require_once "lang/{$lang}.php";

// Simple routing
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Determine controller and action
$page = !empty($url[0]) ? $url[0] : 'home';

// Route to appropriate page
switch($page) {
    case 'home':
    case '':
        require_once 'store/index.php';
        break;
    
    case 'products':
        require_once 'store/products.php';
        break;
    
    case 'product':
        require_once 'store/product-details.php';
        break;
    
    case 'cart':
        require_once 'store/cart.php';
        break;
    
    case 'checkout':
        require_once 'store/checkout.php';
        break;
    
    case 'login':
        require_once 'store/login.php';
        break;
    
    case 'register':
        require_once 'store/register.php';
        break;
    
    case 'account':
        require_once 'store/account.php';
        break;
    
    case 'wishlist':
        require_once 'store/wishlist.php';
        break;
    
    case 'compare':
        require_once 'store/compare.php';
        break;
    
    case 'admin':
        header('Location: admin/login.php');
        exit;
        break;
    
    default:
        http_response_code(404);
        require_once 'store/404.php';
        break;
}
?>