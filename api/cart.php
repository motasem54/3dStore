<?php
/**
 * Cart API - AJAX operations
 * Add, Update, Remove, Get cart items
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';

header('Content-Type: application/json');

// Rate limiting
checkRateLimit('cart_api', 60, 60); // 60 requests per minute

// CSRF check for POST/DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    verifyCsrfToken($_POST['csrf_token'] ?? '');
}

$db = Database::getInstance();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            addToCart();
            break;
        
        case 'update':
            updateCart();
            break;
        
        case 'remove':
            removeFromCart();
            break;
        
        case 'get':
            getCart();
            break;
        
        case 'clear':
            clearCart();
            break;
        
        case 'count':
            getCartCount();
            break;
        
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 400);
}

/**
 * Add product to cart
 */
function addToCart() {
    global $db;
    
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    if ($product_id <= 0) {
        throw new Exception('Invalid product ID');
    }
    
    if ($quantity <= 0 || $quantity > 99) {
        throw new Exception('Invalid quantity');
    }
    
    // Check if product exists and is active
    $product = $db->fetchOne(
        "SELECT id, name_" . LANG . " as name, price, stock, is_active FROM products WHERE id = ?",
        [$product_id]
    );
    
    if (!$product) {
        throw new Exception(t('product_not_found'));
    }
    
    if (!$product['is_active']) {
        throw new Exception(t('product_not_available'));
    }
    
    if ($product['stock'] < $quantity) {
        throw new Exception(t('insufficient_stock'));
    }
    
    // If user is logged in, save to database
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        // Check if already in cart
        $existing = $db->fetchOne(
            "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?",
            [$user_id, $product_id]
        );
        
        if ($existing) {
            // Update quantity
            $new_quantity = $existing['quantity'] + $quantity;
            
            if ($new_quantity > $product['stock']) {
                throw new Exception(t('insufficient_stock'));
            }
            
            $db->query(
                "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?",
                [$new_quantity, $existing['id']]
            );
        } else {
            // Insert new
            $db->insert('cart', [
                'user_id' => $user_id,
                'product_id' => $product_id,
                'quantity' => $quantity
            ]);
        }
    } else {
        // Save to session for guests
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
    }
    
    jsonResponse([
        'success' => true,
        'message' => t('added_to_cart'),
        'cart_count' => getCartItemsCount()
    ]);
}

/**
 * Update cart item quantity
 */
function updateCart() {
    global $db;
    
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);
    
    if ($product_id <= 0) {
        throw new Exception('Invalid product ID');
    }
    
    if ($quantity < 0 || $quantity > 99) {
        throw new Exception('Invalid quantity');
    }
    
    // If quantity is 0, remove item
    if ($quantity === 0) {
        removeFromCart();
        return;
    }
    
    // Check stock
    $product = $db->fetchOne(
        "SELECT stock FROM products WHERE id = ?",
        [$product_id]
    );
    
    if ($quantity > $product['stock']) {
        throw new Exception(t('insufficient_stock'));
    }
    
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        $db->query(
            "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?",
            [$quantity, $user_id, $product_id]
        );
    } else {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] = $quantity;
        }
    }
    
    jsonResponse([
        'success' => true,
        'message' => t('cart_updated'),
        'cart_count' => getCartItemsCount()
    ]);
}

/**
 * Remove item from cart
 */
function removeFromCart() {
    global $db;
    
    $product_id = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
    
    if ($product_id <= 0) {
        throw new Exception('Invalid product ID');
    }
    
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        $db->query(
            "DELETE FROM cart WHERE user_id = ? AND product_id = ?",
            [$user_id, $product_id]
        );
    } else {
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
    }
    
    jsonResponse([
        'success' => true,
        'message' => t('removed_from_cart'),
        'cart_count' => getCartItemsCount()
    ]);
}

/**
 * Get cart items
 */
function getCart() {
    global $db;
    
    $items = [];
    $total = 0;
    
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        $cart_items = $db->fetchAll(
            "SELECT c.*, 
                    p.name_" . LANG . " as product_name,
                    p.price,
                    p.discount_price,
                    p.image_path,
                    p.stock,
                    p.is_active
             FROM cart c
             JOIN products p ON c.product_id = p.id
             WHERE c.user_id = ?
             ORDER BY c.created_at DESC",
            [$user_id]
        );
        
        foreach ($cart_items as $item) {
            $price = $item['discount_price'] > 0 ? $item['discount_price'] : $item['price'];
            $subtotal = $price * $item['quantity'];
            $total += $subtotal;
            
            $items[] = [
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'price' => $price,
                'quantity' => $item['quantity'],
                'subtotal' => $subtotal,
                'image' => UPLOAD_URL . '/products/' . $item['image_path'],
                'stock' => $item['stock'],
                'is_active' => $item['is_active']
            ];
        }
    } else {
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            $product_ids = array_keys($_SESSION['cart']);
            $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
            
            $products = $db->fetchAll(
                "SELECT id,
                        name_" . LANG . " as product_name,
                        price,
                        discount_price,
                        image_path,
                        stock,
                        is_active
                 FROM products
                 WHERE id IN ($placeholders)",
                $product_ids
            );
            
            foreach ($products as $product) {
                $quantity = $_SESSION['cart'][$product['id']];
                $price = $product['discount_price'] > 0 ? $product['discount_price'] : $product['price'];
                $subtotal = $price * $quantity;
                $total += $subtotal;
                
                $items[] = [
                    'product_id' => $product['id'],
                    'product_name' => $product['product_name'],
                    'price' => $price,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                    'image' => UPLOAD_URL . '/products/' . $product['image_path'],
                    'stock' => $product['stock'],
                    'is_active' => $product['is_active']
                ];
            }
        }
    }
    
    jsonResponse([
        'success' => true,
        'items' => $items,
        'total' => $total,
        'count' => count($items)
    ]);
}

/**
 * Clear entire cart
 */
function clearCart() {
    global $db;
    
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $db->query("DELETE FROM cart WHERE user_id = ?", [$user_id]);
    } else {
        $_SESSION['cart'] = [];
    }
    
    jsonResponse([
        'success' => true,
        'message' => t('cart_cleared')
    ]);
}

/**
 * Get cart items count
 */
function getCartCount() {
    jsonResponse([
        'success' => true,
        'count' => getCartItemsCount()
    ]);
}

/**
 * JSON response helper
 */
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}
