<?php
require_once '../../includes/store-init.php';
header('Content-Type: application/json');

if (getSetting('lucky_wheel_enabled', '0') !== '1') {
    echo json_encode(['success' => false, 'message' => 'Spin wheel is disabled']);
    exit;
}

$prizes = $db->fetchAll(
    "SELECT id, name_ar as name, prize_type, prize_value, icon, color, probability 
     FROM lucky_wheel_prizes 
     WHERE is_active = 1 
     ORDER BY sort_order"
);

echo json_encode(['success' => true, 'prizes' => $prizes]);