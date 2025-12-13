<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

$page_title = 'إدارة التصنيفات';
$active_page = 'categories';

$categories = $db->fetchAll(
    "SELECT c.*, COUNT(p.id) as products_count 
     FROM categories c 
     LEFT JOIN products p ON c.id = p.category_id 
     GROUP BY c.id 
     ORDER BY c.created_at DESC"
);

include __DIR__ . '/../includes/header.php';
?>

<div class="glass-card">
    <div class="card-header space-between">
        <h4><i class="bi bi-grid"></i> إدارة التصنيفات (<?php echo count($categories); ?>)</h4>
        <a href="add.php" class="btn-primary"><i class="bi bi-plus-circle"></i> إضافة تصنيف</a>
    </div>
    
    <div class="card-body">
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
            <div class="category-card glass-card">
                <?php if ($category['image_path']): ?>
                <div class="category-image">
                    <img src="<?php echo UPLOAD_URL . '/categories/' . $category['image_path']; ?>" alt="">
                </div>
                <?php endif; ?>
                <div class="category-info">
                    <h5><?php echo escape($category['name_ar']); ?></h5>
                    <p class="text-muted"><?php echo escape($category['name_en']); ?></p>
                    <div class="category-meta">
                        <span><i class="bi bi-box"></i> <?php echo number_format($category['products_count']); ?> منتج</span>
                        <?php if ($category['status'] === 'active'): ?>
                            <span class="badge badge-success">نشط</span>
                        <?php else: ?>
                            <span class="badge badge-warning">معطل</span>
                        <?php endif; ?>
                    </div>
                    <div class="category-actions">
                        <a href="edit.php?id=<?php echo $category['id']; ?>" class="btn-sm"><i class="bi bi-pencil"></i> تعديل</a>
                        <a href="delete.php?id=<?php echo $category['id']; ?>" class="btn-sm btn-danger" data-confirm="حذف هذا التصنيف؟">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($categories)): ?>
            <div class="empty-state">
                <i class="bi bi-inbox" style="font-size: 64px; opacity: 0.3;"></i>
                <p>لا توجد تصنيفات</p>
                <a href="add.php" class="btn-primary">إضافة أول تصنيف</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.categories-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
.category-card { padding: 0; overflow: hidden; }
.category-image { height: 150px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; }
.category-image img { width: 100%; height: 100%; object-fit: cover; }
.category-info { padding: 16px; }
.category-info h5 { font-size: 16px; margin: 0 0 4px 0; }
.category-meta { display: flex; justify-content: space-between; align-items: center; margin: 12px 0; font-size: 14px; }
.category-actions { display: flex; gap: 8px; }
.empty-state { grid-column: 1 / -1; text-align: center; padding: 60px 20px; }
.btn-danger { background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4); }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>