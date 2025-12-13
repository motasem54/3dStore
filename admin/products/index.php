<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

$page_title = 'إدارة المنتجات';
$active_page = 'products';

$search = $_GET['search'] ?? '';
$type_filter = $_GET['type'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(p.name_ar LIKE ? OR p.name_en LIKE ? OR p.sku LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($type_filter) {
    $where[] = "p.product_type = ?";
    $params[] = $type_filter;
}

if ($status_filter) {
    $where[] = "p.status = ?";
    $params[] = $status_filter;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$products = $db->fetchAll(
    "SELECT p.*, c.name_ar as category_name 
     FROM products p 
     LEFT JOIN categories c ON p.category_id = c.id 
     $whereClause
     ORDER BY p.created_at DESC",
    $params
);

include __DIR__ . '/../includes/header.php';
?>

<div class="glass-card">
    <div class="card-header space-between">
        <h4><i class="bi bi-box-seam"></i> جميع المنتجات</h4>
        <a href="add.php" class="btn-primary"><i class="bi bi-plus-circle"></i> إضافة منتج</a>
    </div>
    
    <div class="card-body">
        <form method="GET" class="filters" style="display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap;">
            <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو SKU..." value="<?php echo escape($search); ?>" style="max-width: 300px;">
            
            <select name="type" class="form-control" style="max-width: 150px;">
                <option value="">كل الأنواع</option>
                <option value="normal" <?php echo $type_filter === 'normal' ? 'selected' : ''; ?>>عادي</option>
                <option value="3d" <?php echo $type_filter === '3d' ? 'selected' : ''; ?>>3D</option>
            </select>
            
            <select name="status" class="form-control" style="max-width: 150px;">
                <option value="">كل الحالات</option>
                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>نشط</option>
                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>غير نشط</option>
                <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>مسودة</option>
            </select>
            
            <button type="submit" class="btn-primary"><i class="bi bi-search"></i> بحث</button>
            <a href="index.php" class="btn-sm"><i class="bi bi-x-circle"></i> إلغاء</a>
        </form>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>الصورة</th>
                        <th>SKU</th>
                        <th>المنتج</th>
                        <th>النوع</th>
                        <th>السعر</th>
                        <th>المخزون</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                            <i class="bi bi-inbox" style="font-size: 48px; display: block; margin-bottom: 16px; opacity: 0.3;"></i>
                            لا توجد منتجات
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <?php if ($product['image_path']): ?>
                            <img src="<?php echo UPLOAD_URL . '/products/' . $product['image_path']; ?>" 
                                 alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                            <?php else: ?>
                            <div style="width: 50px; height: 50px; background: rgba(255,255,255,0.05); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-image" style="color: var(--text-secondary);"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo escape($product['sku']); ?></td>
                        <td>
                            <strong><?php echo escape($product['name_ar']); ?></strong>
                            <?php if ($product['category_name']): ?>
                            <br><small style="color: var(--text-secondary);"><?php echo escape($product['category_name']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($product['product_type'] === '3d'): ?>
                            <span class="badge badge-info"><i class="bi bi-box"></i> 3D</span>
                            <?php else: ?>
                            <span class="badge" style="background: rgba(255,255,255,0.1); color: var(--text-secondary);">عادي</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo number_format($product['price_ils'], 2); ?> ₪</strong>
                        </td>
                        <td>
                            <?php if ($product['stock_quantity'] <= $product['reorder_level']): ?>
                            <span class="badge badge-danger"><?php echo $product['stock_quantity']; ?></span>
                            <?php else: ?>
                            <span class="badge badge-success"><?php echo $product['stock_quantity']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $status_class = [
                                'active' => 'badge-success',
                                'inactive' => 'badge-danger',
                                'draft' => 'badge-warning'
                            ];
                            $status_text = [
                                'active' => 'نشط',
                                'inactive' => 'غير نشط',
                                'draft' => 'مسودة'
                            ];
                            ?>
                            <span class="badge <?php echo $status_class[$product['status']] ?? ''; ?>">
                                <?php echo $status_text[$product['status']] ?? $product['status']; ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn-icon" title="تعديل">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $product['id']; ?>" 
                                   class="btn-icon" 
                                   style="color: var(--danger);" 
                                   title="حذف" 
                                   onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>