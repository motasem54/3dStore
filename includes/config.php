<?php
/**
 * Application Configuration
 */

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
    }
}

// Database Configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? '3dstore');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_NAME', $_ENV['APP_NAME'] ?? '3D Store');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? true);

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('PRODUCT_IMAGE_PATH', UPLOAD_PATH . '/products');
define('MODEL_3D_PATH', UPLOAD_PATH . '/models');
define('LOGO_PATH', UPLOAD_PATH . '/logo');

// URLs
define('BASE_URL', rtrim(APP_URL, '/'));
define('ASSETS_URL', BASE_URL . '/store/assets');
define('UPLOAD_URL', BASE_URL . '/uploads');

// Language & Currency
define('DEFAULT_LANG', $_ENV['DEFAULT_LANG'] ?? 'ar');
define('DEFAULT_CURRENCY', $_ENV['DEFAULT_CURRENCY'] ?? 'ILS');
define('USD_TO_ILS_RATE', floatval($_ENV['USD_TO_ILS_RATE'] ?? 3.60));

// Tax Settings
define('TAX_RATE', floatval($_ENV['TAX_RATE'] ?? 17));

// File Upload Settings
define('MAX_FILE_SIZE', $_ENV['MAX_FILE_SIZE'] ?? 10485760); // 10MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_3D_TYPES', ['glb', 'gltf']);

// Email Settings
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? '');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 587);
define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? '');
define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? '');
define('SMTP_FROM_EMAIL', $_ENV['SMTP_FROM_EMAIL'] ?? '');
define('SMTP_FROM_NAME', $_ENV['SMTP_FROM_NAME'] ?? APP_NAME);

// API Keys
define('OPENAI_API_KEY', $_ENV['OPENAI_API_KEY'] ?? '');
define('WHATSAPP_API_TOKEN', $_ENV['WHATSAPP_API_TOKEN'] ?? '');
define('IMAGE_TO_3D_API_KEY', $_ENV['IMAGE_TO_3D_API_KEY'] ?? '');

// Session Settings
define('SESSION_LIFETIME', $_ENV['SESSION_LIFETIME'] ?? 120); // minutes

// Security
define('PASSWORD_MIN_LENGTH', 8);
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 20);

// Error Reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Jerusalem');
?>