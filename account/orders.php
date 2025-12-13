<?php
require_once '../includes/store-init.php';
if (!isLoggedIn()) redirect('/account/login.php');

$page_title = 'طلباتي';
$user = getCurrentUser();
$orders = $db->fetchAll("SELECT * FROM orders WHERE customer_email = ? ORDER BY created_at DESC", [$user['email']]);

include '../includes/header.php';
?>

<section style="padding:80px 0;background:var(--bg-light)">
<div class="container">
<div style="display:grid;grid-template-columns:280px 1fr;gap:30px">
    <?php include 'sidebar.php'; ?>
    
    <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px">
            <h1 style="font-size:32px">طلباتي</h1>
            <span style="background:var(--bg-light);padding:10px 16px;border-radius:10px;font-weight:600">إجمالي: <?php echo count($orders); ?> طلب</span>
        </div>
        
        <?php if (empty($orders)): ?>
        <div style="background:white;border-radius:16px;padding:80px 40px;text-align:center;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
            <i class="bi bi-box-seam" style="font-size:80px;color:var(--text-light);opacity:0.3;display:block;margin-bottom:20px"></i>
            <h3 style="color:var(--text-light);margin-bottom:20px">لم تقم بأي طلبات بعد</h3>
            <a href="/products.php" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;border-radius:50px;text-decoration:none;font-weight:600"><i class="bi bi-shop"></i> ابدأ التسوق</a>
        </div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:20px">
            <?php foreach ($orders as $order): 
                $status_colors = ['pending' => '#fbbf24', 'processing' => '#3b82f6', 'shipped' => '#8b5cf6', 'delivered' => '#10b981', 'cancelled' => '#ef4444'];
                $status_names = ['pending' => 'قيد المراجعة', 'processing' => 'قيد التجهيز', 'shipped' => 'تم الشحن', 'delivered' => 'تم التوصيل', 'cancelled' => 'ملغي'];
            ?>
            <div style="background:white;border-radius:16px;padding:24px;box-shadow:0 4px 12px rgba(0,0,0,0.08);transition:0.3s" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:20px">
                    <div>
                        <h3 style="font-size:20px;margin-bottom:6px">طلب #<?php echo escape($order['order_number']); ?></h3>
                        <p style="color:var(--text-light);font-size:14px"><i class="bi bi-calendar"></i> <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
                    </div>
                    <span style="padding:8px 16px;background:<?php echo $status_colors[$order['status']]; ?>;color:white;border-radius:20px;font-size:13px;font-weight:600"><?php echo $status_names[$order['status']]; ?></span>
                </div>
                
                <div style="background:var(--bg-light);border-radius:12px;padding:16px;margin-bottom:16px">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px">
                        <div><p style="font-size:13px;color:var(--text-light);margin-bottom:4px">عدد المنتجات</p><strong><?php echo $db->fetchOne("SELECT COUNT(*) as count FROM order_items WHERE order_id = ?", [$order['id']])['count']; ?> منتج</strong></div>
                        <div><p style="font-size:13px;color:var(--text-light);margin-bottom:4px">طريقة الدفع</p><strong><?php echo escape($order['payment_method']); ?></strong></div>
                        <div><p style="font-size:13px;color:var(--text-light);margin-bottom:4px">حالة الدفع</p><strong style="color:<?php echo $order['payment_status'] === 'paid' ? 'var(--success)' : 'var(--danger)'; ?>"><?php echo $order['payment_status'] === 'paid' ? 'مدفوع' : 'غير مدفوع'; ?></strong></div>
                        <div><p style="font-size:13px;color:var(--text-light);margin-bottom:4px">الإجمالي</p><strong style="font-size:20px;color:var(--primary)"><?php echo formatPrice($order['total_amount']); ?></strong></div>
                    </div>
                </div>
                
                <div style="display:flex;gap:12px">
                    <a href="/track.php?order=<?php echo $order['order_number']; ?>" style="flex:1;padding:12px;background:var(--primary);color:white;border-radius:10px;text-align:center;text-decoration:none;font-weight:600;transition:0.3s" onmouseover="this.style.background='var(--secondary)'" onmouseout="this.style.background='var(--primary)'"><i class="bi bi-eye"></i> عرض التفاصيل</a>
                    <?php if ($order['status'] === 'delivered'): ?>
                    <a href="/account/review.php?order=<?php echo $order['id']; ?>" style="padding:12px 20px;background:var(--bg-light);border-radius:10px;text-align:center;text-decoration:none;font-weight:600;color:var(--text);transition:0.3s" onmouseover="this.style.background='var(--border)'" onmouseout="this.style.background='var(--bg-light)'"><i class="bi bi-star"></i> تقييم</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>
</section>

<?php include '../includes/footer.php'; ?>