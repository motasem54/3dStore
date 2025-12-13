<?php
require_once '../../includes/store-init.php';
if (!isAdmin()) redirect('/admin/login.php');

$page_title = 'توليد نماذج 3D';

$filter = $_GET['filter'] ?? 'all';

$where = "image_path IS NOT NULL AND status = 'active'";
if ($filter === 'no_3d') {
    $where .= " AND (model_3d_status = 'none' OR model_3d_status = 'failed')";
} elseif ($filter === 'processing') {
    $where .= " AND model_3d_status = 'processing'";
} elseif ($filter === 'completed') {
    $where .= " AND model_3d_status = 'completed'";
}

$products = $db->fetchAll("SELECT * FROM products WHERE $where ORDER BY created_at DESC LIMIT 50");

$stats = [
    'total' => $db->fetchOne("SELECT COUNT(*) as c FROM products WHERE image_path IS NOT NULL")['c'],
    'no_3d' => $db->fetchOne("SELECT COUNT(*) as c FROM products WHERE image_path IS NOT NULL AND (model_3d_status = 'none' OR model_3d_status = 'failed')")['c'],
    'processing' => $db->fetchOne("SELECT COUNT(*) as c FROM products WHERE model_3d_status = 'processing'")['c'],
    'completed' => $db->fetchOne("SELECT COUNT(*) as c FROM products WHERE model_3d_status = 'completed'")['c']
];

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-magic text-primary"></i> توليد نماذج 3D</h2>
    <div>
        <button class="btn btn-primary" onclick="selectAll()">
            <i class="bi bi-check-all"></i> تحديد الكل
        </button>
        <button class="btn btn-success" onclick="generateSelected()">
            <i class="bi bi-play-circle"></i> توليد المحدد
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">الإجمالي</div>
                        <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                    </div>
                    <i class="bi bi-box-seam" style="font-size:2rem;opacity:0.2"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="border-right:3px solid var(--warning) !important">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">بدون 3D</div>
                        <h3 class="mb-0 text-warning"><?php echo $stats['no_3d']; ?></h3>
                    </div>
                    <a href="?filter=no_3d" class="btn btn-sm btn-outline-warning">عرض</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="border-right:3px solid var(--info) !important">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">قيد المعالجة</div>
                        <h3 class="mb-0 text-info"><?php echo $stats['processing']; ?></h3>
                    </div>
                    <a href="?filter=processing" class="btn btn-sm btn-outline-info">عرض</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="border-right:3px solid var(--success) !important">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">مكتمل</div>
                        <h3 class="mb-0 text-success"><?php echo $stats['completed']; ?></h3>
                    </div>
                    <a href="?filter=completed" class="btn btn-sm btn-outline-success">عرض</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50"><input type="checkbox" id="select-all" onclick="toggleAll(this)"></th>
                        <th>المنتج</th>
                        <th>الصورة</th>
                        <th>الحالة</th>
                        <th>التقدم</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr data-id="<?php echo $product['id']; ?>">
                        <td><input type="checkbox" class="product-checkbox" value="<?php echo $product['id']; ?>"></td>
                        <td>
                            <strong><?php echo escape($product['name_ar']); ?></strong><br>
                            <small class="text-muted">#<?php echo $product['id']; ?></small>
                        </td>
                        <td>
                            <?php if ($product['image_path']): ?>
                            <img src="<?php echo UPLOAD_URL . '/products/' . $product['image_path']; ?>" style="width:60px;height:60px;object-fit:cover;border-radius:8px">
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $badges = [
                                'none' => ['secondary', 'بدون 3D'],
                                'processing' => ['info', 'قيد المعالجة'],
                                'completed' => ['success', 'مكتمل'],
                                'failed' => ['danger', 'فشل']
                            ];
                            $status = $badges[$product['model_3d_status']] ?? $badges['none'];
                            ?>
                            <span class="badge bg-<?php echo $status[0]; ?>"><?php echo $status[1]; ?></span>
                        </td>
                        <td>
                            <div class="progress" style="height:20px;display:none" id="progress-<?php echo $product['id']; ?>">
                                <div class="progress-bar" role="progressbar" style="width:0%">0%</div>
                            </div>
                            <span id="status-text-<?php echo $product['id']; ?>">-</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <?php if ($product['model_3d_status'] === 'completed'): ?>
                                <a href="/uploads/models/<?php echo $product['model_3d_path']; ?>" class="btn btn-outline-success" download>
                                    <i class="bi bi-download"></i>
                                </a>
                                <?php endif; ?>
                                <button class="btn btn-outline-primary" onclick="generateSingle(<?php echo $product['id']; ?>)" <?php echo $product['model_3d_status'] === 'processing' ? 'disabled' : ''; ?>>
                                    <i class="bi bi-play"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleAll(checkbox) {
    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = checkbox.checked);
}

function selectAll() {
    document.getElementById('select-all').checked = true;
    toggleAll(document.getElementById('select-all'));
}

function generateSelected() {
    const selected = Array.from(document.querySelectorAll('.product-checkbox:checked')).map(cb => cb.value);
    
    if (selected.length === 0) {
        alert('اختر منتج واحد على الأقل');
        return;
    }
    
    if (!confirm(`سيتم توليد 3D لـ ${selected.length} منتج. متابعة؟`)) return;
    
    selected.forEach((id, index) => {
        setTimeout(() => generateSingle(id), index * 1000);
    });
}

function generateSingle(productId) {
    const row = document.querySelector(`tr[data-id="${productId}"]`);
    const progressBar = document.getElementById(`progress-${productId}`);
    const statusText = document.getElementById(`status-text-${productId}`);
    
    progressBar.style.display = 'block';
    statusText.textContent = 'جاري الإرسال...';
    
    fetch('/api/meshy-3d.php?action=generate', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `product_id=${productId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            statusText.textContent = 'تم الإرسال. جاري المعالجة...';
            checkStatus(productId);
        } else {
            statusText.textContent = 'خطأ: ' + data.error;
            progressBar.style.display = 'none';
        }
    })
    .catch(err => {
        statusText.textContent = 'خطأ في الاتصال';
        console.error(err);
    });
}

function checkStatus(productId) {
    const progressBar = document.getElementById(`progress-${productId}`);
    const statusText = document.getElementById(`status-text-${productId}`);
    
    const interval = setInterval(() => {
        fetch(`/api/meshy-3d.php?action=check_status&product_id=${productId}`)
        .then(r => r.json())
        .then(data => {
            if (data.status === 'completed') {
                clearInterval(interval);
                progressBar.querySelector('.progress-bar').style.width = '100%';
                progressBar.querySelector('.progress-bar').textContent = '100%';
                progressBar.classList.add('bg-success');
                statusText.textContent = 'تم بنجاح!';
                setTimeout(() => location.reload(), 2000);
            } else if (data.status === 'failed') {
                clearInterval(interval);
                progressBar.classList.add('bg-danger');
                statusText.textContent = 'فشل';
            } else {
                const progress = data.progress || 0;
                progressBar.querySelector('.progress-bar').style.width = progress + '%';
                progressBar.querySelector('.progress-bar').textContent = progress + '%';
                statusText.textContent = 'جاري المعالجة...';
            }
        });
    }, 10000); // Check every 10 seconds
}
</script>

<?php include '../includes/footer.php'; ?>