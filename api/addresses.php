<?php
require_once '../includes/store-init.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'add':
        $data = [
            'user_id' => $user_id,
            'label' => trim($_POST['label'] ?? ''),
            'recipient_name' => trim($_POST['recipient_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address_line1' => trim($_POST['address_line1'] ?? ''),
            'address_line2' => trim($_POST['address_line2'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'state' => trim($_POST['state'] ?? ''),
            'postal_code' => trim($_POST['postal_code'] ?? ''),
            'country' => trim($_POST['country'] ?? 'Palestine'),
            'is_default' => isset($_POST['is_default']) ? 1 : 0,
        ];
        
        if ($data['is_default']) {
            $db->execute("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?", [$user_id]);
        }
        
        $fields = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $db->execute("INSERT INTO user_addresses ($fields) VALUES ($placeholders)", array_values($data));
        
        echo json_encode(['success' => true]);
        break;
        
    case 'update':
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        
        $address = $db->fetchOne("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?", [$id, $user_id]);
        
        if (!$address) {
            echo json_encode(['success' => false, 'error' => 'Address not found']);
            exit;
        }
        
        $data = [
            'label' => trim($_POST['label'] ?? $address['label']),
            'recipient_name' => trim($_POST['recipient_name'] ?? $address['recipient_name']),
            'phone' => trim($_POST['phone'] ?? $address['phone']),
            'address_line1' => trim($_POST['address_line1'] ?? $address['address_line1']),
            'address_line2' => trim($_POST['address_line2'] ?? $address['address_line2']),
            'city' => trim($_POST['city'] ?? $address['city']),
            'state' => trim($_POST['state'] ?? $address['state']),
            'postal_code' => trim($_POST['postal_code'] ?? $address['postal_code']),
            'country' => trim($_POST['country'] ?? $address['country']),
        ];
        
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
        $values = array_merge(array_values($data), [$id, $user_id]);
        
        $db->execute("UPDATE user_addresses SET $set WHERE id = ? AND user_id = ?", $values);
        
        echo json_encode(['success' => true]);
        break;
        
    case 'delete':
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        
        $db->execute("DELETE FROM user_addresses WHERE id = ? AND user_id = ?", [$id, $user_id]);
        
        echo json_encode(['success' => true]);
        break;
        
    case 'set_default':
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        
        $address = $db->fetchOne("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?", [$id, $user_id]);
        
        if (!$address) {
            echo json_encode(['success' => false, 'error' => 'Address not found']);
            exit;
        }
        
        $db->execute("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?", [$user_id]);
        $db->execute("UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ?", [$id, $user_id]);
        
        echo json_encode(['success' => true]);
        break;
        
    case 'list':
        $addresses = $db->fetchAll("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC", [$user_id]);
        
        echo json_encode(['success' => true, 'addresses' => $addresses]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}