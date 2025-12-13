<?php
require_once '../includes/store-init.php';
if (!isLoggedIn()) redirect('/account/login.php');

$page_title = 'لوحة التحكم';
$user = getCurrentUser();
$orders = $db->fetchAll("SELECT * FROM orders WHERE customer_email = ? ORDER BY created_at DESC LIMIT 5", [$user['email']]);

include '../includes/header.php';
?>

<section style="padding:80px 0;background:var(--bg-light)">
<div class="container">
<div style="display:grid;grid-template-columns:280px 1fr;gap:30px">
    <!-- Sidebar -->
    <div style="background:white;border-radius:16px;padding:24px;box-shadow:0 4px 12px rgba(0,0,0,0.08);height:fit-content">
        <div style="text-align:center;margin-bottom:24px">
            <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--secondary));margin:0 auto 12px;display:flex;align-items:center;justify-content:center;color:white;font-size:32px"><i class="bi bi-person"></i></div>
            <h3 style="font-size:18px;margin-bottom:4px"><?php echo escape($user['first_name'] . ' ' . $user['last_name']); ?></h3>
            <p style="font-size:14px;color:var(--text-light)"><?php echo escape($user['email']); ?></p>
        </div>
        <nav style="display:flex;flex-direction:column;gap:4px">
            <a href="/account/dashboard.php" style="padding:12px 16px;background:var(--bg-light);border-radius:8px;text-decoration:none;color:var(--dark);font-weight:600;display:flex;align-items:center;gap:10px"><i class="bi bi-speedometer2"></i> لوحة التحكم</a>
            <a href="/account/orders.php" style="padding:12px 16px;border-radius:8px;text-decoration:none;color:var(--text);display:flex;align-items:center;gap:10px"><i class="bi bi-box-seam"></i> طلباتي</a>
            <a href="/account/wishlist.php" style="padding:12px 16px;border-radius:8px;text-decoration:none;color:var(--text);display:flex;align-items:center;gap:10px"><i class="bi bi-heart"></i> المفضلة</a>
            <a href="/account/settings.php" style="padding:12px 16px;border-radius:8px;text-decoration:none;color:var(--text);display:flex;align-items:center;gap:10px"><i class="bi bi-gear"></i> الإعدادات</a>
            <a href="/account/logout.php" style="padding:12px 16px;border-radius:8px;text-decoration:none;color:var(--danger);display:flex;align-items:center;gap:10px;margin-top:12px;border-top:1px solid var(--border);padding-top:16px"><i class="bi bi-box-arrow-right"></i> تسجيل الخروج</a>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div>
        <h1 style="font-size:32px;margin-bottom:30px">مرحباً بعودتك!</h1>
        
        <!-- Stats -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px">
            <div style="background:white;border-radius:16px;padding:24px;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
                <div style="display:flex;align-items:center;gap:16px">
                    <div style="width:60px;height:60px;border-radius:12px;background:linear-gradient(135deg,var(--primary),var(--secondary));display:flex;align-items:center;justify-content:center;color:white;font-size:28px"><i class="bi bi-box-seam"></i></div>
                    <div><p style="font-size:32px;font-weight:700;color:var(--dark)"><?php echo count($orders); ?></p><p style="font-size:14px;color:var(--text-light)">إجمالي الطلبات</p></div>
                </div>
            </div>
            <div style="background:white;border-radius:16px;padding:24px;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
                <div style="display:flex;align-items:center;gap:16px">
                    <div style="width:60px;height:60px;border-radius:12px;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;color:white;font-size:28px"><i class="bi bi-check-circle"></i></div>
                    <div><p style="font-size:32px;font-weight:700;color:var(--dark)"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'delivered')); ?></p><p style="font-size:14px;color:var(--text-light)">مكتمل</p></div>
                </div>
            </div>
            <div style="background:white;border-radius:16px;padding:24px;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
                <div style="display:flex;align-items:center;gap:16px">
                    <div style="width:60px;height:60px;border-radius:12px;background:linear-gradient(135deg,#fbbf24,#f59e0b);display:flex;align-items:center;justify-content:center;color:white;font-size:28px"><i class="bi bi-clock"></i></div>
                    <div><p style="font-size:32px;font-weight:700;color:var(--dark)"><?php echo count(array_filter($orders, fn($o) => in_array($o['status'], ['pending', 'processing', 'shipped']))); ?></p><p style="font-size:14px;color:var(--text-light)">قيد التنفيذ</p></div>
                </div>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div style="background:white;border-radius:16px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
            <h2 style="font-size:22px;margin-bottom:20px">آخر الطلبات</h2>
            <?php if (empty($orders)): ?>
            <p style="text-align:center;padding:40px;color:var(--text-light)">لم تقم بأي طلبات بعد</p>
            <?php else: ?>
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse">
                    <thead><tr style="background:var(--bg-light)"><th style="padding:12px;text-align:right">رقم الطلب</th><th style="padding:12px;text-align:right">التاريخ</th><th style="padding:12px;text-align:right">الحالة</th><th style="padding:12px;text-align:right">المبلغ</th><th style="padding:12px;text-align:center"></th></tr></thead>
                    <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr style="border-bottom:1px solid var(--border)">
                        <td style="padding:12px;font-weight:600"><?php echo escape($order['order_number']); ?></td>
                        <td style="padding:12px;color:var(--text-light)"><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                        <td style="padding:12px"><span style="padding:6px 12px;border-radius:12px;font-size:12px;font-weight:600;background:var(--bg-light)"><?php echo escape($order['status']); ?></span></td>
                        <td style="padding:12px;font-weight:700;color:var(--primary)"><?php echo formatPrice($order['total_amount']); ?></td>
                        <td style="padding:12px;text-align:center"><a href="/track.php?order=<?php echo $order['order_number']; ?>" style="color:var(--primary);text-decoration:none"><i class="bi bi-arrow-left"></i> عرض</a></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
</section>

<?php include '../includes/footer.php'; ?>