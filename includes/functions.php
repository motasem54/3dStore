<?php
/**
 * 3D Store - Helper Functions
 * Phase 1: Core functions for file handling, validation, and utilities
 */

// ========================================
// FILE UPLOAD FUNCTIONS
// ========================================

/**
 * Upload a file to the server
 * @param array $file The $_FILES array element
 * @param string $folder Target folder (products, settings, categories, etc.)
 * @param array $allowed_types Allowed MIME types
 * @param int $max_size Maximum file size in bytes (default 5MB)
 * @return string|false Filename on success, false on failure
 */
function uploadFile($file, $folder = 'products', $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], $max_size = 5242880) {
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return false;
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        return false;
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        return false;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . strtolower($extension);
    
    // Create upload directory if not exists
    $upload_path = UPLOAD_PATH . '/' . $folder;
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0755, true);
    }
    
    // Move uploaded file
    $destination = $upload_path . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Optimize image if it's an image file
        if (in_array($mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            optimizeImage($destination, $mime_type);
        }
        return $filename;
    }
    
    return false;
}

/**
 * Delete a file from the server
 * @param string $filename File name
 * @param string $folder Folder name
 * @return bool Success status
 */
function deleteFile($filename, $folder = 'products') {
    if (empty($filename)) return false;
    
    $file_path = UPLOAD_PATH . '/' . $folder . '/' . $filename;
    if (file_exists($file_path)) {
        return unlink($file_path);
    }
    return false;
}

/**
 * Optimize image file (resize and compress)
 * @param string $file_path Full path to the image
 * @param string $mime_type MIME type of the image
 * @param int $max_width Maximum width (default 1200px)
 * @param int $quality JPEG quality (default 85)
 * @return bool Success status
 */
function optimizeImage($file_path, $mime_type, $max_width = 1200, $quality = 85) {
    // Get image dimensions
    list($width, $height) = getimagesize($file_path);
    
    // Check if resizing is needed
    if ($width <= $max_width) {
        return true; // No optimization needed
    }
    
    // Calculate new dimensions
    $ratio = $max_width / $width;
    $new_width = $max_width;
    $new_height = (int)($height * $ratio);
    
    // Create image resource based on type
    switch ($mime_type) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($file_path);
            break;
        case 'image/png':
            $source = imagecreatefrompng($file_path);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($file_path);
            break;
        case 'image/webp':
            $source = imagecreatefromwebp($file_path);
            break;
        default:
            return false;
    }
    
    // Create new image
    $destination = imagecreatetruecolor($new_width, $new_height);
    
    // Preserve transparency for PNG and GIF
    if ($mime_type === 'image/png' || $mime_type === 'image/gif') {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefilledrectangle($destination, 0, 0, $new_width, $new_height, $transparent);
    }
    
    // Resize image
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // Save optimized image
    switch ($mime_type) {
        case 'image/jpeg':
            imagejpeg($destination, $file_path, $quality);
            break;
        case 'image/png':
            imagepng($destination, $file_path, 9);
            break;
        case 'image/gif':
            imagegif($destination, $file_path);
            break;
        case 'image/webp':
            imagewebp($destination, $file_path, $quality);
            break;
    }
    
    // Free memory
    imagedestroy($source);
    imagedestroy($destination);
    
    return true;
}

// ========================================
// VALIDATION FUNCTIONS
// ========================================

/**
 * Validate email address
 * @param string $email Email to validate
 * @return bool Valid status
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Palestine format)
 * @param string $phone Phone to validate
 * @return bool Valid status
 */
function isValidPhone($phone) {
    // Remove spaces and dashes
    $phone = preg_replace('/[\s-]/', '', $phone);
    
    // Check Palestinian phone formats
    // Mobile: 05XXXXXXXX or +9725XXXXXXXX
    // Landline: 0[2-9]XXXXXXX
    return preg_match('/^(05\d{8}|\+9725\d{8}|0[2-9]\d{7})$/', $phone);
}

/**
 * Sanitize and validate URL
 * @param string $url URL to validate
 * @return string|false Sanitized URL or false
 */
function isValidURL($url) {
    $url = filter_var($url, FILTER_SANITIZE_URL);
    return filter_var($url, FILTER_VALIDATE_URL);
}

/**
 * Check if user is admin
 * @return bool Admin status
 */
function isAdmin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is logged in
 * @return bool Login status
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// ========================================
// SECURITY FUNCTIONS
// ========================================

/**
 * Escape HTML special characters
 * @param string $string String to escape
 * @return string Escaped string
 */
function escape($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 * @return string Token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool Valid status
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ========================================
// FORMATTING FUNCTIONS
// ========================================

/**
 * Format price with currency symbol
 * @param float $price Price to format
 * @param string $currency Currency code (ILS, USD, EUR)
 * @return string Formatted price
 */
function formatPrice($price, $currency = 'ILS') {
    $price = (float)$price;
    
    $symbols = [
        'ILS' => '₪',
        'USD' => '$',
        'EUR' => '€'
    ];
    
    $symbol = $symbols[$currency] ?? '₪';
    
    return number_format($price, 2) . ' ' . $symbol;
}

/**
 * Format date in Arabic style
 * @param string $date Date string
 * @param string $format Format (default: Y-m-d)
 * @return string Formatted date
 */
function formatDate($date, $format = 'Y-m-d') {
    if (empty($date)) return '';
    
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

/**
 * Convert English numbers to Arabic
 * @param string $string String with numbers
 * @return string String with Arabic numbers
 */
function toArabicNumbers($string) {
    $arabic_numbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    $english_numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($english_numbers, $arabic_numbers, $string);
}

/**
 * Truncate text to specified length
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix (default: ...)
 * @return string Truncated text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

// ========================================
// UTILITY FUNCTIONS
// ========================================

/**
 * Redirect to a URL
 * @param string $url URL to redirect to
 * @param int $code HTTP status code (default: 302)
 */
function redirect($url, $code = 302) {
    header("Location: $url", true, $code);
    exit;
}

/**
 * Get site setting value
 * @param string $key Setting key
 * @param mixed $default Default value
 * @return mixed Setting value
 */
function getSetting($key, $default = null) {
    global $db;
    
    static $settings_cache = [];
    
    if (!isset($settings_cache[$key])) {
        $result = $db->fetchOne("SELECT setting_value FROM site_settings WHERE setting_key = ?", [$key]);
        $settings_cache[$key] = $result ? $result['setting_value'] : $default;
    }
    
    return $settings_cache[$key];
}

/**
 * Set site setting value
 * @param string $key Setting key
 * @param mixed $value Setting value
 * @return bool Success status
 */
function setSetting($key, $value) {
    global $db;
    
    return $db->execute(
        "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?",
        [$key, $value, $value]
    );
}

/**
 * Generate random string
 * @param int $length String length
 * @return string Random string
 */
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Send email (basic wrapper)
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email message
 * @param array $headers Additional headers
 * @return bool Success status
 */
function sendEmail($to, $subject, $message, $headers = []) {
    $default_headers = [
        'From' => getSetting('site_email', 'noreply@3dstore.com'),
        'Content-Type' => 'text/html; charset=UTF-8'
    ];
    
    $headers = array_merge($default_headers, $headers);
    $header_string = '';
    foreach ($headers as $key => $value) {
        $header_string .= "$key: $value\r\n";
    }
    
    return mail($to, $subject, $message, $header_string);
}

/**
 * Log error to file
 * @param string $message Error message
 * @param string $file Log file name
 */
function logError($message, $file = 'error.log') {
    $log_path = __DIR__ . '/../logs/' . $file;
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message" . PHP_EOL;
    
    // Create logs directory if not exists
    $log_dir = dirname($log_path);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents($log_path, $log_message, FILE_APPEND);
}

/**
 * Get client IP address
 * @return string IP address
 */
function getClientIP() {
    $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (isset($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    
    return '0.0.0.0';
}

/**
 * Check if request is AJAX
 * @return bool AJAX status
 */
function isAjax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Return JSON response
 * @param mixed $data Data to return
 * @param int $code HTTP status code
 */
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
