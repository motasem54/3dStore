<?php
require_once 'includes/store-init.php';
$page_title = 'مقارنة المنتجات';

$compare_ids = $_SESSION['compare'] ?? [];
$products = [];

if (!empty($compare_ids)) {
    $placeholders = implode(',', array_fill(0, count($compare_ids), '?'));
    $products = $db->fetchAll("SELECT * FROM products WHERE id IN ($placeholders) AND status = 'active'", $compare_ids);
}

include 'includes/header.php';
?>

<section style="padding:80px 0;background:var(--bg-light);min-height:calc(100vh - 200px)">
<div class="container">

<div style="background:white;border-radius:16px;padding:30px;margin-bottom:30px;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
    <h1 style="font-size:32px;font-weight:700;margin-bottom:8px;display:flex;align-items:center;gap:12px">
        <i class="bi bi-arrow-left-right" style="color:var(--primary)"></i> مقارنة المنتجات
    </h1>
    <p style="color:var(--text-light);font-size:15px">قارن بين المنتجات لاختيار الأفضل</p>
</div>

<?php if (empty($products)): ?>
<div style="background:white;border-radius:16px;padding:80px 40px;text-align:center;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
    <div style="width:140px;height:140px;background:linear-gradient(135deg,rgba(59,130,246,0.1),rgba(139,92,246,0.1));border-radius:50%;margin:0 auto 24px;display:flex;align-items:center;justify-content:center">
        <i class="bi bi-arrow-left-right" style="font-size:70px;color:var(--primary)"></i>
    </div>
    <h3 style="font-size:26px;margin-bottom:12px;color:var(--dark)">لا توجد منتجات للمقارنة</h3>
    <p style="color:var(--text-light);margin-bottom:30px;font-size:16px">أضف منتجات للمقارنة بينها</p>
    <a href="products.php" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;border-radius:50px;text-decoration:none;font-weight:600;transition:0.3s" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
        <i class="bi bi-shop"></i> تصفح المنتجات
    </a>
</div>
<?php else: ?>

<div style="background:white;border-radius:16px;overflow-x:auto;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
    <table style="width:100%;border-collapse:collapse">
        <thead>
            <tr style="background:var(--bg-light);border-bottom:2px solid var(--border)">
                <th style="padding:20px;text-align:right;font-weight:600;color:var(--dark);min-width:150px">الميزة</th>
                <?php foreach ($products as $product): ?>
                <th style="padding:20px;text-align:center;min-width:200px">
                    <div style="position:relative">
                        <button onclick="removeFromCompare(<?php echo $product['id']; ?>)" style="position:absolute;top:-10px;left:-10px;width:32px;height:32px;background:var(--danger);color:white;border:none;border-radius:50%;cursor:pointer;transition:0.3s" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'"><i class="bi bi-x-lg"></i></button>
                        <?php if ($product['image_path']): ?>
                        <img src="<?php echo UPLOAD_URL . '/products/' . $product['image_path']; ?>" style="width:120px;height:120px;object-fit:cover;border-radius:12px;margin:0 auto 12px">
                        <?php endif; ?>
                        <div style="font-weight:600;font-size:14px;color:var(--dark)"><?php echo escape($product['name_' . $lang]); ?></div>
                    </div>
                </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <tr style="border-bottom:1px solid var(--border)">
                <td style="padding:16px 20px;font-weight:600">السعر</td>
                <?php foreach ($products as $product): ?>
                <td style="padding:16px 20px;text-align:center">
                    <div style="font-size:22px;font-weight:700;color:var(--primary)"><?php echo formatPrice($product['discount_price'] ?: $product['price']); ?></div>
                    <?php if ($product['discount_price']): ?>
                    <div style="font-size:14px;color:var(--text-light);text-decoration:line-through;margin-top:4px"><?php echo formatPrice($product['price']); ?></div>
                    <?php endif; ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <tr style="border-bottom:1px solid var(--border)">
                <td style="padding:16px 20px;font-weight:600">التقييم</td>
                <?php foreach ($products as $product): ?>
                <td style="padding:16px 20px;text-align:center">
                    <div style="color:#fbbf24;font-size:18px">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i>
                    </div>
                    <div style="color:var(--text-light);font-size:13px;margin-top:4px">(4.5/5)</div>
                </td>
                <?php endforeach; ?>
            </tr>
            <tr style="border-bottom:1px solid var(--border)">
                <td style="padding:16px 20px;font-weight:600">التوفر</td>
                <?php foreach ($products as $product): ?>
                <td style="padding:16px 20px;text-align:center">
                    <span style="padding:6px 14px;background:<?php echo $product['stock'] > 0 ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)'; ?>;color:<?php echo $product['stock'] > 0 ? 'var(--success)' : 'var(--danger)'; ?>;border-radius:20px;font-size:13px;font-weight:600">
                        <?php echo $product['stock'] > 0 ? 'متوفر' : 'غير متوفر'; ?>
                    </span>
                </td>
                <?php endforeach; ?>
            </tr>
            <tr style="border-bottom:1px solid var(--border)">
                <td style="padding:16px 20px;font-weight:600">الوصف</td>
                <?php foreach ($products as $product): ?>
                <td style="padding:16px 20px;text-align:center;font-size:14px;color:var(--text-light);line-height:1.6"><?php echo escape(substr($product['description_' . $lang], 0, 100)); ?>...</td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td style="padding:20px;font-weight:600"></td>
                <?php foreach ($products as $product): ?>
                <td style="padding:20px;text-align:center">
                    <button onclick="addToCart(<?php echo $product['id']; ?>)" style="width:100%;padding:12px;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;border:none;border-radius:10px;cursor:pointer;font-weight:600;transition:0.3s" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 16px rgba(59,130,246,0.4)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">
                        <i class="bi bi-cart-plus"></i> أضف للسلة
                    </button>
                    <a href="product.php?id=<?php echo $product['id']; ?>" style="display:block;margin-top:10px;color:var(--primary);text-decoration:none;font-size:14px;font-weight:600">
                        <i class="bi bi-eye"></i> عرض التفاصيل
                    </a>
                </td>
                <?php endforeach; ?>
            </tr>
        </tbody>
    </table>
</div>

<div style="margin-top:40px;text-align:center">
    <a href="products.php" style="display:inline-block;padding:14px 32px;background:white;border:2px solid var(--primary);color:var(--primary);border-radius:50px;text-decoration:none;font-weight:600;transition:0.3s" onmouseover="this.style.background='var(--primary)';this.style.color='white'" onmouseout="this.style.background='white';this.style.color='var(--primary)'">
        <i class="bi bi-plus-circle"></i> إضافة منتجات للمقارنة
    </a>
</div>

<?php endif; ?>

</div>
</section>

<script>
function removeFromCompare(id) {
    fetch('api/compare.php?action=remove&product_id=' + id, { method: 'POST' })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert('حدث خطأ');
    });
}

function addToCart(id) {
    fetch('api/cart.php?action=add&product_id=' + id + '&quantity=1', { method: 'POST' })
    .then(r => r.json())
    .then(data => {
        if (data.success) alert('تم إضافة المنتج للسلة');
        else alert('حدث خطأ');
    });
}
</script>

<?php include 'includes/footer.php'; ?>