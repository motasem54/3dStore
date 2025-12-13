<?php
require_once 'includes/store-init.php';
$page_title = 'المنتجات';

$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$min_price = $_GET['min_price'] ?? 0;
$max_price = $_GET['max_price'] ?? 10000;

include 'includes/header.php';
?>

<section style="padding:80px 0;background:var(--bg-light)">
<div class="container">

<div style="display:grid;grid-template-columns:280px 1fr;gap:30px">
    
    <!-- Filters Sidebar -->
    <aside style="position:sticky;top:100px;height:fit-content">
        <div style="background:white;border-radius:16px;padding:24px;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
            <h3 style="font-size:20px;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:8px"><i class="bi bi-funnel" style="color:var(--primary)"></i> الفلاتر</h3>
            
            <div id="filters-form">
                <!-- Search -->
                <div style="margin-bottom:24px">
                    <label style="display:block;font-weight:600;margin-bottom:10px;font-size:14px">البحث</label>
                    <input type="text" id="search" value="<?php echo escape($search); ?>" placeholder="ابحث عن منتج..." style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:14px">
                </div>
                
                <!-- Categories -->
                <div style="margin-bottom:24px">
                    <label style="display:block;font-weight:600;margin-bottom:10px;font-size:14px">الفئة</label>
                    <select id="category" style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:14px">
                        <option value="">جميع الفئات</option>
                        <?php
                        $categories = $db->fetchAll("SELECT * FROM categories WHERE status = 'active' ORDER BY name_ar");
                        foreach ($categories as $cat):
                        ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>><?php echo escape($cat['name_ar']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Price Range -->
                <div style="margin-bottom:24px">
                    <label style="display:block;font-weight:600;margin-bottom:10px;font-size:14px">السعر</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                        <input type="number" id="min_price" value="<?php echo $min_price; ?>" placeholder="من" style="padding:10px;border:1px solid var(--border);border-radius:10px;font-size:14px">
                        <input type="number" id="max_price" value="<?php echo $max_price; ?>" placeholder="إلى" style="padding:10px;border:1px solid var(--border);border-radius:10px;font-size:14px">
                    </div>
                </div>
                
                <!-- Sort -->
                <div style="margin-bottom:24px">
                    <label style="display:block;font-weight:600;margin-bottom:10px;font-size:14px">الترتيب</label>
                    <select id="sort" style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:14px">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>الأحدث</option>
                        <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>الأكثر مشاهدة</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>السعر: من الأقل</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>السعر: من الأعلى</option>
                        <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>الاسم</option>
                    </select>
                </div>
                
                <button onclick="applyFilters()" style="width:100%;padding:12px;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer;transition:0.3s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="bi bi-search"></i> تطبيق الفلاتر
                </button>
                
                <button onclick="clearFilters()" style="width:100%;margin-top:10px;padding:10px;background:white;color:var(--danger);border:1px solid var(--danger);border-radius:10px;font-weight:600;cursor:pointer;transition:0.3s">
                    <i class="bi bi-x-circle"></i> مسح الفلاتر
                </button>
            </div>
        </div>
    </aside>
    
    <!-- Products Grid -->
    <main>
        <div style="background:white;border-radius:16px;padding:24px;margin-bottom:24px;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
            <h1 style="font-size:28px;font-weight:700;margin:0">جميع المنتجات</h1>
        </div>
        
        <div id="products-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:20px"></div>
    </main>
    
</div>

</div>
</section>

<style>
@media (max-width: 768px) {
    .container > div { grid-template-columns: 1fr !important; }
    aside { position: static !important; }
    #products-grid { grid-template-columns: repeat(2, 1fr) !important; }
}
</style>

<script src="/assets/js/lazy-load.js"></script>
<script>
let loader;

// Initialize with URL parameters
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    loader = new ProductsLazyLoader({
        container: '#products-grid',
        perPage: 20,
        filters: {
            category: urlParams.get('category') || '',
            search: urlParams.get('search') || '',
            sort: urlParams.get('sort') || 'newest',
            min_price: urlParams.get('min_price') || 0,
            max_price: urlParams.get('max_price') || 10000
        }
    });
});

function applyFilters() {
    const filters = {
        category: document.getElementById('category').value,
        search: document.getElementById('search').value,
        sort: document.getElementById('sort').value,
        min_price: document.getElementById('min_price').value,
        max_price: document.getElementById('max_price').value
    };
    
    // Update URL
    const params = new URLSearchParams();
    Object.keys(filters).forEach(key => {
        if (filters[key]) params.set(key, filters[key]);
    });
    window.history.pushState({}, '', '?' + params.toString());
    
    // Update loader
    loader.updateFilters(filters);
}

function clearFilters() {
    document.getElementById('search').value = '';
    document.getElementById('category').value = '';
    document.getElementById('sort').value = 'newest';
    document.getElementById('min_price').value = 0;
    document.getElementById('max_price').value = 10000;
    
    window.history.pushState({}, '', window.location.pathname);
    loader.updateFilters({});
}

function addToCart(id) {
    fetch('/api/cart.php?action=add&product_id=' + id + '&quantity=1', { method: 'POST' })
    .then(r => r.json())
    .then(data => {
        if (data.success) alert('تم إضافة المنتج للسلة');
        else alert('حدث خطأ');
    });
}
</script>

<?php include 'includes/footer.php'; ?>