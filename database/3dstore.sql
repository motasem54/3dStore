-- ====================================
-- 3D Store Database Schema
-- Multi-language E-commerce with 3D Products
-- Default Currency: ILS (Israeli Shekel) ₪
-- ====================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ====================================
-- Table: users
-- المستخدمين والعملاء
-- ====================================
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','cashier','customer') NOT NULL DEFAULT 'customer',
  `status` enum('active','inactive','blocked') NOT NULL DEFAULT 'active',
  `avatar` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Table: user_addresses
-- عناوين الشحن للمستخدمين
-- ====================================
CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `address_type` enum('shipping','billing','both') NOT NULL DEFAULT 'shipping',
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `country` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_user_address` (`user_id`),
  CONSTRAINT `fk_user_address` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Table: categories
-- التصنيفات
-- ====================================
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_ar` varchar(100) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description_ar` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_parent_category` (`parent_id`),
  CONSTRAINT `fk_parent_category` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Table: products
-- المنتجات (عادية و3D)
-- Default Currency: ILS ₪
-- ====================================
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` varchar(50) NOT NULL,
  `name_ar` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description_ar` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `product_type` enum('normal','3d') NOT NULL DEFAULT 'normal',
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Price in ILS ₪',
  `discount_price` decimal(10,2) DEFAULT NULL COMMENT 'Discounted price in ILS ₪',
  `cost_price` decimal(10,2) DEFAULT 0.00 COMMENT 'Cost in ILS ₪',
  `compare_price` decimal(10,2) DEFAULT NULL COMMENT 'Original price for comparison in ILS ₪',
  `image_path` varchar(255) DEFAULT NULL,
  `model_3d_path` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL COMMENT 'JSON array of image paths',
  `stock` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) DEFAULT 10,
  `track_inventory` tinyint(1) DEFAULT 1,
  `weight` decimal(8,2) DEFAULT NULL COMMENT 'Weight in KG',
  `dimensions` varchar(100) DEFAULT NULL COMMENT 'L x W x H in CM',
  `views` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `meta_title_ar` varchar(255) DEFAULT NULL,
  `meta_title_en` varchar(255) DEFAULT NULL,
  `meta_description_ar` text DEFAULT NULL,
  `meta_description_en` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_product_category` (`category_id`),
  KEY `idx_product_type` (`product_type`),
  KEY `idx_active` (`is_active`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_price` (`price`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Table: cart
-- سلة التسوق
-- ====================================
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`),
  KEY `fk_cart_product` (`product_id`),
  CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Table: orders
-- الطلبات - Default Currency: ILS ₪
-- ====================================
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `order_type` enum('online','pos') NOT NULL DEFAULT 'online',
  `status` enum('pending','confirmed','processing','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `payment_method` enum('cash','credit_card','paypal','stripe','bank_transfer','cod') DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `currency` varchar(3) NOT NULL DEFAULT 'ILS',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `shipping_cost` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_address` text DEFAULT NULL COMMENT 'JSON format',
  `billing_address` text DEFAULT NULL COMMENT 'JSON format',
  `notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `fk_order_user` (`user_id`),
  KEY `fk_order_processor` (`processed_by`),
  KEY `idx_status` (`status`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_order_type` (`order_type`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_order_processor` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Table: order_items
-- تفاصيل الطلبات
-- ====================================
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_sku` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL COMMENT 'Unit price in ILS ₪',
  `total_price` decimal(10,2) NOT NULL COMMENT 'Total price in ILS ₪',
  PRIMARY KEY (`id`),
  KEY `fk_order_item_order` (`order_id`),
  KEY `fk_order_item_product` (`product_id`),
  CONSTRAINT `fk_order_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Table: wishlist
-- المفضلة
-- ====================================
CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  KEY `fk_wishlist_product` (`product_id`),
  CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Table: expenses
-- المصروفات - Default Currency: ILS ₪
-- ====================================
CREATE TABLE `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expense_category` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL COMMENT 'Amount in ILS ₪',
  `description` text DEFAULT NULL,
  `expense_date` date NOT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_expense_user` (`user_id`),
  KEY `idx_expense_date` (`expense_date`),
  CONSTRAINT `fk_expense_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Table: settings
-- الإعدادات العامة
-- ====================================
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','json','boolean') NOT NULL DEFAULT 'text',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إدراج الإعدادات الافتراضية
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('site_name_ar', 'متجر ثلاثي الأبعاد', 'text'),
('site_name_en', '3D Store', 'text'),
('primary_color', '#3b82f6', 'text'),
('secondary_color', '#10b981', 'text'),
('default_language', 'ar', 'text'),
('default_currency', 'ILS', 'text'),
('currency_symbol', '₪', 'text'),
('currency_position', 'after', 'text'),
('logo_path', '', 'text'),
('favicon_path', '', 'text'),
('smtp_host', '', 'text'),
('smtp_port', '587', 'number'),
('smtp_username', '', 'text'),
('smtp_password', '', 'text'),
('smtp_from_email', '', 'text'),
('smtp_from_name', '', 'text'),
('whatsapp_api_token', '', 'text'),
('whatsapp_phone_number', '', 'text'),
('openai_api_key', '', 'text'),
('chatbot_enabled', '1', 'boolean'),
('chatbot_prompt', 'You are a helpful shopping assistant for a 3D e-commerce store in Palestine.', 'text'),
('meshy_api_key', '', 'text'),
('tax_rate', '0', 'number'),
('enable_reviews', '1', 'boolean'),
('enable_wishlist', '1', 'boolean'),
('enable_comparison', '1', 'boolean');

-- ====================================
-- Default Admin User
-- Username: admin
-- Password: admin123 (يجب تغييره فوراً!)
-- ====================================
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `role`, `status`, `email_verified`) VALUES
('admin', 'admin@3dstore.ps', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYIxIvoqgO2', 'مدير النظام', 'admin', 'active', 1);

-- ملاحظة: كلمة المرور الافتراضية هي: admin123
-- يرجى تغييرها فوراً من لوحة التحكم!

COMMIT;

-- ====================================
-- End of 3D Store Database Schema
-- Currency: ILS (Israeli Shekel) ₪
-- ====================================