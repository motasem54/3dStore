<?php
if (!isset($page_title)) $page_title = 'لوحة التحكم';
if (!isset($active_page)) $active_page = '';

$admin_name = $_SESSION['admin_name'] ?? $_SESSION['admin_username'] ?? 'المسؤول';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$role_label = $admin_role === 'admin' ? 'مدير' : 'مبيعات';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($page_title); ?> - 3D Store</title>
    <link rel="stylesheet" href="/admin/assets/css/admin-glass.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
</head>
<body class="admin-panel">
    
    <!-- Sidebar -->
    <aside class="sidebar glass-sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <div class="logo-icon"><i class="bi bi-box-seam"></i></div>
                <div class="logo-text">
                    <h2>3D Store</h2>
                    <p>لوحة التحكم</p>
                </div>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="/admin/index.php" class="nav-item <?php echo $active_page === 'dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                <span>الرئيسية</span>
            </a>
            
            <div class="nav-section">المنتجات</div>
            <a href="/admin/products/" class="nav-item <?php echo $active_page === 'products' ? 'active' : ''; ?>">
                <i class="bi bi-box-seam"></i>
                <span>جميع المنتجات</span>
            </a>
            <a href="/admin/products/add.php" class="nav-item <?php echo $active_page === 'products-add' ? 'active' : ''; ?>">
                <i class="bi bi-plus-circle"></i>
                <span>إضافة منتج</span>
            </a>
            <a href="/admin/products/categories.php" class="nav-item <?php echo $active_page === 'categories' ? 'active' : ''; ?>">
                <i class="bi bi-tags"></i>
                <span>التصنيفات</span>
            </a>
            
            <div class="nav-section">المبيعات</div>
            <a href="/admin/orders/" class="nav-item <?php echo $active_page === 'orders' ? 'active' : ''; ?>">
                <i class="bi bi-receipt"></i>
                <span>الطلبات</span>
                <?php
                $pending = $db->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'")->fetch()['c'] ?? 0;
                if ($pending > 0) echo '<span class="badge-count">'. $pending .'</span>';
                ?>
            </a>
            <a href="/admin/pos/" class="nav-item <?php echo $active_page === 'pos' ? 'active' : ''; ?>">
                <i class="bi bi-calculator"></i>
                <span>نقطة البيع</span>
            </a>
            
            <div class="nav-section">المحاسبة</div>
            <a href="/admin/accounting/" class="nav-item <?php echo $active_page === 'accounting' ? 'active' : ''; ?>">
                <i class="bi bi-graph-up"></i>
                <span>التقارير المالية</span>
            </a>
            <a href="/admin/accounting/expenses.php" class="nav-item <?php echo $active_page === 'expenses' ? 'active' : ''; ?>">
                <i class="bi bi-cash-stack"></i>
                <span>المصروفات</span>
            </a>
            
            <div class="nav-section">المخزون</div>
            <a href="/admin/inventory/" class="nav-item <?php echo $active_page === 'inventory' ? 'active' : ''; ?>">
                <i class="bi bi-boxes"></i>
                <span>إدارة المخزون</span>
            </a>
            
            <div class="nav-section">العملاء</div>
            <a href="/admin/customers/" class="nav-item <?php echo $active_page === 'customers' ? 'active' : ''; ?>">
                <i class="bi bi-people"></i>
                <span>العملاء</span>
            </a>
            <a href="/admin/reviews/" class="nav-item <?php echo $active_page === 'reviews' ? 'active' : ''; ?>">
                <i class="bi bi-star"></i>
                <span>التقييمات</span>
            </a>
            
            <?php if ($admin_role === 'admin'): ?>
            <div class="nav-section">الإعدادات</div>
            <a href="/admin/settings/" class="nav-item <?php echo $active_page === 'settings' ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i>
                <span>الإعدادات العامة</span>
            </a>
            <a href="/admin/settings/appearance.php" class="nav-item <?php echo $active_page === 'appearance' ? 'active' : ''; ?>">
                <i class="bi bi-palette"></i>
                <span>المظهر والألوان</span>
            </a>
            <a href="/admin/settings/chatbot.php" class="nav-item <?php echo $active_page === 'chatbot' ? 'active' : ''; ?>">
                <i class="bi bi-robot"></i>
                <span>ChatBot</span>
            </a>
            <?php endif; ?>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar glass-navbar">
            <div class="navbar-start">
                <button class="btn-icon sidebar-toggle" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="page-title"><?php echo escape($page_title); ?></h1>
            </div>
            
            <div class="navbar-end">
                <button class="btn-icon" title="الإشعارات">
                    <i class="bi bi-bell"></i>
                    <span class="badge-dot"></span>
                </button>
                
                <button class="btn-icon" title="البحث">
                    <i class="bi bi-search"></i>
                </button>
                
                <div class="user-menu">
                    <button class="user-btn" id="userMenuBtn">
                        <i class="bi bi-person-circle"></i>
                        <span><?php echo escape($admin_name); ?></span>
                        <span class="role-badge"><?php echo escape($role_label); ?></span>
                    </button>
                    <div class="dropdown-menu" id="userDropdown">
                        <a href="/admin/profile.php"><i class="bi bi-person"></i> الملف الشخصي</a>
                        <a href="/admin/settings/"><i class="bi bi-gear"></i> الإعدادات</a>
                        <hr>
                        <a href="/admin/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> تسجيل الخروج</a>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Content Wrapper -->
        <div class="content-wrapper">