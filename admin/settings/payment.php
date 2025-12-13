<?php
require_once '../../includes/store-init.php';
if (!isAdmin()) redirect('/admin/login.php');

$page_title = 'إعدادات الدفع';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gateway_id = (int)$_POST['gateway_id'];
    $is_enabled = isset($_POST['is_enabled']) ? 1 : 0;
    $fee_type = $_POST['fee_type'];
    $fee_amount = (float)$_POST['fee_amount'];
    $config = [];
    
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'config_') === 0) {
            $config_key = str_replace('config_', '', $key);
            $config[$config_key] = $value;
        }
    }
    
    $db->execute(
        "UPDATE payment_gateways SET is_enabled = ?, fee_type = ?, fee_amount = ?, config = ? WHERE id = ?",
        [$is_enabled, $fee_type, $fee_amount, json_encode($config), $gateway_id]
    );
    
    $_SESSION['success'] = 'تم تحديث إعدادات بوابة الدفع';
    redirect('/admin/settings/payment.php');
}

$gateways = $db->fetchAll("SELECT * FROM payment_gateways ORDER BY sort_order");

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-credit-card text-primary"></i> إعدادات بوابات الدفع</h2>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <?php foreach ($gateways as $gateway): 
        $config = json_decode($gateway['config'], true) ?? [];
    ?>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="<?php echo $gateway['icon']; ?> text-primary"></i>
                        <?php echo escape($gateway['name_ar']); ?>
                    </h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="enable_<?php echo $gateway['id']; ?>" <?php echo $gateway['is_enabled'] ? 'checked' : ''; ?> onchange="toggleGateway(<?php echo $gateway['id']; ?>, this.checked)">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" id="form_<?php echo $gateway['id']; ?>">
                    <input type="hidden" name="gateway_id" value="<?php echo $gateway['id']; ?>">
                    <input type="hidden" name="is_enabled" id="enabled_<?php echo $gateway['id']; ?>" value="<?php echo $gateway['is_enabled']; ?>">
                    
                    <?php if ($gateway['slug'] === 'paypal'): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Client ID</label>
                        <input type="text" name="config_client_id" class="form-control" value="<?php echo escape($config['client_id'] ?? ''); ?>" placeholder="AXX...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Client Secret</label>
                        <input type="password" name="config_client_secret" class="form-control" value="<?php echo escape($config['client_secret'] ?? ''); ?>" placeholder="EX...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">الوضع</label>
                        <select name="config_mode" class="form-select">
                            <option value="sandbox" <?php echo ($config['mode'] ?? '') === 'sandbox' ? 'selected' : ''; ?>>تجريبي (Sandbox)</option>
                            <option value="live" <?php echo ($config['mode'] ?? '') === 'live' ? 'selected' : ''; ?>>مباشر (Live)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">العملة</label>
                        <select name="config_currency" class="form-select">
                            <option value="USD" <?php echo ($config['currency'] ?? '') === 'USD' ? 'selected' : ''; ?>>USD - دولار</option>
                            <option value="ILS" <?php echo ($config['currency'] ?? '') === 'ILS' ? 'selected' : ''; ?>>ILS - شيكل</option>
                            <option value="EUR" <?php echo ($config['currency'] ?? '') === 'EUR' ? 'selected' : ''; ?>>EUR - يورو</option>
                        </select>
                    </div>
                    <?php elseif ($gateway['slug'] === 'stripe'): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Public Key</label>
                        <input type="text" name="config_public_key" class="form-control" value="<?php echo escape($config['public_key'] ?? ''); ?>" placeholder="pk_...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Secret Key</label>
                        <input type="password" name="config_secret_key" class="form-control" value="<?php echo escape($config['secret_key'] ?? ''); ?>" placeholder="sk_...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Webhook Secret</label>
                        <input type="password" name="config_webhook_secret" class="form-control" value="<?php echo escape($config['webhook_secret'] ?? ''); ?>" placeholder="whsec_...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">العملة</label>
                        <select name="config_currency" class="form-select">
                            <option value="usd" <?php echo ($config['currency'] ?? '') === 'usd' ? 'selected' : ''; ?>>USD - دولار</option>
                            <option value="ils" <?php echo ($config['currency'] ?? '') === 'ils' ? 'selected' : ''; ?>>ILS - شيكل</option>
                            <option value="eur" <?php echo ($config['currency'] ?? '') === 'eur' ? 'selected' : ''; ?>>EUR - يورو</option>
                        </select>
                    </div>
                    <?php elseif ($gateway['slug'] === 'cod'): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">تعليمات للعميل</label>
                        <textarea name="config_instructions" class="form-control" rows="3"><?php echo escape($config['instructions'] ?? ''); ?></textarea>
                    </div>
                    <?php elseif ($gateway['slug'] === 'manual_visa'): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">تعليمات</label>
                        <textarea name="config_instructions" class="form-control" rows="3"><?php echo escape($config['instructions'] ?? ''); ?></textarea>
                        <small class="text-muted">سيتم تخزين معلومات البطاقة بشكل مشفر</small>
                    </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">نوع الرسوم</label>
                            <select name="fee_type" class="form-select">
                                <option value="fixed" <?php echo $gateway['fee_type'] === 'fixed' ? 'selected' : ''; ?>>ثابتة</option>
                                <option value="percentage" <?php echo $gateway['fee_type'] === 'percentage' ? 'selected' : ''; ?>>نسبة مئوية</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">قيمة الرسوم</label>
                            <input type="number" step="0.01" name="fee_amount" class="form-control" value="<?php echo $gateway['fee_amount']; ?>">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save"></i> حفظ الإعدادات
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
function toggleGateway(id, enabled) {
    document.getElementById('enabled_' + id).value = enabled ? '1' : '0';
}
</script>

<?php include '../includes/footer.php'; ?>