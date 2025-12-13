<?php
require_once 'includes/store-init.php';
$product_id = $_GET['id'] ?? 0;
$product = $db->fetchOne("SELECT * FROM products WHERE id = ? AND status = 'active'", [$product_id]);

if (!$product) redirect('/products.php');

$page_title = $product['name_' . $lang];
$category = $db->fetchOne("SELECT * FROM categories WHERE id = ?", [$product['category_id']]);
$images = json_decode($product['images'], true) ?: [];
$is_3d = $product['type'] === '3d' && !empty($product['model_path']);

include 'includes/header.php';
?>

<section style="padding:80px 0">
<div class="container">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;margin-bottom:60px">
    <!-- Product Images/3D -->
    <div>
        <?php if ($is_3d): ?>
        <div style="background:linear-gradient(135deg,#667eea,#764ba2);border-radius:20px;height:500px;display:flex;align-items:center;justify-content:center;margin-bottom:16px">
            <div style="font-size:80px;color:rgba(255,255,255,0.3)"><i class="bi bi-box"></i></div>
        </div>
        <div style="text-align:center;padding:12px;background:var(--bg-light);border-radius:10px"><i class="bi bi-mouse"></i> استخدم الماوس للتدوير والتكبير</div>
        <?php else: ?>
        <div style="background:var(--bg-light);border-radius:20px;height:500px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;overflow:hidden">
            <?php if ($product['image_path']): ?>
            <img src="<?php echo UPLOAD_URL . '/products/' . $product['image_path']; ?>" style="width:100%;height:100%;object-fit:cover">
            <?php else: ?>
            <i class="bi bi-image" style="font-size:80px;color:var(--text-light)"></i>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($images)): ?>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px">
            <?php foreach (array_slice($images, 0, 4) as $img): ?>
            <div style="border-radius:12px;overflow:hidden;height:100px;background:var(--bg-light);cursor:pointer"><img src="<?php echo UPLOAD_URL . '/products/' . $img; ?>" style="width:100%;height:100%;object-fit:cover"></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Product Info -->
    <div>
        <?php if ($category): ?>
        <a href="/products.php?category=<?php echo $category['id']; ?>" style="color:var(--primary);text-decoration:none;font-size:14px;display:inline-block;margin-bottom:8px">
            <i class="bi bi-tag"></i> <?php echo escape($category['name_' . $lang]); ?>
        </a>
        <?php endif; ?>
        
        <h1 style="font-size:36px;margin-bottom:16px"><?php echo escape($product['name_' . $lang]); ?></h1>
        
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
            <div style="display:flex;gap:4px;color:#fbbf24;font-size:18px">
                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i>
            </div>
            <span style="color:var(--text-light)">(<?php echo rand(50, 200); ?> تقييم)</span>
            <span style="color:var(--text-light)">|</span>
            <span style="color:var(--success)"><i class="bi bi-check-circle-fill"></i> متوفر في المخزون</span>
        </div>
        
        <div style="margin-bottom:24px">
            <span style="font-size:42px;font-weight:700;color:var(--primary)"><?php echo formatPrice($currency === 'ILS' ? $product['price_ils'] : $product['price_usd']); ?></span>
            <?php if ($product['sale_price_ils'] > 0): ?>
            <span style="font-size:24px;color:var(--text-light);text-decoration:line-through;margin-right:16px"><?php echo formatPrice($currency === 'ILS' ? $product['sale_price_ils'] : $product['sale_price_usd']); ?></span>
            <span style="background:var(--danger);color:white;padding:6px 12px;border-radius:20px;font-size:14px;font-weight:600">-<?php echo round((1 - $product['sale_price_ils'] / $product['price_ils']) * 100); ?>%</span>
            <?php endif; ?>
        </div>
        
        <p style="color:var(--text);line-height:1.8;margin-bottom:30px"><?php echo nl2br(escape($product['description_' . $lang])); ?></p>
        
        <div style="display:flex;gap:12px;margin-bottom:30px">
            <div style="display:flex;align-items:center;gap:8px;background:var(--bg-light);border-radius:10px;padding:8px">
                <button onclick="changeQty(-1)" style="width:40px;height:40px;border:none;background:white;border-radius:8px;cursor:pointer;font-size:18px"><i class="bi bi-dash"></i></button>
                <input type="number" id="qty" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" style="width:60px;text-align:center;border:none;background:transparent;font-size:18px;font-weight:600">
                <button onclick="changeQty(1)" style="width:40px;height:40px;border:none;background:white;border-radius:8px;cursor:pointer;font-size:18px"><i class="bi bi-plus"></i></button>
            </div>
            <button onclick="addToCart(<?php echo $product['id']; ?>)" style="flex:1;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;border:none;padding:14px;border-radius:10px;font-size:16px;font-weight:600;cursor:pointer"><i class="bi bi-cart-plus"></i> إضافة للسلة</button>
            <button style="width:56px;height:56px;background:var(--bg-light);border:none;border-radius:10px;cursor:pointer;font-size:20px"><i class="bi bi-heart"></i></button>
            <button style="width:56px;height:56px;background:var(--bg-light);border:none;border-radius:10px;cursor:pointer;font-size:20px"><i class="bi bi-arrow-left-right"></i></button>
        </div>
        
        <div style="background:var(--bg-light);border-radius:12px;padding:20px">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px"><i class="bi bi-truck" style="font-size:24px;color:var(--primary)"></i><div><strong>شحن مجاني</strong><p style="font-size:13px;color:var(--text-light)">على جميع الطلبات فوق 200₪</p></div></div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px"><i class="bi bi-arrow-repeat" style="font-size:24px;color:var(--primary)"></i><div><strong>إرجاع مجاني</strong><p style="font-size:13px;color:var(--text-light)">خلال 14 يوم</p></div></div>
            <div style="display:flex;align-items:center;gap:12px"><i class="bi bi-shield-check" style="font-size:24px;color:var(--primary)"></i><div><strong>ضمان الجودة</strong><p style="font-size:13px;color:var(--text-light)">منتجات أصلية 100%</p></div></div>
        </div>
    </div>
</div>

<!-- Related Products -->
<h2 style="font-size:28px;margin-bottom:30px;text-align:center">منتجات ذات صلة</h2>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:24px">
<?php
$related = $db->fetchAll("SELECT * FROM products WHERE category_id = ? AND id != ? AND status = 'active' LIMIT 4", [$product['category_id'], $product['id']]);
foreach ($related as $item):
    $price = $currency === 'ILS' ? $item['price_ils'] : $item['price_usd'];
?>
<div style="background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);transition:0.3s" onmouseover="this.style.transform='translateY(-8px)'" onmouseout="this.style.transform='translateY(0)'">
    <div style="height:200px;background:linear-gradient(135deg,#667eea,#764ba2);position:relative">
        <?php if ($item['image_path']): ?>
        <img src="<?php echo UPLOAD_URL . '/products/' . $item['image_path']; ?>" style="width:100%;height:100%;object-fit:cover">
        <?php endif; ?>
    </div>
    <div style="padding:16px">
        <a href="/product.php?id=<?php echo $item['id']; ?>" style="font-size:15px;font-weight:600;color:var(--dark);text-decoration:none;display:block;margin-bottom:8px"><?php echo escape($item['name_' . $lang]); ?></a>
        <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="font-size:18px;font-weight:700;color:var(--primary)"><?php echo formatPrice($price); ?></span>
            <button onclick="addToCart(<?php echo $item['id']; ?>)" style="width:36px;height:36px;background:var(--primary);border:none;border-radius:8px;color:white;cursor:pointer"><i class="bi bi-cart-plus"></i></button>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
</div>
</section>

<script>
function changeQty(change) {
    const input = document.getElementById('qty');
    const newVal = parseInt(input.value) + change;
    if (newVal >= 1 && newVal <= parseInt(input.max)) input.value = newVal;
}

function addToCart(productId) {
    const qty = document.getElementById('qty')?.value || 1;
    fetch('/api/cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'add', product_id: productId, quantity: qty})
    }).then(r => r.json()).then(data => {
        if (data.success) {
            alert('تم الإضافة للسلة!');
            location.reload();
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>