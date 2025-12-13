<?php
/**
 * Image Processing Functions
 * Watermark, Resize, Optimize
 */

/**
 * Apply watermark to an image
 * @param string $image_path Full path to the image
 * @return bool Success status
 */
function applyWatermark($image_path) {
    // Get watermark settings
    $watermark_image = getSetting('watermark_image', '');
    $watermark_position = getSetting('watermark_position', 'bottom-right');
    $watermark_opacity = (int)getSetting('watermark_opacity', 50);
    
    if (empty($watermark_image)) {
        return false;
    }
    
    $watermark_path = UPLOAD_PATH . '/settings/' . $watermark_image;
    
    if (!file_exists($watermark_path)) {
        return false;
    }
    
    // Get image info
    $image_info = getimagesize($image_path);
    $watermark_info = getimagesize($watermark_path);
    
    if (!$image_info || !$watermark_info) {
        return false;
    }
    
    // Create image resources
    $image = createImageFromFile($image_path, $image_info[2]);
    $watermark = createImageFromFile($watermark_path, $watermark_info[2]);
    
    if (!$image || !$watermark) {
        return false;
    }
    
    // Get dimensions
    $image_width = imagesx($image);
    $image_height = imagesy($image);
    $watermark_width = imagesx($watermark);
    $watermark_height = imagesy($watermark);
    
    // Resize watermark if too large (max 20% of image)
    $max_watermark_width = $image_width * 0.2;
    if ($watermark_width > $max_watermark_width) {
        $ratio = $max_watermark_width / $watermark_width;
        $new_watermark_width = (int)($watermark_width * $ratio);
        $new_watermark_height = (int)($watermark_height * $ratio);
        
        $resized_watermark = imagecreatetruecolor($new_watermark_width, $new_watermark_height);
        imagealphablending($resized_watermark, false);
        imagesavealpha($resized_watermark, true);
        
        imagecopyresampled(
            $resized_watermark, $watermark,
            0, 0, 0, 0,
            $new_watermark_width, $new_watermark_height,
            $watermark_width, $watermark_height
        );
        
        imagedestroy($watermark);
        $watermark = $resized_watermark;
        $watermark_width = $new_watermark_width;
        $watermark_height = $new_watermark_height;
    }
    
    // Calculate position
    list($dest_x, $dest_y) = calculateWatermarkPosition(
        $watermark_position,
        $image_width,
        $image_height,
        $watermark_width,
        $watermark_height
    );
    
    // Apply watermark with opacity
    imagecopymerge(
        $image, $watermark,
        $dest_x, $dest_y,
        0, 0,
        $watermark_width, $watermark_height,
        $watermark_opacity
    );
    
    // Save image
    $result = saveImage($image, $image_path, $image_info[2]);
    
    // Free memory
    imagedestroy($image);
    imagedestroy($watermark);
    
    return $result;
}

/**
 * Create image resource from file
 * @param string $path File path
 * @param int $type Image type (IMAGETYPE_*)
 * @return resource|false
 */
function createImageFromFile($path, $type) {
    switch ($type) {
        case IMAGETYPE_JPEG:
            return imagecreatefromjpeg($path);
        case IMAGETYPE_PNG:
            return imagecreatefrompng($path);
        case IMAGETYPE_GIF:
            return imagecreatefromgif($path);
        case IMAGETYPE_WEBP:
            return imagecreatefromwebp($path);
        default:
            return false;
    }
}

/**
 * Save image to file
 * @param resource $image Image resource
 * @param string $path File path
 * @param int $type Image type
 * @param int $quality Quality (0-100)
 * @return bool Success status
 */
function saveImage($image, $path, $type, $quality = 85) {
    switch ($type) {
        case IMAGETYPE_JPEG:
            return imagejpeg($image, $path, $quality);
        case IMAGETYPE_PNG:
            return imagepng($image, $path, 9);
        case IMAGETYPE_GIF:
            return imagegif($image, $path);
        case IMAGETYPE_WEBP:
            return imagewebp($image, $path, $quality);
        default:
            return false;
    }
}

/**
 * Calculate watermark position
 * @param string $position Position name
 * @param int $image_width Image width
 * @param int $image_height Image height
 * @param int $watermark_width Watermark width
 * @param int $watermark_height Watermark height
 * @return array [x, y]
 */
function calculateWatermarkPosition($position, $image_width, $image_height, $watermark_width, $watermark_height) {
    $margin = 10;
    
    switch ($position) {
        case 'top-left':
            return [$margin, $margin];
        
        case 'top-right':
            return [$image_width - $watermark_width - $margin, $margin];
        
        case 'bottom-left':
            return [$margin, $image_height - $watermark_height - $margin];
        
        case 'bottom-right':
            return [
                $image_width - $watermark_width - $margin,
                $image_height - $watermark_height - $margin
            ];
        
        case 'center':
            return [
                ($image_width - $watermark_width) / 2,
                ($image_height - $watermark_height) / 2
            ];
        
        default:
            return [
                $image_width - $watermark_width - $margin,
                $image_height - $watermark_height - $margin
            ];
    }
}

/**
 * Batch apply watermark to all products
 * @return array Results
 */
function batchApplyWatermark() {
    global $db;
    
    $products = $db->fetchAll("SELECT id, image_path FROM products WHERE image_path IS NOT NULL AND watermark_applied = 0");
    
    $success = 0;
    $failed = 0;
    
    foreach ($products as $product) {
        $image_path = UPLOAD_PATH . '/products/' . $product['image_path'];
        
        if (file_exists($image_path)) {
            if (applyWatermark($image_path)) {
                $db->execute("UPDATE products SET watermark_applied = 1 WHERE id = ?", [$product['id']]);
                $success++;
            } else {
                $failed++;
            }
        }
    }
    
    return [
        'success' => $success,
        'failed' => $failed,
        'total' => count($products)
    ];
}
