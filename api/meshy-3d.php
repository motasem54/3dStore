<?php
/**
 * Meshy AI Integration
 * Convert Image to 3D Model
 */

require_once '../includes/store-init.php';

if (!isAdmin() && !isAjax()) {
    jsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$api_key = getSetting('meshy_api_key', '');

if (empty($api_key)) {
    jsonResponse(['success' => false, 'error' => 'Meshy API Key not configured']);
}

/**
 * Generate 3D model from product image
 */
if ($action === 'generate') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    
    $product = $db->fetchOne("SELECT * FROM products WHERE id = ?", [$product_id]);
    if (!$product) {
        jsonResponse(['success' => false, 'error' => 'Product not found']);
    }
    
    if (empty($product['image_path'])) {
        jsonResponse(['success' => false, 'error' => 'Product has no image']);
    }
    
    // Update status to processing
    $db->execute("UPDATE products SET model_3d_status = 'processing' WHERE id = ?", [$product_id]);
    
    // Upload image to Meshy
    $image_url = SITE_URL . UPLOAD_URL . '/products/' . $product['image_path'];
    
    $ch = curl_init('https://api.meshy.ai/v2/image-to-3d');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $api_key,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'image_url' => $image_url,
            'enable_pbr' => true,
            'topology' => 'quad',
            'target_polycount' => 30000
        ])
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        $db->execute("UPDATE products SET model_3d_status = 'failed' WHERE id = ?", [$product_id]);
        logError("Meshy AI Error: $response", '3d-generation.log');
        jsonResponse(['success' => false, 'error' => 'API request failed', 'details' => $response]);
    }
    
    $result = json_decode($response, true);
    $task_id = $result['result'] ?? null;
    
    if (!$task_id) {
        $db->execute("UPDATE products SET model_3d_status = 'failed' WHERE id = ?", [$product_id]);
        jsonResponse(['success' => false, 'error' => 'No task ID returned']);
    }
    
    // Store task ID in database for polling
    $db->execute(
        "INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES (?, ?, 'text') ON DUPLICATE KEY UPDATE setting_value = ?",
        ["meshy_task_{$product_id}", $task_id, $task_id]
    );
    
    jsonResponse([
        'success' => true,
        'task_id' => $task_id,
        'message' => 'Generation started. This may take 2-5 minutes.'
    ]);
}

/**
 * Check generation status
 */
elseif ($action === 'check_status') {
    $product_id = (int)($_GET['product_id'] ?? $_POST['product_id'] ?? 0);
    
    $task_id = getSetting("meshy_task_{$product_id}", '');
    if (empty($task_id)) {
        jsonResponse(['success' => false, 'error' => 'No task found']);
    }
    
    $ch = curl_init("https://api.meshy.ai/v2/image-to-3d/{$task_id}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $api_key
        ]
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        jsonResponse(['success' => false, 'error' => 'Status check failed']);
    }
    
    $result = json_decode($response, true);
    $status = $result['status'] ?? 'UNKNOWN';
    
    if ($status === 'SUCCEEDED') {
        // Download the model
        $model_url = $result['model_urls']['glb'] ?? null;
        
        if ($model_url) {
            // Download GLB file
            $model_content = file_get_contents($model_url);
            $filename = uniqid() . '_' . $product_id . '.glb';
            $model_path = UPLOAD_PATH . '/models/' . $filename;
            
            // Create models directory if not exists
            if (!is_dir(UPLOAD_PATH . '/models')) {
                mkdir(UPLOAD_PATH . '/models', 0755, true);
            }
            
            file_put_contents($model_path, $model_content);
            
            // Update product
            $db->execute(
                "UPDATE products SET model_3d_path = ?, model_3d_status = 'completed', model_3d_generated_at = NOW(), enable_3d_view = 1 WHERE id = ?",
                [$filename, $product_id]
            );
            
            // Clean up task
            $db->execute("DELETE FROM site_settings WHERE setting_key = ?", ["meshy_task_{$product_id}"]);
            
            jsonResponse([
                'success' => true,
                'status' => 'completed',
                'model_path' => $filename
            ]);
        }
    } elseif ($status === 'FAILED') {
        $db->execute("UPDATE products SET model_3d_status = 'failed' WHERE id = ?", [$product_id]);
        jsonResponse(['success' => false, 'status' => 'failed', 'error' => $result['error'] ?? 'Generation failed']);
    } else {
        // Still processing
        jsonResponse([
            'success' => true,
            'status' => 'processing',
            'progress' => $result['progress'] ?? 0
        ]);
    }
}

/**
 * List all products needing 3D generation
 */
elseif ($action === 'list_pending') {
    $products = $db->fetchAll("
        SELECT id, name_ar, image_path, model_3d_status 
        FROM products 
        WHERE image_path IS NOT NULL 
        AND (model_3d_status = 'none' OR model_3d_status = 'failed')
        AND status = 'active'
        ORDER BY created_at DESC
        LIMIT 50
    ");
    
    jsonResponse(['success' => true, 'products' => $products]);
}

/**
 * Batch generate for multiple products
 */
elseif ($action === 'batch_generate') {
    $product_ids = $_POST['product_ids'] ?? [];
    
    if (empty($product_ids)) {
        jsonResponse(['success' => false, 'error' => 'No products selected']);
    }
    
    $results = [];
    foreach ($product_ids as $product_id) {
        // Trigger generation for each product
        $_POST['product_id'] = $product_id;
        $_GET['action'] = 'generate';
        
        // Small delay to avoid rate limiting
        usleep(500000); // 0.5 second
    }
    
    jsonResponse([
        'success' => true,
        'message' => count($product_ids) . ' products queued for generation'
    ]);
}

else {
    jsonResponse(['success' => false, 'error' => 'Invalid action']);
}