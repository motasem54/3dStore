<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

$page_title = 'إدارة المنتجات';
$active_page = 'products';

// Filters
$type_filter = $_GET['type'] ?? 'all';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = $_GET['search'] ?? '';

$where = [];
$params = [];

if ($type_filter !== 'all') {
    $where[] = "p.product_type = ?";
    $params[] = $type_filter;
}

if ($category_filter > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $category_filter;
}

if (!empty($search)) {
    $where[] = "(p.name_ar LIKE ? OR p.name_en LIKE ? OR p.sku LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$total = $db->fetch("SELECT COUNT(*) as count FROM products p {$whereClause}", $params)['count'] ?? 0;
$totalPages = ceil($total / $perPage);

$products = $db->fetchAll(
    "SELECT p.*, c.name_ar as category_name 
     FROM products p 
     LEFT JOIN categories c ON p.category_id = c.id 
     {$whereClause}
     ORDER BY p.created_at DESC 
     LIMIT {$perPage} OFFSET {$offset}",
    $params
);

$categories = $db->fetchAll("SELECT id, name_ar FROM categories WHERE status = 'active' ORDER BY name_ar");

include __DIR__ . '/../includes/header.php';
?>

<div class="glass-card">
    <div class="card-header space-between">
        <h4><i class="bi bi-box-seam"></i> إدارة المنتجات (<?php echo number_format($total); ?>)</h4>
        <a href="add.php" class="btn-primary">
            <i class="bi bi-plus-circle"></i> إضافة منتج
        </a>
    </div>
    
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" class="filters-form">
            <div class="filters-row">
                <div class="form-group">
                    <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو SKU..." value="<?php echo escape($search); ?>">
                </div>
                
                <div class="form-group">
                    <select name="type" class="form-control">
                        <option value="all">جميع الأنواع</option>
                        <option value="normal" <?php echo $type_filter === 'normal' ? 'selected' : ''; ?>>عادي</option>
                        <option value="3d" <?php echo $type_filter === '3d' ? 'selected' : ''; ?>>ثلاثي الأبعاد</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <select name="category" class="form-control">
                        <option value="0">جميع التصنيفات</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter === (int)$cat['id'] ? 'selected' : ''; ?>>
                            <?php echo escape($cat['name_ar']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn-primary"><i class="bi bi-search"></i></button>
                <a href="index.php" class="btn-sm"><i class="bi bi-x-circle"></i></a>
            </div>
        </form>
        
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card glass-card">
                <div class="product-image">
                    <?php if ($product['image_path']): ?>
                    <img src="<?php echo UPLOAD_URL . '/products/' . $product['image_path']; ?>" alt="">
                    <?php else: ?>
                    <div class="no-image"><i class="bi bi-image"></i></div>
                    <?php endif; ?>
                    
                    <?php if ($product['product_type'] === '3d'): ?>
                    <span class="type-badge badge-3d"><i class="bi bi-badge-3d"></i> 3D</span>
                    <?php endif; ?>
                </div>
                
                <div class="product-info">
                    <h5><?php echo escape($product['name_ar']); ?></h5>
                    <p class="text-muted">SKU: <?php echo escape($product['sku']); ?></p>
                    <div class="product-meta">
                        <span class="price"><?php echo formatPrice($product['price_ils']); ?></span>
                        <span class="stock <?php echo $product['stock_quantity'] <= $product['reorder_level'] ? 'low' : ''; ?>">
                            <i class="bi bi-box"></i> <?php echo number_format($product['stock_quantity']); ?>
                        </span>
                    </div>
                    <div class="product-actions">
                        <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn-sm"><i class="bi bi-pencil"></i> تعديل</a>
                        <?php if ($product['product_type'] === 'normal' && empty($product['model_3d_path'])): ?>
                        <a href="convert-3d.php?id=<?php echo $product['id']; ?>" class="btn-sm btn-info" title="تحويل إلى 3D">
                            <i class="bi bi-magic"></i> 3D
                        </a>
                        <?php endif; ?>
                        <a href="delete.php?id=<?php echo $product['id']; ?>" class="btn-sm btn-danger" data-confirm="حذف هذا المنتج؟">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($products)): ?>
            <div class="empty-state">
                <i class="bi bi-inbox" style="font-size: 64px; opacity: 0.3;"></i>
                <p>لا توجد منتجات</p>
                <a href="add.php" class="btn-primary">إضافة أول منتج</a>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&type=<?php echo $type_filter; ?>&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search); ?>" class="btn-sm">السابق</a>
            <?php endif; ?>
            <span class="page-info">صفحة <?php echo $page; ?> من <?php echo $totalPages; ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&type=<?php echo $type_filter; ?>&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search); ?>" class="btn-sm">التالي</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.filters-form { margin-bottom: 20px; }
.filters-row { display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; }
.filters-row .form-group { flex: 1; min-width: 180px; }
.products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-top: 20px; }
.product-card { padding: 0; overflow: hidden; }
.product-image { height: 200px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; position: relative; }
.product-image img { width: 100%; height: 100%; object-fit: cover; }
.no-image { font-size: 48px; color: var(--text-muted); }
.type-badge { position: absolute; top: 10px; right: 10px; }
.badge-3d { background: linear-gradient(135deg, var(--info), var(--primary)); }
.product-info { padding: 16px; }
.product-info h5 { font-size: 16px; margin: 0 0 8px 0; }
.product-meta { display: flex; justify-content: space-between; align-items: center; margin: 12px 0; }
.price { font-size: 18px; font-weight: 700; color: var(--success); }
.stock { display: flex; align-items: center; gap: 4px; font-size: 14px; }
.stock.low { color: var(--warning); }
.product-actions { display: flex; gap: 8px; }
.btn-info { background: rgba(59, 130, 246, 0.2); border-color: rgba(59, 130, 246, 0.4); }
.btn-danger { background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4); }
.empty-state { grid-column: 1 / -1; text-align: center; padding: 60px 20px; }
.pagination { display: flex; gap: 12px; align-items: center; justify-content: center; margin-top: 20px; }
.page-info { color: var(--text-muted); font-size: 14px; }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>