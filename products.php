<?php
require_once 'includes/store-init.php';
$page_title = 'المنتجات';

$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$min_price = $_GET['min_price'] ?? 0;
$max_price = $_GET['max_price'] ?? 10000;
$page_num = $_GET['page'] ?? 1;
$per_page = 20;
$offset = ($page_num - 1) * $per_page;

$where = ["status = 'active'"];
$params = [];

if ($category) {
    $where[] = "category_id = ?";
    $params[] = $category;
}

if ($search) {
    $where[] = "(name_ar LIKE ? OR name_en LIKE ? OR description_ar LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($min_price > 0) {
    $where[] = "price >= ?";
    $params[] = $min_price;
}

if ($max_price < 10000) {
    $where[] = "price <= ?";
    $params[] = $max_price;
}

$where_clause = implode(' AND ', $where);

$order_by = match($sort) {
    'price_low' => 'price ASC',
    'price_high' => 'price DESC',
    'name' => 'name_' . $lang . ' ASC',
    'popular' => 'views DESC',
    default => 'created_at DESC'
};

$total = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE $where_clause", $params)['count'];
$products = $db->fetchAll("SELECT * FROM products WHERE $where_clause ORDER BY $order_by LIMIT ? OFFSET ?", array_merge($params, [$per_page, $offset]));
$categories = $db->fetchAll("SELECT * FROM categories WHERE status = 'active' ORDER BY name_" . $lang);

$total_pages = ceil($total / $per_page);

include 'includes/header.php';
?>

<section style="padding:80px 0;background:var(--bg-light)">
<div class="container">

<div style="display:grid;grid-template-columns:280px 1fr;gap:30px">
    
    <!-- Filters Sidebar -->
    <aside style="position:sticky;top:100px;height:fit-content">
        <div style="background:white;border-radius:16px;padding:24px;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
            <h3 style="font-size:20px;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:8px"><i class="bi bi-funnel" style="color:var(--primary)"></i> الفلاتر</h3>
            
            <form method="GET" id="filterForm">
                <!-- Search -->
                <div style="margin-bottom:24px">
                    <label style="display:block;font-weight:600;margin-bottom:10px;font-size:14px">البحث</label>
                    <input type="text" name="search" value="<?php echo escape($search); ?>" placeholder="ابحث عن منتج..." style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:14px">
                </div>
                
                <!-- Categories -->
                <div style="margin-bottom:24px">
                    <label style="display:block;font-weight:600;margin-bottom:10px;font-size:14px">الفئة</label>
                    <select name="category" style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:14px">
                        <option value="">جميع الفئات</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>><?php echo escape($cat['name_' . $lang]); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Price Range -->
                <div style="margin-bottom:24px">
                    <label style="display:block;font-weight:600;margin-bottom:10px;font-size:14px">السعر</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                        <input type="number" name="min_price" value="<?php echo $min_price; ?>" placeholder="من" style="padding:10px;border:1px solid var(--border);border-radius:10px;font-size:14px">
                        <input type="number" name="max_price" value="<?php echo $max_price; ?>" placeholder="إلى" style="padding:10px;border:1px solid var(--border);border-radius:10px;font-size:14px">
                    </div>
                </div>
                
                <!-- Sort -->
                <div style="margin-bottom:24px">
                    <label style="display:block;font-weight:600;margin-bottom:10px;font-size:14px">الترتيب</label>
                    <select name="sort" style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:14px">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>الأحدث</option>
                        <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>الأكثر مشاهدة</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>السعر: من الأقل للأعلى</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>السعر: من الأعلى للأقل</option>
                        <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>الاسم</option>
                    </select>
                </div>
                
                <button type="submit" style="width:100%;padding:12px;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer;transition:0.3s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="bi bi-search"></i> تطبيق الفلاتر
                </button>
                
                <?php if ($search || $category || $min_price > 0 || $max_price < 10000): ?>
                <a href="products.php" style="display:block;text-align:center;margin-top:12px;color:var(--danger);text-decoration:none;font-size:14px;font-weight:600">
                    <i class="bi bi-x-circle"></i> مسح الفلاتر
                </a>
                <?php endif; ?>
            </form>
        </div>
    </aside>
    
    <!-- Products Grid -->
    <main>
        <div style="background:white;border-radius:16px;padding:24px;margin-bottom:24px;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
                <h1 style="font-size:28px;font-weight:700;margin:0">
                    <?php echo $category ? escape($categories[array_search($category, array_column($categories, 'id'))]['name_' . $lang] ?? 'المنتجات') : 'جميع المنتجات'; ?>
                </h1>
                <div style="color:var(--text-light);font-size:15px">
                    <strong><?php echo $total; ?></strong> منتج
                </div>
            </div>
        </div>
        
        <?php if (empty($products)): ?>
        <div style="background:white;border-radius:16px;padding:60px;text-align:center;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
            <div style="width:120px;height:120px;background:var(--bg-light);border-radius:50%;margin:0 auto 24px;display:flex;align-items:center;justify-content:center">
                <i class="bi bi-inbox" style="font-size:60px;color:var(--text-light)"></i>
            </div>
            <h3 style="font-size:24px;margin-bottom:12px;color:var(--dark)">لا توجد منتجات</h3>
            <p style="color:var(--text-light);margin-bottom:24px">جرب تغيير معايير البحث</p>
            <a href="products.php" style="display:inline-block;padding:12px 24px;background:var(--primary);color:white;border-radius:10px;text-decoration:none;font-weight:600">عرض جميع المنتجات</a>
        </div>
        <?php else: ?>
        
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:20px">
            <?php foreach ($products as $product): ?>
            <div class="product-card" style="background:white;border:1px solid var(--border);border-radius:12px;overflow:hidden;transition:all 0.3s;display:flex;flex-direction:column;height:100%" onmouseover="this.style.transform='translateY(-5px)';this.style.boxShadow='0 10px 30px rgba(0,0,0,0.1)';this.style.borderColor='var(--primary)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none';this.style.borderColor='var(--border)'">
                <div style="width:100%;height:200px;overflow:hidden;position:relative;background:#f8f9fa">
                    <a href="product.php?id=<?php echo $product['id']; ?>">
                        <?php if ($product['image_path']): ?>
                        <img src="<?php echo UPLOAD_URL . '/products/' . $product['image_path']; ?>" style="width:100%;height:100%;object-fit:cover;transition:0.3s" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        <?php else: ?>
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center"><i class="bi bi-image" style="font-size:48px;color:var(--text-light)"></i></div>
                        <?php endif; ?>
                    </a>
                    <?php if ($product['discount_price']): ?>
                    <span style="position:absolute;top:12px;right:12px;background:var(--danger);color:white;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700"><?php echo round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>%-</span>
                    <?php endif; ?>
                    <div style="position:absolute;top:12px;left:12px;display:flex;flex-direction:column;gap:6px;opacity:0;transition:0.3s" class="product-actions">
                        <button onclick="addToWishlist(<?php echo $product['id']; ?>)" style="width:32px;height:32px;background:white;border:none;border-radius:8px;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,0.1);transition:0.3s" onmouseover="this.style.background='var(--primary)';this.style.color='white'" onmouseout="this.style.background='white';this.style.color=''"><i class="bi bi-heart"></i></button>
                        <button onclick="quickView(<?php echo $product['id']; ?>)" style="width:32px;height:32px;background:white;border:none;border-radius:8px;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,0.1);transition:0.3s" onmouseover="this.style.background='var(--primary)';this.style.color='white'" onmouseout="this.style.background='white';this.style.color=''"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div style="padding:14px;flex:1;display:flex;flex-direction:column">
                    <a href="product.php?id=<?php echo $product['id']; ?>" style="font-size:14px;font-weight:600;color:var(--dark);margin-bottom:8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;height:40px;line-height:20px;text-decoration:none"><?php echo escape($product['name_' . $lang]); ?></a>
                    <div style="display:flex;align-items:center;gap:4px;margin-bottom:8px;font-size:12px">
                        <div style="color:#fbbf24;display:flex;gap:2px">
                            <?php for($i=0;$i<5;$i++): ?>
                            <i class="bi bi-star<?php echo $i < 4 ? '-fill' : ''; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span style="color:var(--text-light);font-size:11px">(<?php echo rand(10, 200); ?>)</span>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;gap:8px">
                        <div style="display:flex;align-items:center;gap:6px;flex-direction:row-reverse;flex-wrap:wrap">
                            <span style="font-size:18px;font-weight:700;color:var(--primary)"><?php echo formatPrice($product['discount_price'] ?: $product['price']); ?></span>
                            <?php if ($product['discount_price']): ?>
                            <span style="font-size:13px;color:var(--text-light);text-decoration:line-through"><?php echo formatPrice($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        <button onclick="addToCart(<?php echo $product['id']; ?>)" style="padding:6px 12px;background:var(--primary);color:white;border:none;border-radius:8px;cursor:pointer;font-size:12px;font-weight:600;transition:0.3s;white-space:nowrap" onmouseover="this.style.background='var(--secondary)';this.style.transform='scale(1.05)'" onmouseout="this.style.background='var(--primary)';this.style.transform='scale(1)'">أضف</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div style="display:flex;justify-content:center;gap:8px;margin-top:40px;flex-wrap:wrap">
            <?php if ($page_num > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page_num - 1])); ?>" style="padding:10px 16px;background:white;border:1px solid var(--border);border-radius:8px;text-decoration:none;color:var(--text);font-weight:600;transition:0.3s" onmouseover="this.style.background='var(--primary)';this.style.color='white';this.style.borderColor='var(--primary)'" onmouseout="this.style.background='white';this.style.color='var(--text)';this.style.borderColor='var(--border)'"><i class="bi bi-chevron-right"></i></a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page_num - 2); $i <= min($total_pages, $page_num + 2); $i++): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" style="padding:10px 16px;background:<?php echo $i === $page_num ? 'var(--primary)' : 'white'; ?>;border:1px solid <?php echo $i === $page_num ? 'var(--primary)' : 'var(--border)'; ?>;border-radius:8px;text-decoration:none;color:<?php echo $i === $page_num ? 'white' : 'var(--text)'; ?>;font-weight:600;transition:0.3s" <?php if ($i !== $page_num): ?>onmouseover="this.style.background='var(--primary)';this.style.color='white';this.style.borderColor='var(--primary)'" onmouseout="this.style.background='white';this.style.color='var(--text)';this.style.borderColor='var(--border)'"<?php endif; ?>><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php if ($page_num < $total_pages): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page_num + 1])); ?>" style="padding:10px 16px;background:white;border:1px solid var(--border);border-radius:8px;text-decoration:none;color:var(--text);font-weight:600;transition:0.3s" onmouseover="this.style.background='var(--primary)';this.style.color='white';this.style.borderColor='var(--primary)'" onmouseout="this.style.background='white';this.style.color='var(--text)';this.style.borderColor='var(--border)'"><i class="bi bi-chevron-left"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
    </main>
    
</div>

</div>
</section>

<style>
.product-card:hover .product-actions { opacity: 1 !important; }
@media (max-width: 768px) {
    .container > div { grid-template-columns: 1fr !important; }
    aside { position: static !important; }
}
</style>

<script>
function addToCart(id) { alert('تم إضافة المنتج للسلة'); }
function addToWishlist(id) { alert('تم إضافة المنتج للمفضلة'); }
function quickView(id) { window.location.href = 'product.php?id=' + id; }
</script>

<?php include 'includes/footer.php'; ?>