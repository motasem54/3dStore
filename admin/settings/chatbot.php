<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminRole();

$page_title = 'إعدادات ChatBot (الذكاء الاصطناعي)';
$active_page = 'settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
        $error = 'خطأ في التحقق';
    } else {
        $chatbot_settings = [
            'chatbot_enabled' => isset($_POST['chatbot_enabled']) ? '1' : '0',
            'openai_api_key' => $_POST['openai_api_key'] ?? '',
            'chatbot_model' => $_POST['chatbot_model'] ?? 'gpt-3.5-turbo',
            'chatbot_temperature' => (float)($_POST['chatbot_temperature'] ?? 0.7),
            'chatbot_system_prompt' => sanitizeInput($_POST['chatbot_system_prompt'] ?? ''),
            'chatbot_welcome_message' => sanitizeInput($_POST['chatbot_welcome_message'] ?? ''),
        ];
        
        try {
            foreach ($chatbot_settings as $key => $value) {
                $existing = $db->fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);
                if ($existing) {
                    $db->query("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?", [$value, $key]);
                } else {
                    $db->query("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)", [$key, $value]);
                }
            }
            $success = 'تم حفظ إعدادات ChatBot بنجاح';
        } catch (Exception $e) {
            $error = 'حدث خطأ أثناء الحفظ';
        }
    }
}

$chatbot = [];
$keys = ['chatbot_enabled', 'openai_api_key', 'chatbot_model', 'chatbot_temperature', 'chatbot_system_prompt', 'chatbot_welcome_message'];
foreach ($keys as $key) {
    $result = $db->fetch("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
    $chatbot[$key] = $result['setting_value'] ?? '';
}

include __DIR__ . '/../includes/header.php';
?>

<?php if (isset($success)): ?>
<div class="alert alert-success"><i class="bi bi-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>
<?php if (isset($error)): ?>
<div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="glass-card">
    <div class="card-header space-between">
        <h4><i class="bi bi-robot"></i> إعدادات ChatBot (الذكاء الاصطناعي)</h4>
        <a href="index.php" class="btn-sm"><i class="bi bi-arrow-right"></i> رجوع</a>
    </div>
    
    <div class="card-body">
        <form method="POST" class="chatbot-form">
            <input type="hidden" name="csrf" value="<?php echo escape($_SESSION['csrf']); ?>">
            
            <div class="form-section">
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="chatbot_enabled" <?php echo $chatbot['chatbot_enabled'] === '1' ? 'checked' : ''; ?>>
                        <span>تفعيل ChatBot في الموقع</span>
                    </label>
                    <small class="text-muted">سيظهر أيقونة الدردشة في الركن السفلي من الموقع</small>
                </div>
                
                <div class="form-group">
                    <label>OpenAI API Key <span class="required">*</span></label>
                    <input type="text" name="openai_api_key" class="form-control" 
                           value="<?php echo escape($chatbot['openai_api_key']); ?>" 
                           placeholder="sk-...">
                    <small class="text-muted">احصل على API Key من: <a href="https://platform.openai.com/api-keys" target="_blank">platform.openai.com</a></small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>نموذج AI</label>
                        <select name="chatbot_model" class="form-control">
                            <option value="gpt-3.5-turbo" <?php echo ($chatbot['chatbot_model'] ?? 'gpt-3.5-turbo') === 'gpt-3.5-turbo' ? 'selected' : ''; ?>>GPT-3.5 Turbo (موصى به)</option>
                            <option value="gpt-4" <?php echo ($chatbot['chatbot_model'] ?? '') === 'gpt-4' ? 'selected' : ''; ?>>GPT-4 (أقوى)</option>
                            <option value="gpt-4-turbo" <?php echo ($chatbot['chatbot_model'] ?? '') === 'gpt-4-turbo' ? 'selected' : ''; ?>>GPT-4 Turbo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Temperature (الإبداع)</label>
                        <input type="number" step="0.1" min="0" max="2" name="chatbot_temperature" class="form-control" 
                               value="<?php echo escape($chatbot['chatbot_temperature'] ?: '0.7'); ?>">
                        <small class="text-muted">0 = أكثر دقة، 2 = أكثر إبداعاً</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>رسالة الترحيب</label>
                    <textarea name="chatbot_welcome_message" class="form-control" rows="2"><?php echo escape($chatbot['chatbot_welcome_message'] ?: 'مرحباً! كيف يمكنني مساعدتك اليوم؟'); ?></textarea>
                    <small class="text-muted">الرسالة التي تظهر عند فتح نافذة الدردشة</small>
                </div>
                
                <div class="form-group">
                    <label>System Prompt (تعليمات النظام)</label>
                    <textarea name="chatbot_system_prompt" class="form-control" rows="6"><?php echo escape($chatbot['chatbot_system_prompt'] ?: 'أنت مساعد ذكي لمتجر إلكتروني يبيع منتجات ثلاثية الأبعاد. ساعد العملاء في العثور على المنتجات، الإجابة عن الأسئلة، وتقديم الدعم الفني. كن ودوداً ومحترفاً.'); ?></textarea>
                    <small class="text-muted">تعليمات توجه سلوك ChatBot (بالعربية أو الإنجليزية)</small>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary"><i class="bi bi-check-circle"></i> حفظ الإعدادات</button>
            </div>
        </form>
        
        <div class="help-box">
            <h6><i class="bi bi-info-circle"></i> ملاحظات مهمة</h6>
            <ul>
                <li><strong>التكلفة:</strong> OpenAI تفرض رسوماً على كل استخدام. GPT-3.5 Turbo هو الأرخص.</li>
                <li><strong>الحد الأقصى:</strong> يمكنك وضع حد أقصى للإنفاق في لوحة OpenAI.</li>
                <li><strong>الأمان:</strong> احتفظ بـ API Key سرياً ولا تشاركه مع أحد.</li>
                <li><strong>التخصيص:</strong> عدّل System Prompt ليناسب متجرك ومنتجاتك.</li>
                <li><strong>البدائل:</strong> يمكنك استخدام Claude AI أو Gemini بدلاً من OpenAI.</li>
            </ul>
        </div>
    </div>
</div>

<style>
.chatbot-form { max-width: 900px; }
.form-section { background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; margin-bottom: 20px; }
.form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.form-group label { font-size: 14px; color: var(--text-secondary); }
.form-group small { font-size: 12px; }
.required { color: var(--danger); }
.checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; margin-bottom: 8px; }
.checkbox-label input { width: 18px; height: 18px; cursor: pointer; }
.form-actions { display: flex; gap: 12px; justify-content: flex-end; }
.help-box { margin-top: 30px; padding: 20px; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 12px; }
.help-box h6 { margin: 0 0 12px 0; color: var(--info); }
.help-box ul { margin: 0; padding-right: 20px; line-height: 2; }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>