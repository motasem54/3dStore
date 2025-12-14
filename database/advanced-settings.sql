-- ============================================
-- Advanced Site Settings
-- Language, 3D Products, Categories, Pages
-- ============================================

-- 1. Language Settings
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('enable_arabic', '1', 'boolean'),
('enable_english', '0', 'boolean'),
('force_single_language', '0', 'boolean')
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- 2. 3D Products Settings
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('enable_3d_products', '1', 'boolean'),
('show_3d_badge', '1', 'boolean'),
('3d_viewer_auto_rotate', '1', 'boolean')
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- 3. Categories Display Settings
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('category_icon_type', 'icon', 'text'),
('show_categories_home', '1', 'boolean'),
('categories_position', 'above_products', 'text')
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- 4. Homepage Settings
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('show_homepage_slider', '1', 'boolean'),
('homepage_layout', '{"slider":1,"categories":2,"featured":3,"latest":4,"3d":5}', 'json')
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- 5. Add icon field to categories
ALTER TABLE categories 
ADD COLUMN IF NOT EXISTS icon VARCHAR(100) DEFAULT NULL COMMENT 'Bootstrap icon class' AFTER image,
ADD COLUMN IF NOT EXISTS icon_type ENUM('icon','image') DEFAULT 'image' AFTER icon;

-- 6. Pages table for custom content
CREATE TABLE IF NOT EXISTS pages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(100) UNIQUE NOT NULL,
    title_ar VARCHAR(255) NOT NULL,
    title_en VARCHAR(255),
    content_ar TEXT,
    content_en TEXT,
    meta_title_ar VARCHAR(255),
    meta_title_en VARCHAR(255),
    meta_description_ar TEXT,
    meta_description_en TEXT,
    is_active BOOLEAN DEFAULT 1,
    show_in_footer BOOLEAN DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default pages
INSERT INTO pages (slug, title_ar, title_en, content_ar, content_en, show_in_footer, sort_order) VALUES
('about', 'من نحن', 'About Us', '<h2>من نحن</h2><p>نحن متجر متخصص في بيع المنتجات ثلاثية الأبعاد...</p>', '<h2>About Us</h2><p>We are a specialized store for 3D products...</p>', 1, 1),
('privacy', 'سياسة الخصوصية', 'Privacy Policy', '<h2>سياسة الخصوصية</h2><p>نحن نحترم خصوصيتك...</p>', '<h2>Privacy Policy</h2><p>We respect your privacy...</p>', 1, 2),
('terms', 'شروط الاستخدام', 'Terms of Service', '<h2>شروط الاستخدام</h2><p>بإستخدامك للموقع فإنك توافق على...</p>', '<h2>Terms of Service</h2><p>By using our website, you agree to...</p>', 1, 3),
('shipping', 'سياسة الشحن', 'Shipping Policy', '<h2>سياسة الشحن</h2><p>نقوم بالشحن لجميع المدن...</p>', '<h2>Shipping Policy</h2><p>We ship to all cities...</p>', 1, 4),
('returns', 'سياسة الاسترجاع', 'Return Policy', '<h2>سياسة الاسترجاع</h2><p>يمكنك استرجاع المنتج خلال 14 يوم...</p>', '<h2>Return Policy</h2><p>You can return products within 14 days...</p>', 1, 5)
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- ============================================
-- Settings Complete!
-- ============================================