-- ============================================
-- 3D Store - Phase 1 Database Update
-- Payment Gateways + Settings + Shipping + Stats
-- ============================================

-- 1. Site Settings Table
CREATE TABLE IF NOT EXISTS site_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text','number','boolean','json','image','color') DEFAULT 'text',
    setting_group VARCHAR(50) DEFAULT 'general',
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key),
    INDEX idx_group (setting_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Settings
INSERT INTO site_settings (setting_key, setting_value, setting_type, setting_group, description) VALUES
('site_name', '3D Store', 'text', 'general', 'اسم المتجر'),
('site_logo', '', 'image', 'general', 'شعار المتجر'),
('site_favicon', '', 'image', 'general', 'أيقونة المتجر'),
('primary_color', '#3b82f6', 'color', 'appearance', 'اللون الأساسي'),
('secondary_color', '#8b5cf6', 'color', 'appearance', 'اللون الثانوي'),
('success_color', '#10b981', 'color', 'appearance', 'لون النجاح'),
('danger_color', '#ef4444', 'color', 'appearance', 'لون الخطر'),
('products_per_page', '20', 'number', 'display', 'عدد المنتجات في الصفحة'),
('homepage_latest', '10', 'number', 'display', 'عدد أحدث المنتجات'),
('homepage_bestsellers', '8', 'number', 'display', 'عدد الأكثر مبيعاً'),
('homepage_3d', '4', 'number', 'display', 'عدد منتجات 3D'),
('enable_watermark', '0', 'boolean', 'watermark', 'تفعيل العلامة المائية'),
('watermark_image', '', 'image', 'watermark', 'صورة العلامة المائية'),
('watermark_position', 'bottom-right', 'text', 'watermark', 'موقع العلامة المائية'),
('watermark_opacity', '50', 'number', 'watermark', 'شفافية العلامة المائية')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- 2. Payment Gateways Table
CREATE TABLE IF NOT EXISTS payment_gateways (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    name_ar VARCHAR(100) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    is_enabled BOOLEAN DEFAULT 0,
    icon VARCHAR(255),
    config JSON,
    fee_type ENUM('fixed','percentage') DEFAULT 'fixed',
    fee_amount DECIMAL(10,2) DEFAULT 0.00,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_enabled (is_enabled),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Payment Gateways
INSERT INTO payment_gateways (name, name_ar, slug, icon, config, sort_order) VALUES
('PayPal', 'باي بال', 'paypal', 'bi-paypal', JSON_OBJECT(
    'client_id', '',
    'client_secret', '',
    'mode', 'sandbox',
    'currency', 'USD'
), 1),
('Stripe', 'سترايب', 'stripe', 'bi-credit-card', JSON_OBJECT(
    'public_key', '',
    'secret_key', '',
    'webhook_secret', '',
    'currency', 'usd'
), 2),
('Cash on Delivery', 'الدفع عند الاستلام', 'cod', 'bi-cash-coin', JSON_OBJECT(
    'instructions', 'سيتم تحصيل المبلغ عند استلام الطلب'
), 3),
('Manual Visa', 'فيزا يدوي', 'manual_visa', 'bi-credit-card-2-front', JSON_OBJECT(
    'instructions', 'أدخل معلومات بطاقة الائتمان'
), 4)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- 3. Shipping Companies Table
CREATE TABLE IF NOT EXISTS shipping_companies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    name_ar VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    website VARCHAR(255),
    tracking_url VARCHAR(255),
    is_active BOOLEAN DEFAULT 1,
    sort_order INT DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Shipping Shipments Table
CREATE TABLE IF NOT EXISTS shipping_shipments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    shipment_number VARCHAR(100) UNIQUE NOT NULL,
    shipment_date DATE NOT NULL,
    total_orders INT DEFAULT 0,
    total_amount DECIMAL(10,2) DEFAULT 0.00,
    shipping_cost DECIMAL(10,2) DEFAULT 0.00,
    company_commission DECIMAL(10,2) DEFAULT 0.00,
    net_amount DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending','shipped','delivered','returned') DEFAULT 'pending',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES shipping_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_company (company_id),
    INDEX idx_status (status),
    INDEX idx_date (shipment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Shipment Items Table
CREATE TABLE IF NOT EXISTS shipment_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    shipment_id INT NOT NULL,
    order_id INT NOT NULL,
    tracking_number VARCHAR(100),
    weight DECIMAL(10,2),
    dimensions VARCHAR(50),
    shipping_cost DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending','shipped','delivered','returned') DEFAULT 'pending',
    delivered_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shipment_id) REFERENCES shipping_shipments(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_shipment (shipment_id),
    INDEX idx_order (order_id),
    INDEX idx_tracking (tracking_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Product Images Table (Multiple Images)
CREATE TABLE IF NOT EXISTS product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Payment Transactions Table
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    gateway_slug VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(255),
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    status ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
    payment_data JSON,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order (order_id),
    INDEX idx_gateway (gateway_slug),
    INDEX idx_status (status),
    INDEX idx_transaction (transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Add columns to orders table if not exist
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS payment_gateway VARCHAR(50) AFTER payment_method,
ADD COLUMN IF NOT EXISTS shipping_company_id INT AFTER shipping_address,
ADD COLUMN IF NOT EXISTS tracking_number VARCHAR(100) AFTER shipping_company_id,
ADD COLUMN IF NOT EXISTS shipped_at TIMESTAMP NULL AFTER tracking_number,
ADD COLUMN IF NOT EXISTS delivered_at TIMESTAMP NULL AFTER shipped_at,
ADD INDEX IF NOT EXISTS idx_payment_gateway (payment_gateway),
ADD INDEX IF NOT EXISTS idx_shipping_company (shipping_company_id);

-- 9. Sales Statistics View
CREATE OR REPLACE VIEW sales_statistics AS
SELECT 
    DATE(created_at) as sale_date,
    COUNT(DISTINCT id) as total_orders,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_order_value,
    SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_orders,
    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders
FROM orders
GROUP BY DATE(created_at)
ORDER BY sale_date DESC;

-- 10. Top Products View
CREATE OR REPLACE VIEW top_products AS
SELECT 
    p.id,
    p.name_ar,
    p.name_en,
    p.image_path,
    p.price,
    p.stock,
    COUNT(oi.id) as times_ordered,
    SUM(oi.quantity) as total_quantity_sold,
    SUM(oi.total_price) as total_revenue
FROM products p
LEFT JOIN order_items oi ON p.id = oi.product_id
LEFT JOIN orders o ON oi.order_id = o.id
WHERE o.status != 'cancelled'
GROUP BY p.id
ORDER BY total_quantity_sold DESC
LIMIT 20;

-- 11. Update products table for watermark tracking
ALTER TABLE products
ADD COLUMN IF NOT EXISTS watermark_applied BOOLEAN DEFAULT 0 AFTER image_path,
ADD INDEX IF NOT EXISTS idx_watermark (watermark_applied);

-- ============================================
-- End of Phase 1 Update
-- ============================================