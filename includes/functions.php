<?php
/**
 * Helper Functions
 */

/**
 * Redirect
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Get Current URL
 */
function currentUrl() {
    return $_SERVER['REQUEST_URI'];
}

/**
 * Format Price
 */
function formatPrice($price, $currency = null) {
    if ($currency === null) {
        $currency = $_SESSION['currency'] ?? DEFAULT_CURRENCY;
    }
    
    $symbol = ($currency === 'ILS') ? '₪' : '$';
    return $symbol . number_format($price, 2);
}

/**
 * Convert Currency
 */
function convertCurrency($amount, $from, $to) {
    if ($from === $to) {
        return $amount;
    }
    
    if ($from === 'USD' && $to === 'ILS') {
        return $amount * USD_TO_ILS_RATE;
    }
    
    if ($from === 'ILS' && $to === 'USD') {
        return $amount / USD_TO_ILS_RATE;
    }
    
    return $amount;
}

/**
 * Calculate Tax
 */
function calculateTax($amount) {
    return $amount * (TAX_RATE / 100);
}

/**
 * Format Date
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

/**
 * Get Language Text
 */
function lang($key, $default = '') {
    global $LANG;
    return $LANG[$key] ?? $default;
}

/**
 * Get Setting
 */
function getSetting($key, $default = '') {
    global $db;
    $result = $db->fetch(
        "SELECT setting_value FROM settings WHERE setting_key = ?",
        [$key]
    );
    return $result['setting_value'] ?? $default;
}

/**
 * Update Setting
 */
function updateSetting($key, $value) {
    global $db;
    return $db->query(
        "UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?",
        [$value, $key]
    );
}

/**
 * Upload File
 */
function uploadFile($file, $destination, $allowedTypes) {
    $errors = validateFileUpload($file, $allowedTypes);
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileName = uniqid() . '_' . time() . '.' . $fileExt;
    $filePath = $destination . '/' . $fileName;
    
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return ['success' => true, 'filename' => $fileName, 'path' => $filePath];
    }
    
    return ['success' => false, 'errors' => ['File upload failed.']];
}

/**
 * Generate Order Number
 */
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Send Email
 */
function sendEmail($to, $subject, $body) {
    // Implementation will be added later with PHPMailer or native mail()
    // For now, log the email
    global $db;
    return $db->insert(
        "INSERT INTO email_logs (recipient, subject, body, status, sent_at) VALUES (?, ?, ?, 'pending', NOW())",
        [$to, $subject, $body]
    );
}

/**
 * Debug function
 */
function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}
?>