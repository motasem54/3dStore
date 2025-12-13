<?php
require_once 'includes/store-init.php';
$page_title = 'سلة التسوق';

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$cart_items = [];
$subtotal = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $products = $db->fetchAll("SELECT * FROM products WHERE id IN ($placeholders) AND status = 'active'", $ids);
    
    foreach ($products as $product) {
        $quantity = $_SESSION['cart'][$product['id']];
        $price = $currency === 'ILS' ? $product['price_ils'] : $product['price_usd'];
        $total = $price * $quantity;
        
        $cart_items[] = [
            'id' => $product['id'],
            'name' => $product['name_' . $lang],
            'sku' => $product['sku'],
            'image' => $product['image_path'],
            'price' => $price,
            'quantity' => $quantity,
            'stock' => $product['stock_quantity'],
            'total' => $total
        ];
        
        $subtotal += $total;
    }
}

include 'includes/header.php';
?>

<section style="padding:80px 0;background:var(--bg-light)">
<div class="container">
<h1 style="font-size:32px;margin-bottom:32px;text-align:center"><i class="bi bi-cart3"></i> سلة التسوق</h1>

<?php if (empty($cart_items)): ?>
<div style="text-align:center;padding:80px 20px">
    <i class="bi bi-cart-x" style="font-size:80px;color:var(--text-light);opacity:0.3;display:block;margin-bottom:20px"></i>
    <h3 style="color:var(--text-light);margin-bottom:20px">السلة فارغة</h3>
    <a href="/products.php" class="btn btn-primary"><i class="bi bi-shop"></i> تسوق الآن</a>
</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:2fr 1fr;gap:30px">
    <div>
        <?php foreach ($cart_items as $item): ?>
        <div style="background:white;border-radius:16px;padding:20px;margin-bottom:16px;display:flex;gap:20px;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
            <div style="width:100px;height:100px;border-radius:12px;overflow:hidden;flex-shrink:0">
                <?php if ($item['image']): ?>
                <img src="<?php echo UPLOAD_URL . '/products/' . $item['image']; ?>" style="width:100%;height:100%;object-fit:cover">
                <?php else: ?>
                <div style="width:100%;height:100%;background:var(--bg-light);display:flex;align-items:center;justify-content:center"><i class="bi bi-image" style="font-size:32px;color:var(--text-light)"></i></div>
                <?php endif; ?>
            </div>
            
            <div style="flex:1">
                <h3 style="font-size:18px;margin-bottom:4px"><?php echo escape($item['name']); ?></h3>
                <p style="color:var(--text-light);font-size:14px;margin-bottom:12px">SKU: <?php echo escape($item['sku']); ?></p>
                <div style="display:flex;align-items:center;gap:16px">
                    <div style="display:flex;align-items:center;gap:8px;background:var(--bg-light);border-radius:8px;padding:4px">
                        <button onclick="updateCart(<?php echo $item['id']; ?>, -1)" style="width:32px;height:32px;border:none;background:white;border-radius:6px;cursor:pointer"><i class="bi bi-dash"></i></button>
                        <span style="width:40px;text-align:center;font-weight:600"><?php echo $item['quantity']; ?></span>
                        <button onclick="updateCart(<?php echo $item['id']; ?>, 1)" style="width:32px;height:32px;border:none;background:white;border-radius:6px;cursor:pointer"><i class="bi bi-plus"></i></button>
                    </div>
                    <button onclick="removeFromCart(<?php echo $item['id']; ?>)" style="color:var(--danger);background:none;border:none;cursor:pointer;font-size:14px"><i class="bi bi-trash"></i> حذف</button>
                </div>
            </div>
            
            <div style="text-align:left"><p style="font-size:20px;font-weight:700;color:var(--primary)"><?php echo formatPrice($item['total']); ?></p></div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div>
        <div style="background:white;border-radius:16px;padding:24px;box-shadow:0 4px 12px rgba(0,0,0,0.08);position:sticky;top:100px">
            <h3 style="font-size:20px;margin-bottom:20px">ملخص الطلب</h3>
            <div style="border-top:1px solid var(--border);padding-top:16px;margin-bottom:16px">
                <div style="display:flex;justify-content:space-between;margin-bottom:12px"><span>المجموع الجزئي</span><strong><?php echo formatPrice($subtotal); ?></strong></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:12px"><span>الشحن</span><strong>مجاني</strong></div>
            </div>
            <div style="border-top:2px solid var(--border);padding-top:16px;margin-bottom:24px"><div style="display:flex;justify-content:space-between;font-size:20px"><strong>الإجمالي</strong><strong style="color:var(--primary)"><?php echo formatPrice($subtotal); ?></strong></div></div>
            <a href="/checkout.php" style="display:block;width:100%;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;padding:14px;border-radius:10px;font-weight:600;text-align:center;text-decoration:none"><i class="bi bi-credit-card"></i> إتمام الطلب</a>
            <a href="/products.php" style="display:block;text-align:center;margin-top:16px;color:var(--primary);text-decoration:none"><i class="bi bi-arrow-right"></i> متابعة التسوق</a>
        </div>
    </div>
</div>
<?php endif; ?>
</div>
</section>

<script>
function updateCart(productId, change) {
    fetch('/api/cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'update', product_id: productId, change: change})
    }).then(r => r.json()).then(data => { if(data.success) location.reload(); });
}

function removeFromCart(productId) {
    if(confirm('هل أنت متأكد؟')) {
        fetch('/api/cart.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'remove', product_id: productId})
        }).then(r => r.json()).then(data => { if(data.success) location.reload(); });
    }
}
</script>

<?php include 'includes/footer.php'; ?>