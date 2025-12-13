<?php
require_once '../includes/store-init.php';
$page_title = 'عناويني';

if (!isLoggedIn()) {
    redirect('/account/login.php');
}

$user_id = $_SESSION['user_id'];
$addresses = $db->fetchAll("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC", [$user_id]);

include '../includes/header.php';
?>

<section style="padding:80px 0;background:var(--bg-light);min-height:calc(100vh - 200px)">
<div class="container" style="max-width:1000px">

<div style="display:grid;grid-template-columns:280px 1fr;gap:30px">
    
    <?php include 'sidebar.php'; ?>
    
    <main>
        <div style="background:white;border-radius:16px;padding:30px;margin-bottom:24px;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
                <h1 style="font-size:28px;font-weight:700;margin:0;display:flex;align-items:center;gap:12px">
                    <i class="bi bi-geo-alt" style="color:var(--primary)"></i> عناويني
                </h1>
                <button onclick="openAddressModal()" style="padding:10px 20px;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;border:none;border-radius:10px;cursor:pointer;font-weight:600;transition:0.3s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="bi bi-plus-circle"></i> إضافة عنوان
                </button>
            </div>
        </div>
        
        <?php if (empty($addresses)): ?>
        <div style="background:white;border-radius:16px;padding:60px 40px;text-align:center;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
            <div style="width:120px;height:120px;background:linear-gradient(135deg,rgba(59,130,246,0.1),rgba(139,92,246,0.1));border-radius:50%;margin:0 auto 24px;display:flex;align-items:center;justify-content:center">
                <i class="bi bi-geo-alt" style="font-size:60px;color:var(--primary)"></i>
            </div>
            <h3 style="font-size:24px;margin-bottom:12px;color:var(--dark)">لا توجد عناوين</h3>
            <p style="color:var(--text-light);margin-bottom:24px">أضف عنوان الشحن لتسهيل عملية الطلب</p>
            <button onclick="openAddressModal()" style="padding:12px 28px;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;border:none;border-radius:50px;cursor:pointer;font-weight:600;transition:0.3s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="bi bi-plus-circle"></i> إضافة عنوان
            </button>
        </div>
        <?php else: ?>
        
        <div style="display:grid;gap:20px">
            <?php foreach ($addresses as $address): ?>
            <div style="background:white;border:2px solid <?php echo $address['is_default'] ? 'var(--primary)' : 'var(--border)'; ?>;border-radius:12px;padding:24px;transition:0.3s;position:relative" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
                <?php if ($address['is_default']): ?>
                <span style="position:absolute;top:12px;left:12px;padding:4px 12px;background:var(--primary);color:white;border-radius:20px;font-size:11px;font-weight:700">العنوان الافتراضي</span>
                <?php endif; ?>
                
                <div style="display:grid;grid-template-columns:1fr auto;gap:20px">
                    <div>
                        <h3 style="font-size:18px;font-weight:600;margin-bottom:12px;color:var(--dark)"><?php echo escape($address['label']); ?></h3>
                        <div style="color:var(--text);line-height:1.8">
                            <div style="margin-bottom:6px"><i class="bi bi-person" style="color:var(--primary);margin-left:8px"></i><?php echo escape($address['recipient_name']); ?></div>
                            <div style="margin-bottom:6px"><i class="bi bi-telephone" style="color:var(--primary);margin-left:8px"></i><?php echo escape($address['phone']); ?></div>
                            <div style="margin-bottom:6px"><i class="bi bi-geo-alt" style="color:var(--primary);margin-left:8px"></i><?php echo escape($address['address_line1']); ?></div>
                            <?php if ($address['address_line2']): ?>
                            <div style="margin-bottom:6px;padding-right:28px"><?php echo escape($address['address_line2']); ?></div>
                            <?php endif; ?>
                            <div style="padding-right:28px"><?php echo escape($address['city'] . ', ' . $address['state'] . ' ' . $address['postal_code']); ?></div>
                        </div>
                    </div>
                    
                    <div style="display:flex;flex-direction:column;gap:8px">
                        <button onclick="editAddress(<?php echo $address['id']; ?>)" style="padding:8px 16px;background:var(--bg-light);border:none;border-radius:8px;cursor:pointer;transition:0.3s;font-weight:600;font-size:13px" onmouseover="this.style.background='var(--primary)';this.style.color='white'" onmouseout="this.style.background='var(--bg-light)';this.style.color=''">
                            <i class="bi bi-pencil"></i> تعديل
                        </button>
                        <?php if (!$address['is_default']): ?>
                        <button onclick="setDefault(<?php echo $address['id']; ?>)" style="padding:8px 16px;background:var(--bg-light);border:none;border-radius:8px;cursor:pointer;transition:0.3s;font-weight:600;font-size:13px" onmouseover="this.style.background='var(--success)';this.style.color='white'" onmouseout="this.style.background='var(--bg-light)';this.style.color=''">
                            <i class="bi bi-check-circle"></i> تعيين
                        </button>
                        <?php endif; ?>
                        <button onclick="deleteAddress(<?php echo $address['id']; ?>)" style="padding:8px 16px;background:var(--bg-light);border:none;border-radius:8px;cursor:pointer;transition:0.3s;font-weight:600;font-size:13px" onmouseover="this.style.background='var(--danger)';this.style.color='white'" onmouseout="this.style.background='var(--bg-light)';this.style.color=''">
                            <i class="bi bi-trash"></i> حذف
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php endif; ?>
    </main>
    
</div>

</div>
</section>

<style>
@media (max-width: 768px) {
    .container > div { grid-template-columns: 1fr !important; }
    div[style*="grid-template-columns:1fr auto"] { grid-template-columns: 1fr !important; }
}
</style>

<script>
function openAddressModal() { alert('سيتم فتح نموذج إضافة عنوان'); }
function editAddress(id) { alert('تعديل العنوان #' + id); }
function setDefault(id) {
    if (confirm('تعيين هذا العنوان كعنوان افتراضي؟')) {
        fetch('../api/addresses.php?action=set_default&id=' + id, { method: 'POST' })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); });
    }
}
function deleteAddress(id) {
    if (confirm('هل تريد حذف هذا العنوان؟')) {
        fetch('../api/addresses.php?action=delete&id=' + id, { method: 'POST' })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); });
    }
}
</script>

<?php include '../includes/footer.php'; ?>