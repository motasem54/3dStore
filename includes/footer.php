    </main>
    
    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div class="footer-logo">
                        <div class="logo-icon"><i class="bi bi-box-seam"></i></div>
                        <span>3D Store</span>
                    </div>
                    <p class="footer-desc"><?php echo t('hero_desc'); ?></p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h4>روابط سريعة</h4>
                    <ul class="footer-links">
                        <li><a href="/"><?php echo t('home'); ?></a></li>
                        <li><a href="/products.php"><?php echo t('products'); ?></a></li>
                        <li><a href="/categories.php"><?php echo t('categories'); ?></a></li>
                        <li><a href="/track.php">تتبع طلبك</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>خدمة العملاء</h4>
                    <ul class="footer-links">
                        <li><a href="/account/"><?php echo t('account'); ?></a></li>
                        <li><a href="/cart.php"><?php echo t('cart'); ?></a></li>
                        <li><a href="/wishlist.php">المفضلة</a></li>
                        <li><a href="/contact.php">اتصل بنا</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>اشترك بالنشرة</h4>
                    <p>احصل على آخر العروض والمنتجات</p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="بريدك الإلكتروني" required>
                        <button type="submit"><i class="bi bi-send"></i></button>
                    </form>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> 3D Store. <?php echo t('copyright'); ?></p>
                <div class="payment-methods">
                    <i class="bi bi-credit-card"></i>
                    <i class="bi bi-paypal"></i>
                    <i class="bi bi-cash"></i>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="/assets/js/store.js"></script>
</body>
</html>