<div class="product-card">
    <div class="product-image">
        <?php if ($product['image_path']): ?>
        <img src="<?php echo UPLOAD_URL . '/products/' . $product['image_path']; ?>" alt="<?php echo escape($product['name_' . $lang]); ?>">
        <?php else: ?>
        <div class="product-placeholder"><i class="bi bi-image"></i></div>
        <?php endif; ?>
        
        <?php if ($product['product_type'] === '3d'): ?>
        <span class="product-badge badge-3d"><i class="bi bi-box"></i> 3D</span>
        <?php endif; ?>
        
        <div class="product-actions">
            <button class="action-btn" onclick="addToWishlist(<?php echo $product['id']; ?>)" title="إضافة للمفضلة">
                <i class="bi bi-heart"></i>
            </button>
            <a href="/product.php?id=<?php echo $product['id']; ?>" class="action-btn" title="<?php echo t('quick_view'); ?>">
                <i class="bi bi-eye"></i>
            </a>
        </div>
    </div>
    
    <div class="product-info">
        <a href="/product.php?id=<?php echo $product['id']; ?>" class="product-title">
            <?php echo escape($product['name_' . $lang]); ?>
        </a>
        
        <div class="product-meta">
            <span class="product-sku"><?php echo escape($product['sku']); ?></span>
            <?php if ($product['stock_quantity'] > 0): ?>
            <span class="stock-badge in-stock"><i class="bi bi-check-circle"></i> متوفر</span>
            <?php else: ?>
            <span class="stock-badge out-of-stock"><i class="bi bi-x-circle"></i> غير متوفر</span>
            <?php endif; ?>
        </div>
        
        <div class="product-footer">
            <div class="product-price">
                <?php 
                $price = $currency === 'ILS' ? $product['price_ils'] : $product['price_usd'];
                echo formatPrice($price);
                ?>
            </div>
            
            <?php if ($product['stock_quantity'] > 0): ?>
            <button class="btn-add-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                <i class="bi bi-cart-plus"></i>
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>