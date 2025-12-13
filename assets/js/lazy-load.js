/**
 * Lazy Loading for Products
 * Infinite scroll implementation
 */

class ProductsLazyLoader {
    constructor(options = {}) {
        this.container = options.container || '#products-grid';
        this.apiUrl = options.apiUrl || '/api/products-lazy.php';
        this.currentPage = 1;
        this.perPage = options.perPage || 20;
        this.loading = false;
        this.hasMore = true;
        this.filters = options.filters || {};
        
        this.init();
    }
    
    init() {
        this.attachScrollListener();
        this.loadProducts();
    }
    
    attachScrollListener() {
        let ticking = false;
        
        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    this.checkScroll();
                    ticking = false;
                });
                ticking = true;
            }
        });
    }
    
    checkScroll() {
        if (this.loading || !this.hasMore) return;
        
        const scrollPosition = window.innerHeight + window.scrollY;
        const threshold = document.body.offsetHeight * 0.8; // Load at 80%
        
        if (scrollPosition >= threshold) {
            this.loadMore();
        }
    }
    
    loadMore() {
        this.currentPage++;
        this.loadProducts();
    }
    
    async loadProducts() {
        if (this.loading) return;
        
        this.loading = true;
        this.showLoader();
        
        try {
            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage,
                ...this.filters
            });
            
            const response = await fetch(`${this.apiUrl}?${params}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderProducts(data.products);
                this.hasMore = data.pagination.has_more;
                
                if (!this.hasMore) {
                    this.showEndMessage();
                }
            }
        } catch (error) {
            console.error('Error loading products:', error);
            this.showError();
        } finally {
            this.loading = false;
            this.hideLoader();
        }
    }
    
    renderProducts(products) {
        const container = document.querySelector(this.container);
        
        products.forEach(product => {
            const card = this.createProductCard(product);
            container.appendChild(card);
        });
    }
    
    createProductCard(product) {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.style.cssText = 'background:white;border:1px solid var(--border);border-radius:12px;overflow:hidden;transition:all 0.3s;display:flex;flex-direction:column;height:100%';
        
        const discount = product.discount_percentage > 0 ? 
            `<span style="position:absolute;top:12px;right:12px;background:var(--danger);color:white;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700">${product.discount_percentage}%-</span>` : '';
        
        const imageUrl = product.image_url || '/assets/images/placeholder.png';
        
        card.innerHTML = `
            <div style="width:100%;height:200px;overflow:hidden;position:relative;background:#f8f9fa">
                <a href="/product.php?id=${product.id}">
                    <img src="${imageUrl}" style="width:100%;height:100%;object-fit:cover;transition:0.3s" loading="lazy" alt="${product.name_ar}">
                </a>
                ${discount}
            </div>
            <div style="padding:14px;flex:1;display:flex;flex-direction:column">
                <a href="/product.php?id=${product.id}" style="font-size:14px;font-weight:600;color:var(--dark);margin-bottom:8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;height:40px;line-height:20px;text-decoration:none">${product.name_ar}</a>
                <div style="display:flex;align-items:center;gap:4px;margin-bottom:8px;font-size:12px">
                    <div style="color:#fbbf24;display:flex;gap:2px">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star"></i>
                    </div>
                    <span style="color:var(--text-light);font-size:11px">(4.0)</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;gap:8px">
                    <div style="display:flex;align-items:center;gap:6px;flex-direction:row-reverse;flex-wrap:wrap">
                        <span style="font-size:18px;font-weight:700;color:var(--primary)">${product.price_formatted}</span>
                        ${product.original_price_formatted ? `<span style="font-size:13px;color:var(--text-light);text-decoration:line-through">${product.original_price_formatted}</span>` : ''}
                    </div>
                    <button onclick="addToCart(${product.id})" style="padding:6px 12px;background:var(--primary);color:white;border:none;border-radius:8px;cursor:pointer;font-size:12px;font-weight:600;transition:0.3s;white-space:nowrap">أضف</button>
                </div>
            </div>
        `;
        
        // Hover effects
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-5px)';
            card.style.boxShadow = '0 10px 30px rgba(0,0,0,0.1)';
            card.style.borderColor = 'var(--primary)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = 'none';
            card.style.borderColor = 'var(--border)';
        });
        
        return card;
    }
    
    showLoader() {
        let loader = document.getElementById('lazy-loader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'lazy-loader';
            loader.style.cssText = 'text-align:center;padding:40px;';
            loader.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">جاري التحميل...</span></div>';
            document.querySelector(this.container).parentElement.appendChild(loader);
        }
        loader.style.display = 'block';
    }
    
    hideLoader() {
        const loader = document.getElementById('lazy-loader');
        if (loader) loader.style.display = 'none';
    }
    
    showEndMessage() {
        let message = document.getElementById('end-message');
        if (!message) {
            message = document.createElement('div');
            message.id = 'end-message';
            message.style.cssText = 'text-align:center;padding:40px;color:var(--text-light);font-size:14px;';
            message.innerHTML = '<i class="bi bi-check-circle" style="font-size:24px;display:block;margin-bottom:10px"></i>تم عرض جميع المنتجات';
            document.querySelector(this.container).parentElement.appendChild(message);
        }
    }
    
    showError() {
        const error = document.createElement('div');
        error.style.cssText = 'text-align:center;padding:20px;color:var(--danger);';
        error.innerHTML = 'حدث خطأ أثناء تحميل المنتجات. <a href="javascript:location.reload()">إعادة المحاولة</a>';
        document.querySelector(this.container).parentElement.appendChild(error);
    }
    
    updateFilters(newFilters) {
        this.filters = { ...this.filters, ...newFilters };
        this.reset();
        this.loadProducts();
    }
    
    reset() {
        this.currentPage = 1;
        this.hasMore = true;
        const container = document.querySelector(this.container);
        container.innerHTML = '';
        
        const loader = document.getElementById('lazy-loader');
        const endMsg = document.getElementById('end-message');
        if (loader) loader.remove();
        if (endMsg) endMsg.remove();
    }
}

// Auto-initialize if products grid exists
if (document.getElementById('products-grid')) {
    window.productsLoader = new ProductsLazyLoader({
        container: '#products-grid',
        perPage: 20
    });
}