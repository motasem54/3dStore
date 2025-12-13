-- ============================================
-- Update Currency to ILS (Shekel) ₪
-- ============================================

-- Update site settings
UPDATE site_settings 
SET setting_value = 'ILS' 
WHERE setting_key = 'currency';

-- Add currency setting if not exists
INSERT INTO site_settings (setting_key, setting_value, setting_type, setting_group, description) 
VALUES ('currency', 'ILS', 'text', 'general', 'العملة')
ON DUPLICATE KEY UPDATE setting_value = 'ILS';

-- Add currency symbol
INSERT INTO site_settings (setting_key, setting_value, setting_type, setting_group, description) 
VALUES ('currency_symbol', '₪', 'text', 'general', 'رمز العملة')
ON DUPLICATE KEY UPDATE setting_value = '₪';

-- Add currency position (before/after)
INSERT INTO site_settings (setting_key, setting_value, setting_type, setting_group, description) 
VALUES ('currency_position', 'after', 'text', 'general', 'موقع رمز العملة')
ON DUPLICATE KEY UPDATE setting_value = 'after';

-- Update PayPal config
UPDATE payment_gateways 
SET config = JSON_SET(config, '$.currency', 'ILS')
WHERE slug = 'paypal';

-- Update Stripe config
UPDATE payment_gateways 
SET config = JSON_SET(config, '$.currency', 'ils')
WHERE slug = 'stripe';

-- Update any existing orders currency
ALTER TABLE orders 
MODIFY COLUMN currency VARCHAR(3) DEFAULT 'ILS';

-- Update payment_transactions
ALTER TABLE payment_transactions 
MODIFY COLUMN currency VARCHAR(3) DEFAULT 'ILS';

-- ============================================
-- Currency Update Complete! ₪
-- ============================================