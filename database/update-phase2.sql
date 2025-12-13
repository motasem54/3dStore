-- ============================================
-- 3D Store - Phase 2 Database Update
-- Lazy Loading + Multiple Images + Watermark
-- ============================================

-- 1. Add watermark applied flag to products
ALTER TABLE products
ADD COLUMN IF NOT EXISTS watermark_applied BOOLEAN DEFAULT 0 AFTER image_path,
ADD INDEX idx_watermark (watermark_applied);

-- 2. Ensure product_images table exists (created in phase 1, but adding here for completeness)
CREATE TABLE IF NOT EXISTS product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_primary (is_primary),
    INDEX idx_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Watermark settings
INSERT INTO site_settings (setting_key, setting_value, setting_type, setting_group, description) VALUES
('enable_watermark', '0', 'boolean', 'watermark', 'تفعيل العلامة المائية'),
('watermark_image', '', 'text', 'watermark', 'صورة العلامة المائية'),
('watermark_position', 'bottom-right', 'text', 'watermark', 'موقع العلامة المائية'),
('watermark_opacity', '50', 'number', 'watermark', 'شفافية العلامة المائية')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- 4. Homepage product counts settings
INSERT INTO site_settings (setting_key, setting_value, setting_type, setting_group, description) VALUES
('homepage_latest_count', '8', 'number', 'homepage', 'عدد أحدث المنتجات'),
('homepage_featured_count', '8', 'number', 'homepage', 'عدد المنتجات المميزة'),
('homepage_bestseller_count', '8', 'number', 'homepage', 'عدد الأكثر مبيعاً'),
('homepage_3d_count', '8', 'number', 'homepage', 'عدد منتجات 3D')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- 5. Add views counter optimization
ALTER TABLE products
ADD INDEX IF NOT EXISTS idx_views (views),
ADD INDEX IF NOT EXISTS idx_created (created_at);

-- 6. Optimize product images queries
ALTER TABLE product_images
ADD INDEX IF NOT EXISTS idx_composite (product_id, is_primary, sort_order);

-- 7. Create trigger to update product main image when primary image changes
DELIMITER $$

DROP TRIGGER IF EXISTS update_product_main_image$$

CREATE TRIGGER update_product_main_image
AFTER UPDATE ON product_images
FOR EACH ROW
BEGIN
    IF NEW.is_primary = 1 AND OLD.is_primary = 0 THEN
        UPDATE products 
        SET image_path = NEW.image_path 
        WHERE id = NEW.product_id;
    END IF;
END$$

DELIMITER ;

-- 8. Create view for products with images count
CREATE OR REPLACE VIEW view_products_with_images AS
SELECT 
    p.*,
    COUNT(pi.id) as images_count,
    GROUP_CONCAT(pi.image_path ORDER BY pi.is_primary DESC, pi.sort_order SEPARATOR ',') as all_images
FROM products p
LEFT JOIN product_images pi ON p.id = pi.product_id
GROUP BY p.id;

-- 9. Add full-text search index for better search performance
ALTER TABLE products
ADD FULLTEXT INDEX idx_search (name_ar, name_en, description_ar, description_en);

-- 10. Optimize orders table for lazy loading
ALTER TABLE orders
ADD INDEX IF NOT EXISTS idx_user_status (user_id, status),
ADD INDEX IF NOT EXISTS idx_created_desc (created_at DESC);

-- ============================================
-- Phase 2 Complete!
-- Features:
-- - Lazy Loading optimization
-- - Multiple images per product
-- - Watermark system
-- - Homepage customization
-- - Search optimization
-- - Performance indexes
-- ============================================