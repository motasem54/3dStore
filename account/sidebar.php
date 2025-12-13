<div style="background:white;border-radius:16px;padding:24px;box-shadow:0 4px 12px rgba(0,0,0,0.08);height:fit-content;position:sticky;top:100px">
    <div style="text-align:center;margin-bottom:24px">
        <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--secondary));margin:0 auto 12px;display:flex;align-items:center;justify-content:center;color:white;font-size:32px"><i class="bi bi-person"></i></div>
        <h3 style="font-size:18px;margin-bottom:4px"><?php echo escape($user['first_name'] . ' ' . $user['last_name']); ?></h3>
        <p style="font-size:14px;color:var(--text-light)"><?php echo escape($user['email']); ?></p>
    </div>
    <nav style="display:flex;flex-direction:column;gap:4px">
        <a href="/account/dashboard.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>"><i class="bi bi-speedometer2"></i> لوحة التحكم</a>
        <a href="/account/orders.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : ''; ?>"><i class="bi bi-box-seam"></i> طلباتي</a>
        <a href="/account/wishlist.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'wishlist.php' ? 'active' : ''; ?>"><i class="bi bi-heart"></i> المفضلة</a>
        <a href="/account/addresses.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'addresses.php' ? 'active' : ''; ?>"><i class="bi bi-geo-alt"></i> العناوين</a>
        <a href="/account/settings.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>"><i class="bi bi-gear"></i> الإعدادات</a>
        <a href="/account/logout.php" class="sidebar-link" style="color:var(--danger);margin-top:12px;border-top:1px solid var(--border);padding-top:16px"><i class="bi bi-box-arrow-right"></i> تسجيل الخروج</a>
    </nav>
</div>

<style>
.sidebar-link {
    padding: 12px 16px;
    border-radius: 8px;
    text-decoration: none;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 10px;
    transition: 0.3s;
    font-weight: 500;
}

.sidebar-link:hover {
    background: var(--bg-light);
    padding-right: 20px;
}

.sidebar-link.active {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    font-weight: 600;
}

.sidebar-link i {
    font-size: 18px;
}
</style>