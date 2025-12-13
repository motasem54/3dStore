<?php
require_once 'includes/store-init.php';
$page_title = 'المفضلة';

if (!isLoggedIn()) {
    redirect('/account/login.php?redirect=wishlist.php');
}

$user_id = $_SESSION['user_id'];
$wishlist_items = $db->fetchAll("
    SELECT w.*, p.*, p.id as product_id 
    FROM wishlist w 
    JOIN products p ON w.product_id = p.id 
    WHERE w.user_id = ? AND p.status = 'active'
    ORDER BY w.created_at DESC
", [$user_id]);

include 'includes/header.php';
?>

<section style="padding:80px 0;background:var(--bg-light);min-height:calc(100vh - 200px)">
<div class="container">

<div style="background:white;border-radius:16px;padding:30px;margin-bottom:30px;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
    <h1 style="font-size:32px;font-weight:700;margin-bottom:8px;display:flex;align-items:center;gap:12px">
        <i class="bi bi-heart-fill" style="color:var(--danger)"></i> المفضلة
    </h1>
    <p style="color:var(--text-light);font-size:15px">المنتجات التي أضفتها إلى قائمة المفضلة</p>
</div>

<?php if (empty($wishlist_items)): ?>
<div style="background:white;border-radius:16px;padding:80px 40px;text-align:center;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
    <div style="width:140px;height:140px;background:linear-gradient(135deg,rgba(239,68,68,0.1),rgba(239,68,68,0.05));border-radius:50%;margin:0 auto 24px;display:flex;align-items:center;justify-content:center">
        <i class="bi bi-heart" style="font-size:70px;color:var(--danger)"></i>
    </div>
    <h3 style="font-size:26px;margin-bottom:12px;color:var(--dark)">قائمة المفضلة فارغة</h3>
    <p style="color:var(--text-light);margin-bottom:30px;font-size:16px">لم تقم بإضافة أي منتجات إلى المفضلة بعد</p>
    <a href="products.php" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;border-radius:50px;text-decoration:none;font-weight:600;transition:0.3s" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
        <i class="bi bi-shop"></i> تصفح المنتجات
    </a>
</div>
<?php else: ?>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:24px">
    <?php foreach ($wishlist_items as $item): ?>
    <div style="background:white;border:1px solid var(--border);border-radius:16px;overflow:hidden;transition:all 0.3s;position:relative;display:flex;flex-direction:column" onmouseover="this.style.transform='translateY(-8px)';this.style.boxShadow='0 10px 30px rgba(0,0,0,0.12)';this.style.borderColor='var(--primary)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none';this.style.borderColor='var(--border)'">
        
        <button onclick="removeFromWishlist(<?php echo $item['product_id']; ?>)" style="position:absolute;top:12px;left:12px;width:36px;height:36px;background:rgba(255,255,255,0.95);border:none;border-radius:50%;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,0.15);z-index:10;transition:0.3s" onmouseover="this.style.background='var(--danger)';this.style.color='white';this.style.transform='scale(1.1)'" onmouseout="this.style.background='rgba(255,255,255,0.95)';this.style.color='';this.style.transform='scale(1)'">
            <i class="bi bi-x-lg"></i>
        </button>
        
        <div style="width:100%;height:220px;overflow:hidden;position:relative;background:#f8f9fa">
            <a href="product.php?id=<?php echo $item['product_id']; ?>">
                <?php if ($item['image_path']): ?>
                <img src="<?php echo UPLOAD_URL . '/products/' . $item['image_path']; ?>" style="width:100%;height:100%;object-fit:cover;transition:0.3s" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                <?php else: ?>
                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center"><i class="bi bi-image" style="font-size:50px;color:var(--text-light)"></i></div>
                <?php endif; ?>
            </a>
            <?php if ($item['discount_price']): ?>
            <span style="position:absolute;top:12px;right:12px;background:var(--danger);color:white;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:700"><?php echo round((($item['price'] - $item['discount_price']) / $item['price']) * 100); ?>%-</span>
            <?php endif; ?>
        </div>
        
        <div style="padding:18px;flex:1;display:flex;flex-direction:column">
            <a href="product.php?id=<?php echo $item['product_id']; ?>" style="font-size:15px;font-weight:600;color:var(--dark);margin-bottom:10px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;text-decoration:none;line-height:1.4"><?php echo escape($item['name_' . $lang]); ?></a>
            
            <div style="display:flex;align-items:center;gap:4px;margin-bottom:12px;font-size:13px">
                <div style="color:#fbbf24;display:flex;gap:2px">
                    <?php for($i=0;$i<5;$i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                </div>
                <span style="color:var(--text-light);font-size:12px">(4.5)</span>
            </div>
            
            <div style="margin-top:auto">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;flex-direction:row-reverse">
                    <span style="font-size:22px;font-weight:700;color:var(--primary)"><?php echo formatPrice($item['discount_price'] ?: $item['price']); ?></span>
                    <?php if ($item['discount_price']): ?>
                    <span style="font-size:15px;color:var(--text-light);text-decoration:line-through"><?php echo formatPrice($item['price']); ?></span>
                    <?php endif; ?>
                </div>
                
                <button onclick="addToCart(<?php echo $item['product_id']; ?>)" style="width:100%;padding:12px;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;border:none;border-radius:10px;cursor:pointer;font-weight:600;transition:0.3s" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 16px rgba(59,130,246,0.4)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">
                    <i class="bi bi-cart-plus"></i> أضف للسلة
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div style="margin-top:40px;text-align:center">
    <a href="products.php" style="display:inline-block;padding:14px 32px;background:white;border:2px solid var(--primary);color:var(--primary);border-radius:50px;text-decoration:none;font-weight:600;transition:0.3s" onmouseover="this.style.background='var(--primary)';this.style.color='white'" onmouseout="this.style.background='white';this.style.color='var(--primary)'">
        <i class="bi bi-plus-circle"></i> إضافة منتجات أخرى
    </a>
</div>

<?php endif; ?>

</div>
</section>

<script>
function removeFromWishlist(id) {
    if (confirm('هل تريد إزالة هذا المنتج من المفضلة؟')) {
        fetch('api/wishlist.php?action=remove&product_id=' + id, { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
            else alert('حدث خطأ');
        });
    }
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