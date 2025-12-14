-- ============================================
-- Homepage Features: Banners + Lucky Wheel
-- ============================================

-- 1. Banners Table
CREATE TABLE IF NOT EXISTS banners (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title_ar VARCHAR(255),
    title_en VARCHAR(255),
    image_path VARCHAR(255) NOT NULL,
    link_url VARCHAR(500),
    position ENUM('top','middle','bottom','sidebar') DEFAULT 'top',
    is_active BOOLEAN DEFAULT 1,
    start_date DATETIME,
    end_date DATETIME,
    sort_order INT DEFAULT 0,
    clicks INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_position (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample banners
INSERT INTO banners (title_ar, title_en, image_path, link_url, position, sort_order) VALUES
('عروض الجمعة البيضاء', 'Black Friday Sale', '/uploads/banners/banner1.jpg', '/products?sale=1', 'top', 1),
('خصم 50% على الإلكترونيات', '50% Off Electronics', '/uploads/banners/banner2.jpg', '/category/electronics', 'middle', 2);

-- 2. Lucky Wheel Settings
CREATE TABLE IF NOT EXISTS lucky_wheel_prizes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name_ar VARCHAR(100) NOT NULL,
    name_en VARCHAR(100),
    prize_type ENUM('discount_percentage','discount_fixed','free_shipping','product','coupon') DEFAULT 'discount_percentage',
    prize_value VARCHAR(100) NOT NULL,
    icon VARCHAR(100) DEFAULT 'bi-gift',
    color VARCHAR(20) DEFAULT '#3b82f6',
    probability INT DEFAULT 10 COMMENT 'Probability weight 1-100',
    max_wins INT DEFAULT 0 COMMENT '0 = unlimited',
    total_wins INT DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample prizes
INSERT INTO lucky_wheel_prizes (name_ar, name_en, prize_type, prize_value, icon, color, probability, sort_order) VALUES
('خصم 10%', '10% OFF', 'discount_percentage', '10', 'bi-percent', '#fbbf24', 30, 1),
('خصم 20%', '20% OFF', 'discount_percentage', '20', 'bi-percent', '#3b82f6', 20, 2),
('خصم 50%', '50% OFF', 'discount_percentage', '50', 'bi-percent', '#10b981', 10, 3),
('شحن مجاني', 'Free Shipping', 'free_shipping', '1', 'bi-truck', '#8b5cf6', 25, 4),
('حظاً أوفر', 'Better Luck!', 'coupon', '0', 'bi-emoji-frown', '#ef4444', 15, 5);

-- 3. Lucky Wheel User Spins Log
CREATE TABLE IF NOT EXISTS lucky_wheel_spins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    prize_id INT,
    prize_code VARCHAR(50) COMMENT 'Generated coupon code',
    is_used BOOLEAN DEFAULT 0,
    spun_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (prize_id) REFERENCES lucky_wheel_prizes(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_used (is_used)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Lucky Wheel Settings
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('lucky_wheel_enabled', '1', 'boolean'),
('lucky_wheel_spins_per_user', '1', 'number'),
('lucky_wheel_reset_period', 'daily', 'text'),
('lucky_wheel_require_login', '1', 'boolean'),
('back_to_top_enabled', '1', 'boolean'),
('header_fixed', '1', 'boolean')
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- ============================================
-- Complete!
-- ============================================