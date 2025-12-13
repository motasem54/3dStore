<?php
require_once '../includes/store-init.php';
header('Content-Type: application/json');

if (!isAdmin()) {
    jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
}

if (!isset($_FILES['file']) || !isset($_POST['product_id'])) {
    jsonResponse(['success' => false, 'error' => 'Missing file or product_id']);
}

$product_id = (int)$_POST['product_id'];
$is_primary = isset($_POST['is_primary']) ? 1 : 0;

// Check if product exists
$product = $db->fetchOne("SELECT id FROM products WHERE id = ?", [$product_id]);
if (!$product) {
    jsonResponse(['success' => false, 'error' => 'Product not found']);
}

// Upload file
$filename = uploadFile($_FILES['file'], 'products');

if (!$filename) {
    jsonResponse(['success' => false, 'error' => 'Upload failed']);
}

// Apply watermark if enabled
$watermark_enabled = getSetting('enable_watermark', 0);
if ($watermark_enabled) {
    $file_path = UPLOAD_PATH . '/products/' . $filename;
    applyWatermark($file_path);
}

// Get current max sort order
$max_order = $db->fetchOne(
    "SELECT COALESCE(MAX(sort_order), 0) as max_order FROM product_images WHERE product_id = ?",
    [$product_id]
)['max_order'];

// If this is primary, unset other primary images
if ($is_primary) {
    $db->execute("UPDATE product_images SET is_primary = 0 WHERE product_id = ?", [$product_id]);
    
    // Also update main product image
    $db->execute("UPDATE products SET image_path = ? WHERE id = ?", [$filename, $product_id]);
}

// Insert image record
$db->execute(
    "INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)",
    [$product_id, $filename, $is_primary, $max_order + 1]
);

$image_id = $db->lastInsertId();

jsonResponse([
    'success' => true,
    'image_id' => $image_id,
    'filename' => $filename,
    'url' => UPLOAD_URL . '/products/' . $filename
]);