<?php
/**
 * Security Functions
 * CSRF, XSS, Rate Limiting, Input Validation
 */

/**
 * Generate CSRF Token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'Invalid CSRF token']));
    }
    return true;
}

/**
 * Get CSRF Token HTML Input
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}

/**
 * Sanitize HTML output (prevent XSS)
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize user input
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return trim(strip_tags($input));
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (international format)
 */
function isValidPhone($phone) {
    // Remove spaces, dashes, parentheses
    $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
    // Check if it's a valid phone number (8-15 digits, may start with +)
    return preg_match('/^\+?[0-9]{8,15}$/', $phone);
}

/**
 * Validate URL
 */
function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Check password strength
 * Returns: ['valid' => bool, 'message' => string]
 */
function checkPasswordStrength($password) {
    if (strlen($password) < 8) {
        return ['valid' => false, 'message' => 'Password must be at least 8 characters'];
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one uppercase letter'];
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one lowercase letter'];
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one number'];
    }
    
    return ['valid' => true, 'message' => 'Password is strong'];
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Rate Limiting
 * Prevents brute force attacks
 * 
 * @param string $key Unique identifier (e.g., 'login_' . $ip)
 * @param int $max_attempts Maximum attempts allowed
 * @param int $time_window Time window in seconds
 */
function checkRateLimit($key, $max_attempts = 5, $time_window = 300) {
    $cache_dir = __DIR__ . '/../cache/rate_limit/';
    
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }
    
    $file = $cache_dir . md5($key) . '.json';
    
    $data = [];
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
    }
    
    $now = time();
    
    // Clean old attempts
    if (isset($data['attempts'])) {
        $data['attempts'] = array_filter($data['attempts'], function($timestamp) use ($now, $time_window) {
            return ($now - $timestamp) < $time_window;
        });
    } else {
        $data['attempts'] = [];
    }
    
    // Check if rate limit exceeded
    if (count($data['attempts']) >= $max_attempts) {
        $wait_time = $time_window - ($now - min($data['attempts']));
        
        http_response_code(429);
        header('Retry-After: ' . $wait_time);
        die(json_encode([
            'success' => false,
            'message' => 'Too many requests. Please try again in ' . ceil($wait_time / 60) . ' minutes.'
        ]));
    }
    
    // Add new attempt
    $data['attempts'][] = $now;
    file_put_contents($file, json_encode($data));
    
    return true;
}

/**
 * Clear rate limit for a key
 */
function clearRateLimit($key) {
    $cache_dir = __DIR__ . '/../cache/rate_limit/';
    $file = $cache_dir . md5($key) . '.json';
    
    if (file_exists($file)) {
        unlink($file);
    }
}

/**
 * Prevent SQL Injection (use with PDO prepared statements)
 * This is just a helper - always use prepared statements!
 */
function escapeSql($value) {
    return addslashes($value);
}

/**
 * Validate file upload
 */
function validateFileUpload($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif'], $max_size = 5242880) {
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['valid' => false, 'message' => 'No file uploaded'];
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        return ['valid' => false, 'message' => 'File too large (max ' . ($max_size / 1024 / 1024) . 'MB)'];
    }
    
    // Check file extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_types)) {
        return ['valid' => false, 'message' => 'Invalid file type'];
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed_mimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    if (!in_array($mime, $allowed_mimes)) {
        return ['valid' => false, 'message' => 'Invalid file MIME type'];
    }
    
    return ['valid' => true, 'message' => 'File is valid'];
}

/**
 * Generate random secure token
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Validate API key
 */
function validateApiKey($api_key) {
    // Add your API key validation logic here
    // For example, check against database or config
    return !empty($api_key);
}

/**
 * Get client IP address
 */
function getClientIp() {
    $ip = '';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    }
    
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

/**
 * Check if request is AJAX
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Sanitize filename
 */
function sanitizeFilename($filename) {
    // Remove any path info
    $filename = basename($filename);
    
    // Remove special characters
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    
    return $filename;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Require admin access
 */
function requireAdmin() {
    if (!isAdmin()) {
        http_response_code(403);
        die('Access denied');
    }
}

/**
 * Require login
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /account/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * Prevent clickjacking
 */
function preventClickjacking() {
    header('X-Frame-Options: SAMEORIGIN');
    header('Content-Security-Policy: frame-ancestors \'self\'');
}

/**
 * Set security headers
 */
function setSecurityHeaders() {
    // Prevent MIME sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Enable XSS protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Prevent clickjacking
    preventClickjacking();
    
    // HSTS (if using HTTPS)
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Auto-apply security headers
setSecurityHeaders();
