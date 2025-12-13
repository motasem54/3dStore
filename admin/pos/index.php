<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

$page_title = 'نقطة البيع (POS)';
$active_page = 'pos';

$categories = $db->fetchAll("SELECT id, name_ar FROM categories WHERE status = 'active' ORDER BY name_ar");

include __DIR__ . '/../includes/header.php';
?>

<div class="pos-container">
    <!-- Products Panel -->
    <div class="pos-products">
        <div class="glass-card" style="height: 100%;">
            <div class="card-header">
                <div class="pos-search">
                    <input type="text" id="productSearch" class="form-control" placeholder="بحث عن منتج (اسم، SKU، باركود)...">
                </div>
            </div>
            
            <div class="card-body" style="padding: 16px;">
                <div class="pos-categories">
                    <button class="category-btn active" data-category="all">الكل</button>
                    <?php foreach ($categories as $cat): ?>
                    <button class="category-btn" data-category="<?php echo $cat['id']; ?>">
                        <?php echo escape($cat['name_ar']); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                
                <div id="productsGrid" class="pos-products-grid">
                    <!-- Products will be loaded here via AJAX -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cart Panel -->
    <div class="pos-cart">
        <div class="glass-card" style="height: 100%; display: flex; flex-direction: column;">
            <div class="card-header">
                <h4><i class="bi bi-cart"></i> السلة</h4>
            </div>
            
            <div class="card-body" style="flex: 1; overflow-y: auto;">
                <div id="cartItems">
                    <div class="empty-cart">
                        <i class="bi bi-cart-x" style="font-size: 64px; opacity: 0.2;"></i>
                        <p class="text-muted">السلة فارغة</p>
                    </div>
                </div>
            </div>
            
            <div class="pos-summary">
                <div class="summary-row">
                    <span>المجموع الفرعي:</span>
                    <strong id="subtotal">0.00 ₪</strong>
                </div>
                <div class="summary-row">
                    <span>الضريبة (17%):</span>
                    <strong id="tax">0.00 ₪</strong>
                </div>
                <div class="summary-row" style="border-top: 2px solid var(--glass-border); padding-top: 12px; margin-top: 12px;">
                    <span style="font-size: 18px;">المجموع الكلي:</span>
                    <strong id="total" style="font-size: 20px; color: var(--success);">0.00 ₪</strong>
                </div>
            </div>
            
            <div class="pos-actions">
                <button type="button" id="clearCart" class="btn-sm" style="flex: 1;">
                    <i class="bi bi-trash"></i> مسح
                </button>
                <button type="button" id="checkoutBtn" class="btn-primary" style="flex: 2;">
                    <i class="bi bi-cash-coin"></i> إتمام البيع
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div id="checkoutModal" class="modal" style="display: none;">
    <div class="modal-content glass-card" style="max-width: 500px;">
        <div class="card-header space-between">
            <h4><i class="bi bi-cash-coin"></i> إتمام البيع</h4>
            <button class="close-modal"><i class="bi bi-x"></i></button>
        </div>
        <div class="card-body">
            <form id="checkoutForm">
                <div class="form-group">
                    <label>اسم العميل</label>
                    <input type="text" name="customer_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>رقم الهاتف</label>
                    <input type="text" name="customer_phone" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>البريد الإلكتروني (اختياري)</label>
                    <input type="email" name="customer_email" class="form-control">
                </div>
                <div class="form-group">
                    <label>طريقة الدفع</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="cash">نقداً</option>
                        <option value="card">بطاقة</option>
                        <option value="bank_transfer">تحويل بنكي</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="summary-box">
                    <div class="summary-row">
                        <span>المجموع الكلي:</span>
                        <strong id="modalTotal" style="color: var(--success); font-size: 20px;">0.00 ₪</strong>
                    </div>
                </div>
                
                <div class="form-actions" style="margin-top: 20px;">
                    <button type="button" class="btn-sm close-modal">إلغاء</button>
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check-circle"></i> تأكيد البيع
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.pos-container { display: grid; grid-template-columns: 1fr 400px; gap: 20px; height: calc(100vh - 140px); }
.pos-products, .pos-cart { min-height: 500px; }
.pos-search { width: 100%; }
.pos-categories { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
.category-btn { padding: 8px 16px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.05); 
                border-radius: 8px; cursor: pointer; transition: all 0.3s; }
.category-btn:hover, .category-btn.active { background: var(--primary); border-color: var(--primary); }
.pos-products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; }
.product-item { background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); 
                border-radius: 10px; padding: 12px; cursor: pointer; text-align: center; transition: all 0.3s; }
.product-item:hover { background: rgba(255,255,255,0.1); transform: translateY(-2px); }
.product-item img { width: 100%; height: 80px; object-fit: cover; border-radius: 6px; margin-bottom: 8px; }
.product-item h6 { font-size: 13px; margin: 0 0 4px 0; }
.product-item .price { color: var(--success); font-weight: 600; font-size: 14px; }
.cart-item { background: rgba(255,255,255,0.05); padding: 12px; border-radius: 8px; margin-bottom: 10px; }
.cart-item-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.cart-item-qty { display: flex; gap: 6px; align-items: center; }
.qty-btn { width: 28px; height: 28px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.05); 
           border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.qty-btn:hover { background: var(--primary); }
.pos-summary { padding: 16px; border-top: 2px solid var(--glass-border); }
.summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
.pos-actions { padding: 16px; display: flex; gap: 12px; border-top: 1px solid var(--glass-border); }
.empty-cart { text-align: center; padding: 60px 20px; }
.modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); 
         display: flex; align-items: center; justify-content: center; z-index: 9999; }
.modal-content { width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; }
.close-modal { background: transparent; border: none; font-size: 24px; cursor: pointer; }
.summary-box { background: rgba(124, 92, 255, 0.1); padding: 16px; border-radius: 10px; margin: 16px 0; }
@media (max-width: 968px) { .pos-container { grid-template-columns: 1fr; } }
</style>

<script>
let cart = [];
let products = [];

// Load products
function loadProducts(category = 'all', search = '') {
    fetch(`api/get-products.php?category=${category}&search=${encodeURIComponent(search)}`)
        .then(r => r.json())
        .then(data => {
            products = data;
            renderProducts();
        });
}

function renderProducts() {
    const grid = document.getElementById('productsGrid');
    grid.innerHTML = products.map(p => `
        <div class="product-item" onclick="addToCart(${p.id})">
            ${p.image_path ? `<img src="${p.image_path}" alt="">` : '<div style="height:80px;background:#333;border-radius:6px;"></div>'}
            <h6>${p.name_ar}</h6>
            <div class="price">${p.price_ils} ₪</div>
        </div>
    `).join('');
}

function addToCart(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;
    
    const existing = cart.find(item => item.id === productId);
    if (existing) {
        existing.quantity++;
    } else {
        cart.push({...product, quantity: 1});
    }
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartItems');
    if (cart.length === 0) {
        container.innerHTML = '<div class="empty-cart"><i class="bi bi-cart-x" style="font-size:64px;opacity:0.2;"></i><p class="text-muted">السلة فارغة</p></div>';
    } else {
        container.innerHTML = cart.map((item, index) => `
            <div class="cart-item">
                <div class="cart-item-header">
                    <strong>${item.name_ar}</strong>
                    <button onclick="removeFromCart(${index})" class="qty-btn"><i class="bi bi-trash"></i></button>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div class="cart-item-qty">
                        <button onclick="updateQty(${index}, -1)" class="qty-btn"><i class="bi bi-dash"></i></button>
                        <span style="width:40px;text-align:center;">${item.quantity}</span>
                        <button onclick="updateQty(${index}, 1)" class="qty-btn"><i class="bi bi-plus"></i></button>
                    </div>
                    <strong style="color:var(--success);">${(item.price_ils * item.quantity).toFixed(2)} ₪</strong>
                </div>
            </div>
        `).join('');
    }
    updateSummary();
}

function updateQty(index, change) {
    cart[index].quantity += change;
    if (cart[index].quantity <= 0) cart.splice(index, 1);
    renderCart();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
}

function updateSummary() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price_ils * item.quantity), 0);
    const tax = subtotal * 0.17;
    const total = subtotal + tax;
    
    document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' ₪';
    document.getElementById('tax').textContent = tax.toFixed(2) + ' ₪';
    document.getElementById('total').textContent = total.toFixed(2) + ' ₪';
    document.getElementById('modalTotal').textContent = total.toFixed(2) + ' ₪';
}

document.getElementById('clearCart').addEventListener('click', () => {
    if (confirm('مسح جميع المنتجات من السلة؟')) {
        cart = [];
        renderCart();
    }
});

document.getElementById('checkoutBtn').addEventListener('click', () => {
    if (cart.length === 0) {
        alert('السلة فارغة!');
        return;
    }
    document.getElementById('checkoutModal').style.display = 'flex';
});

document.querySelectorAll('.close-modal').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('checkoutModal').style.display = 'none';
    });
});

document.getElementById('checkoutForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('cart', JSON.stringify(cart));
    
    fetch('api/create-pos-order.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('تم إتمام البيع بنجاح!');
            cart = [];
            renderCart();
            document.getElementById('checkoutModal').style.display = 'none';
            e.target.reset();
            window.open(`../orders/print.php?id=${data.order_id}`, '_blank');
        } else {
            alert('حدث خطأ: ' + data.error);
        }
    });
});

document.getElementById('productSearch').addEventListener('input', (e) => {
    const category = document.querySelector('.category-btn.active').dataset.category;
    loadProducts(category, e.target.value);
});

document.querySelectorAll('.category-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        loadProducts(btn.dataset.category, document.getElementById('productSearch').value);
    });
});

loadProducts();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>