<?php
/**
 * Product Sections Component
 * Selling, Featured, Discount Products
 */

function renderProductSection($title, $products, $type = 'selling') {
    if (empty($products)) return;
    
    $badgeColors = [
        'selling' => 'bg-danger',
        'featured' => 'bg-primary', 
        'discount' => 'bg-success'
    ];
    
    $badgeColor = $badgeColors[$type] ?? 'bg-info';
    ?>
    
    <section class="products-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title"><?php echo escape($title); ?></h2>
            </div>
            
            <div class="row g-4">
                <?php foreach ($products as $product): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card">
                        <div class="product-image">
                            <a href="/product/<?php echo $product['slug']; ?>">
                                <img src="<?php echo escape($product['image_path']); ?>" 
                                     alt="<?php echo escape(getLangField($product, 'name')); ?>">
                            </a>
                            
                            <?php if ($type === 'discount' && !empty($product['discount_price'])): 
                                $discount = round((($product['price'] - $product['discount_price']) / $product['price']) * 100);
                            ?>
                            <span class="product-badge discount">-<?php echo $discount; ?>%</span>
                            <?php elseif ($type === 'selling'): ?>
                            <span class="product-badge <?php echo $badgeColor; ?>">Best Seller</span>
                            <?php elseif ($type === 'featured'): ?>
                            <span class="product-badge <?php echo $badgeColor; ?>">Featured</span>
                            <?php endif; ?>
                            
                            <div class="product-actions">
                                <button class="btn-wishlist" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                                    <i class="bi bi-heart"></i>
                                </button>
                                <button class="btn-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                                    <i class="bi bi-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="product-info">
                            <div class="product-category">
                                <?php echo escape($product['category_name_' . LANG] ?? ''); ?>
                            </div>
                            
                            <h3 class="product-name">
                                <a href="/product/<?php echo $product['slug']; ?>">
                                    <?php echo escape(getLangField($product, 'name')); ?>
                                </a>
                            </h3>
                            
                            <div class="product-rating">
                                <?php 
                                $rating = $product['avg_rating'] ?? 4;
                                for ($i = 1; $i <= 5; $i++): 
                                    echo $i <= $rating ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                                endfor;
                                ?>
                                <span class="text-muted">(<?php echo $product['review_count'] ?? 0; ?>)</span>
                            </div>
                            
                            <div class="product-price">
                                <?php if (!empty($product['discount_price'])): ?>
                                    <?php echo formatPrice($product['discount_price']); ?>
                                    <span class="old-price"><?php echo formatPrice($product['price']); ?></span>
                                <?php else: ?>
                                    <?php echo formatPrice($product['price']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <?php
}

// Get Selling Products
function getSellingProducts($limit = 8) {
    global $db;
    return $db->fetchAll(
        "SELECT p.*, c.name_ar as category_name_ar, c.name_en as category_name_en,
                COUNT(DISTINCT oi.id) as total_sold
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         LEFT JOIN order_items oi ON p.id = oi.product_id
         WHERE p.is_active = 1
         GROUP BY p.id
         ORDER BY total_sold DESC
         LIMIT ?",
        [$limit]
    );
}

// Get Featured Products
function getFeaturedProducts($limit = 8) {
    global $db;
    return $db->fetchAll(
        "SELECT p.*, c.name_ar as category_name_ar, c.name_en as category_name_en
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         WHERE p.is_active = 1 AND p.is_featured = 1
         ORDER BY p.created_at DESC
         LIMIT ?",
        [$limit]
    );
}

// Get Discount Products
function getDiscountProducts($limit = 8) {
    global $db;
    return $db->fetchAll(
        "SELECT p.*, c.name_ar as category_name_ar, c.name_en as category_name_en
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         WHERE p.is_active = 1 AND p.discount_price IS NOT NULL AND p.discount_price > 0
         ORDER BY (p.price - p.discount_price) DESC
         LIMIT ?",
        [$limit]
    );
}
?>