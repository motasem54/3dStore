<?php
require_once 'includes/store-init.php';
if (empty($_SESSION['cart'])) redirect('/cart.php');

$page_title = 'إتمام الطلب';
include 'includes/header.php';
?>

<section style="padding:80px 0;background:var(--bg-light)">
<div class="container">
<h1 style="font-size:32px;margin-bottom:40px;text-align:center"><i class="bi bi-credit-card"></i> إتمام الطلب</h1>

<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:30px">
    <div>
        <!-- Shipping Info -->
        <div style="background:white;border-radius:16px;padding:30px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
            <h3 style="font-size:20px;margin-bottom:24px"><i class="bi bi-truck"></i> معلومات الشحن</h3>
            <form id="checkoutForm">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
                    <div><label style="display:block;margin-bottom:8px;font-weight:600">الاسم الأول</label><input type="text" name="first_name" required style="width:100%;padding:12px;border:1px solid var(--border);border-radius:8px"></div>
                    <div><label style="display:block;margin-bottom:8px;font-weight:600">الاسم الأخير</label><input type="text" name="last_name" required style="width:100%;padding:12px;border:1px solid var(--border);border-radius:8px"></div>
                </div>
                <div style="margin-bottom:16px"><label style="display:block;margin-bottom:8px;font-weight:600">رقم الجوال</label><input type="tel" name="phone" required style="width:100%;padding:12px;border:1px solid var(--border);border-radius:8px"></div>
                <div style="margin-bottom:16px"><label style="display:block;margin-bottom:8px;font-weight:600">البريد الإلكتروني</label><input type="email" name="email" required style="width:100%;padding:12px;border:1px solid var(--border);border-radius:8px"></div>
                <div style="margin-bottom:16px"><label style="display:block;margin-bottom:8px;font-weight:600">العنوان الكامل</label><textarea name="address" required rows="3" style="width:100%;padding:12px;border:1px solid var(--border);border-radius:8px"></textarea></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div><label style="display:block;margin-bottom:8px;font-weight:600">المدينة</label><input type="text" name="city" required style="width:100%;padding:12px;border:1px solid var(--border);border-radius:8px"></div>
                    <div><label style="display:block;margin-bottom:8px;font-weight:600">الرمز البريدي</label><input type="text" name="postal_code" style="width:100%;padding:12px;border:1px solid var(--border);border-radius:8px"></div>
                </div>
            </form>
        </div>
        
        <!-- Payment Method -->
        <div style="background:white;border-radius:16px;padding:30px;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
            <h3 style="font-size:20px;margin-bottom:24px"><i class="bi bi-wallet2"></i> طريقة الدفع</h3>
            <div style="display:flex;flex-direction:column;gap:12px">
                <label style="display:flex;align-items:center;gap:12px;padding:16px;border:2px solid var(--border);border-radius:12px;cursor:pointer;transition:0.3s" class="payment-option">
                    <input type="radio" name="payment" value="cod" checked style="width:20px;height:20px">
                    <i class="bi bi-cash" style="font-size:24px;color:var(--primary)"></i>
                    <div><strong>الدفع عند الاستلام</strong><p style="font-size:13px;color:var(--text-light);margin-top:4px">ادفع بعد وصول الطلب</p></div>
                </label>
                <label style="display:flex;align-items:center;gap:12px;padding:16px;border:2px solid var(--border);border-radius:12px;cursor:pointer;transition:0.3s" class="payment-option">
                    <input type="radio" name="payment" value="card" style="width:20px;height:20px">
                    <i class="bi bi-credit-card" style="font-size:24px;color:var(--primary)"></i>
                    <div><strong>بطاقة الائتمان</strong><p style="font-size:13px;color:var(--text-light);margin-top:4px">دفع مباشر وآمن</p></div>
                </label>
                <label style="display:flex;align-items:center;gap:12px;padding:16px;border:2px solid var(--border);border-radius:12px;cursor:pointer;transition:0.3s" class="payment-option">
                    <input type="radio" name="payment" value="paypal" style="width:20px;height:20px">
                    <i class="bi bi-paypal" style="font-size:24px;color:var(--primary)"></i>
                    <div><strong>PayPal</strong><p style="font-size:13px;color:var(--text-light);margin-top:4px">ادفع عبر بايبال</p></div>
                </label>
            </div>
        </div>
    </div>
    
    <!-- Order Summary -->
    <div>
        <div style="background:white;border-radius:16px;padding:24px;box-shadow:0 4px 12px rgba(0,0,0,0.08);position:sticky;top:100px">
            <h3 style="font-size:20px;margin-bottom:20px">ملخص الطلب</h3>
            <div style="max-height:300px;overflow-y:auto;margin-bottom:16px">
                <?php
                $subtotal = 0;
                foreach ($_SESSION['cart'] as $id => $qty) {
                    $product = $db->fetchOne("SELECT * FROM products WHERE id = ?", [$id]);
                    if ($product) {
                        $price = $currency === 'ILS' ? $product['price_ils'] : $product['price_usd'];
                        $total = $price * $qty;
                        $subtotal += $total;
                        echo '<div style="display:flex;justify-content:space-between;margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid var(--border)"><div><strong>' . escape($product['name_' . $lang]) . '</strong><p style="font-size:13px;color:var(--text-light)">x' . $qty . '</p></div><strong>' . formatPrice($total) . '</strong></div>';
                    }
                }
                ?>
            </div>
            <div style="border-top:1px solid var(--border);padding-top:16px;margin-bottom:16px">
                <div style="display:flex;justify-content:space-between;margin-bottom:12px"><span>المجموع الجزئي</span><strong><?php echo formatPrice($subtotal); ?></strong></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:12px"><span>الشحن</span><strong style="color:var(--success)">مجاني</strong></div>
            </div>
            <div style="border-top:2px solid var(--border);padding-top:16px;margin-bottom:24px"><div style="display:flex;justify-content:space-between;font-size:22px"><strong>الإجمالي</strong><strong style="color:var(--primary)"><?php echo formatPrice($subtotal); ?></strong></div></div>
            <button type="submit" form="checkoutForm" style="width:100%;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;padding:16px;border:none;border-radius:10px;font-size:16px;font-weight:600;cursor:pointer"><i class="bi bi-check-circle"></i> إتمام الطلب</button>
            <div style="margin-top:20px;padding:16px;background:var(--bg-light);border-radius:8px;font-size:13px;text-align:center"><i class="bi bi-shield-check" style="color:var(--success);margin-left:4px"></i> معاملاتك محمية بنظام SSL</div>
        </div>
    </div>
</div>
</div>
</section>

<style>
.payment-option:has(input:checked) { border-color: var(--primary); background: rgba(59,130,246,0.05); }
</style>

<script>
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const payment = document.querySelector('input[name="payment"]:checked').value;
    formData.append('payment_method', payment);
    
    fetch('/api/checkout.php', {
        method: 'POST',
        body: formData
    }).then(r => r.json()).then(data => {
        if (data.success) {
            window.location.href = '/track.php?order=' + data.order_id;
        } else {
            alert('حدث خطأ: ' + data.message);
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>