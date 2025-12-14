<?php
require_once '../../includes/store-init.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$prizeId = $input['prize_id'] ?? 0;

// Check if user already spun today
$resetPeriod = getSetting('lucky_wheel_reset_period', 'daily');
$timeCondition = match($resetPeriod) {
    'daily' => 'DATE(spun_at) = CURDATE()',
    'weekly' => 'WEEK(spun_at) = WEEK(NOW())',
    'monthly' => 'MONTH(spun_at) = MONTH(NOW())',
    'never' => '1=1',
    default => 'DATE(spun_at) = CURDATE()'
};

$existing = $db->fetchOne(
    "SELECT id FROM lucky_wheel_spins WHERE user_id = ? AND $timeCondition",
    [$_SESSION['user_id']]
);

if ($existing) {
    echo json_encode(['success' => false, 'message' => 'Already spun']);
    exit;
}

// Generate coupon code
$code = 'SPIN' . strtoupper(substr(md5(uniqid()), 0, 8));

// Save spin
$db->insert('lucky_wheel_spins', [
    'user_id' => $_SESSION['user_id'],
    'prize_id' => $prizeId,
    'prize_code' => $code
]);

// Update prize wins count
$db->query(
    "UPDATE lucky_wheel_prizes SET total_wins = total_wins + 1 WHERE id = ?",
    [$prizeId]
);

echo json_encode(['success' => true, 'code' => $code]);