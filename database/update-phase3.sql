-- ============================================
-- 3D Store - Phase 3 Database Update
-- 3D Models + Homepage Slider
-- ============================================

-- 1. Add 3D model fields to products
ALTER TABLE products
ADD COLUMN IF NOT EXISTS model_3d_path VARCHAR(255) AFTER image_path,
ADD COLUMN IF NOT EXISTS model_3d_status ENUM('none','processing','completed','failed') DEFAULT 'none' AFTER model_3d_path,
ADD COLUMN IF NOT EXISTS model_3d_generated_at TIMESTAMP NULL AFTER model_3d_status,
ADD COLUMN IF NOT EXISTS enable_3d_view BOOLEAN DEFAULT 0 AFTER model_3d_generated_at,
ADD INDEX idx_3d_status (model_3d_status),
ADD INDEX idx_3d_enabled (enable_3d_view);

-- 2. Homepage Slider Table
CREATE TABLE IF NOT EXISTS homepage_slider (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title_ar VARCHAR(255) NOT NULL,
    title_en VARCHAR(255),
    subtitle_ar TEXT,
    subtitle_en TEXT,
    image_path VARCHAR(255) NOT NULL,
    button_text_ar VARCHAR(100),
    button_text_en VARCHAR(100),
    button_link VARCHAR(255),
    background_color VARCHAR(7) DEFAULT '#3b82f6',
    text_color VARCHAR(7) DEFAULT '#ffffff',
    is_active BOOLEAN DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default slider items
INSERT INTO homepage_slider (title_ar, title_en, subtitle_ar, subtitle_en, image_path, button_text_ar, button_text_en, button_link, sort_order) VALUES
('مرحباً بك في متجر 3D', 'Welcome to 3D Store', 'تسوق أفضل المنتجات بتقنية العرض ثلاثي الأبعاد', 'Shop the best products with 3D viewing technology', 'slide1.jpg', 'تسوق الآن', 'Shop Now', '/products.php', 1),
('عروض حصرية', 'Exclusive Offers', 'خصومات تصل إلى 50% على منتجات مختارة', 'Discounts up to 50% on selected products', 'slide2.jpg', 'اكتشف العروض', 'Discover Offers', '/products.php?sort=price_low', 2),
('تجربة 3D فريدة', 'Unique 3D Experience', 'شاهد المنتجات بتقنية ثلاثية الأبعاد قبل الشراء', 'View products in 3D before buying', 'slide3.jpg', 'جرب الآن', 'Try Now', '/products.php', 3)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- 3. 3D API Settings
INSERT INTO site_settings (setting_key, setting_value, setting_type, setting_group, description) VALUES
('meshy_api_key', '', 'text', '3d_settings', 'Meshy AI API Key'),
('enable_auto_3d', '0', 'boolean', '3d_settings', 'تفعيل تحويل الصور لـ 3D تلقائياً'),
('3d_generation_mode', 'manual', 'text', '3d_settings', 'manual أو auto')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- ============================================
-- End of Phase 3 Update
-- ============================================