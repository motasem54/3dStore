<?php
/**
 * Helper Functions
 * All utility functions in one place
 */

require_once __DIR__ . '/db.php';

/**
 * Get setting value from database
 */
function getSetting($key, $default = '') {
    static $settings = null;
    
    if ($settings === null) {
        $db = Database::getInstance();
        $rows = $db->fetchAll("SELECT setting_key, setting_value FROM site_settings");
        
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    
    return $settings[$key] ?? $default;
}

/**
 * Update setting in database
 */
function updateSetting($key, $value) {
    $db = Database::getInstance();
    
    $db->query(
        "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()",
        [$key, $value, $value]
    );
    
    return true;
}

/**
 * Get cart items count
 */
function getCartItemsCount() {
    if (isset($_SESSION['user_id'])) {
        $db = Database::getInstance();
        $result = $db->fetchOne(
            "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?",
            [$_SESSION['user_id']]
        );
        return (int)($result['total'] ?? 0);
    } else {
        return isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
    }
}

/**
 * Format price with currency symbol
 */
function formatPrice($price, $currency = null) {
    if ($currency === null) {
        $currency = getSetting('currency', 'ILS');
    }
    
    $symbol = getSetting('currency_symbol', '₪');
    $position = getSetting('currency_position', 'after');
    $formatted = number_format($price, 2);
    
    if ($position === 'before') {
        return $symbol . ' ' . $formatted;
    } else {
        return $formatted . ' ' . $symbol;
    }
}

/**
 * Get currency symbol
 */
function getCurrencySymbol() {
    return getSetting('currency_symbol', '₪');
}

/**
 * Get discount percentage
 */
function getDiscountPercentage($original_price, $discount_price) {
    if ($original_price <= 0) return 0;
    return round((($original_price - $discount_price) / $original_price) * 100);
}

/**
 * Translation helper
 */
function t($key) {
    static $translations = null;
    
    if ($translations === null) {
        $lang = LANG;
        $file = __DIR__ . "/../lang/{$lang}.php";
        
        if (file_exists($file)) {
            $translations = require $file;
        } else {
            $translations = [];
        }
    }
    
    return $translations[$key] ?? $key;
}

/**
 * Truncate text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Time ago helper
 */
function timeAgo($timestamp) {
    $diff = time() - strtotime($timestamp);
    
    if ($diff < 60) {
        return $diff . ' seconds ago';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return date('M d, Y', strtotime($timestamp));
    }
}

/**
 * Generate slug from text
 */
function generateSlug($text) {
    // Convert to lowercase
    $text = strtolower($text);
    
    // Replace spaces with hyphens
    $text = str_replace(' ', '-', $text);
    
    // Remove special characters
    $text = preg_replace('/[^a-z0-9-]/', '', $text);
    
    // Remove multiple hyphens
    $text = preg_replace('/-+/', '-', $text);
    
    return trim($text, '-');
}

/**
 * Redirect helper
 */
function redirect($url, $status_code = 302) {
    header("Location: {$url}", true, $status_code);
    exit;
}

/**
 * Flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function getFlash($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

/**
 * Check if product is in wishlist
 */
function isInWishlist($product_id) {
    if (!isset($_SESSION['user_id'])) return false;
    
    $db = Database::getInstance();
    $result = $db->fetchOne(
        "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?",
        [$_SESSION['user_id'], $product_id]
    );
    
    return !empty($result);
}

/**
 * Escape HTML output
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// ============================================
// IMAGE PROCESSING FUNCTIONS
// ============================================

/**
 * Upload and process image
 */
function uploadImage($file, $destination = 'products', $max_width = 1200, $max_height = 0, $apply_watermark = true) {
    require_once __DIR__ . '/security.php';
    $validation = validateFileUpload($file, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    
    if (!$validation['valid']) {
        return ['success' => false, 'message' => $validation['message']];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid() . '_' . time() . '.' . $ext;
    
    $upload_dir = UPLOAD_PATH . '/' . $destination . '/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filepath = $upload_dir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => 'Failed to upload file'];
    }
    
    if ($max_width > 0 || $max_height > 0) {
        resizeImage($filepath, $max_width, $max_height);
    }
    
    if ($apply_watermark && getSetting('enable_watermark') == '1') {
        applyWatermark($filepath);
    }
    
    return [
        'success' => true,
        'filename' => $filename,
        'path' => $filepath,
        'message' => 'Image uploaded successfully'
    ];
}

/**
 * Resize image
 */
function resizeImage($filepath, $max_width = 1200, $max_height = 0, $quality = 85) {
    if (!file_exists($filepath)) return false;
    
    $info = getimagesize($filepath);
    if (!$info) return false;
    
    list($width, $height, $type) = $info;
    
    if ($max_height == 0) $max_height = $height;
    
    $ratio = min($max_width / $width, $max_height / $height);
    if ($ratio >= 1) return true;
    
    $new_width = floor($width * $ratio);
    $new_height = floor($height * $ratio);
    
    $source = createImageResource($filepath, $type);
    if (!$source) return false;
    
    $destination = imagecreatetruecolor($new_width, $new_height);
    
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefilledrectangle($destination, 0, 0, $new_width, $new_height, $transparent);
    }
    
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    saveImageResource($destination, $filepath, $type, $quality);
    
    imagedestroy($source);
    imagedestroy($destination);
    
    return true;
}

/**
 * Apply watermark
 */
function applyWatermark($image_path) {
    $watermark_file = getSetting('watermark_image');
    if (empty($watermark_file)) return false;
    
    $watermark_path = UPLOAD_PATH . '/settings/' . $watermark_file;
    if (!file_exists($watermark_path) || !file_exists($image_path)) return false;
    
    $position = getSetting('watermark_position', 'bottom-right');
    $opacity = (int)getSetting('watermark_opacity', 50);
    
    $image_info = getimagesize($image_path);
    $watermark_info = getimagesize($watermark_path);
    
    if (!$image_info || !$watermark_info) return false;
    
    $image = createImageResource($image_path, $image_info[2]);
    $watermark = createImageResource($watermark_path, $watermark_info[2]);
    
    if (!$image || !$watermark) return false;
    
    $image_width = imagesx($image);
    $image_height = imagesy($image);
    $watermark_width = imagesx($watermark);
    $watermark_height = imagesy($watermark);
    
    $max_wm_width = $image_width * 0.25;
    $max_wm_height = $image_height * 0.25;
    
    if ($watermark_width > $max_wm_width || $watermark_height > $max_wm_height) {
        $ratio = min($max_wm_width / $watermark_width, $max_wm_height / $watermark_height);
        $new_wm_width = floor($watermark_width * $ratio);
        $new_wm_height = floor($watermark_height * $ratio);
        
        $scaled_watermark = imagecreatetruecolor($new_wm_width, $new_wm_height);
        imagealphablending($scaled_watermark, false);
        imagesavealpha($scaled_watermark, true);
        
        imagecopyresampled($scaled_watermark, $watermark, 0, 0, 0, 0, 
                          $new_wm_width, $new_wm_height, $watermark_width, $watermark_height);
        
        imagedestroy($watermark);
        $watermark = $scaled_watermark;
        $watermark_width = $new_wm_width;
        $watermark_height = $new_wm_height;
    }
    
    list($x, $y) = calculateWatermarkPosition(
        $image_width, $image_height,
        $watermark_width, $watermark_height,
        $position
    );
    
    imagecopymerge($image, $watermark, $x, $y, 0, 0, 
                   $watermark_width, $watermark_height, $opacity);
    
    saveImageResource($image, $image_path, $image_info[2]);
    
    imagedestroy($image);
    imagedestroy($watermark);
    
    return true;
}

function createImageResource($filepath, $type) {
    switch ($type) {
        case IMAGETYPE_JPEG: return imagecreatefromjpeg($filepath);
        case IMAGETYPE_PNG: return imagecreatefrompng($filepath);
        case IMAGETYPE_GIF: return imagecreatefromgif($filepath);
        case IMAGETYPE_WEBP: return imagecreatefromwebp($filepath);
        default: return false;
    }
}

function saveImageResource($resource, $filepath, $type, $quality = 85) {
    switch ($type) {
        case IMAGETYPE_JPEG: return imagejpeg($resource, $filepath, $quality);
        case IMAGETYPE_PNG: return imagepng($resource, $filepath, 9);
        case IMAGETYPE_GIF: return imagegif($resource, $filepath);
        case IMAGETYPE_WEBP: return imagewebp($resource, $filepath, $quality);
        default: return false;
    }
}

function calculateWatermarkPosition($image_width, $image_height, $wm_width, $wm_height, $position) {
    $padding = 10;
    
    switch ($position) {
        case 'top-left': return [$padding, $padding];
        case 'top-center': return [($image_width - $wm_width) / 2, $padding];
        case 'top-right': return [$image_width - $wm_width - $padding, $padding];
        case 'center-left': return [$padding, ($image_height - $wm_height) / 2];
        case 'center': return [($image_width - $wm_width) / 2, ($image_height - $wm_height) / 2];
        case 'center-right': return [$image_width - $wm_width - $padding, ($image_height - $wm_height) / 2];
        case 'bottom-left': return [$padding, $image_height - $wm_height - $padding];
        case 'bottom-center': return [($image_width - $wm_width) / 2, $image_height - $wm_height - $padding];
        case 'bottom-right':
        default: return [$image_width - $wm_width - $padding, $image_height - $wm_height - $padding];
    }
}

function deleteFile($filepath) {
    if (file_exists($filepath) && is_file($filepath)) {
        return unlink($filepath);
    }
    return false;
}

function humanFileSize($bytes, $decimals = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $decimals) . ' ' . $units[$i];
}
