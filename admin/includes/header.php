<?php
if (!isset($page_title)) $page_title = 'لوحة التحكم';
if (!isset($active_page)) $active_page = '';

$admin_name = $_SESSION['admin_name'] ?? $_SESSION['admin_username'] ?? 'المسؤول';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$role_label = $admin_role === 'admin' ? 'مدير' : 'مبيعات';

// Get pending orders count
$pending_orders = 0;
try {
    $result = $db->fetch("SELECT COUNT(*) as c FROM orders WHERE status='pending'");
    $pending_orders = (int)($result['c'] ?? 0);
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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
            <button class="sidebar-close" id="sidebarClose">
                <i class="bi bi-x-lg"></i>
            </button>
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
            <a href="/admin/categories/" class="nav-item <?php echo $active_page === 'categories' ? 'active' : ''; ?>">
                <i class="bi bi-tags"></i>
                <span>التصنيفات</span>
            </a>
            
            <div class="nav-section">المبيعات</div>
            <a href="/admin/orders/" class="nav-item <?php echo $active_page === 'orders' ? 'active' : ''; ?>">
                <i class="bi bi-receipt"></i>
                <span>الطلبات</span>
                <?php if ($pending_orders > 0): ?>
                <span class="badge-count"><?php echo $pending_orders; ?></span>
                <?php endif; ?>
            </a>
            <a href="/admin/pos/" class="nav-item <?php echo $active_page === 'pos' ? 'active' : ''; ?>">
                <i class="bi bi-calculator"></i>
                <span>نقطة البيع</span>
            </a>
            <a href="/admin/reports/" class="nav-item <?php echo $active_page === 'reports' ? 'active' : ''; ?>">
                <i class="bi bi-graph-up"></i>
                <span>التقارير</span>
            </a>
            
            <div class="nav-section">المستخدمين</div>
            <a href="/admin/users/" class="nav-item <?php echo $active_page === 'users' ? 'active' : ''; ?>">
                <i class="bi bi-people"></i>
                <span>المستخدمين</span>
            </a>
            
            <?php if ($admin_role === 'admin'): ?>
            <div class="nav-section">الإعدادات</div>
            <a href="/admin/settings/" class="nav-item <?php echo $active_page === 'settings' ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i>
                <span>عامة</span>
            </a>
            <a href="/admin/settings/smtp.php" class="nav-item <?php echo $active_page === 'smtp' ? 'active' : ''; ?>">
                <i class="bi bi-envelope"></i>
                <span>SMTP</span>
            </a>
            <a href="/admin/settings/whatsapp.php" class="nav-item <?php echo $active_page === 'whatsapp' ? 'active' : ''; ?>">
                <i class="bi bi-whatsapp"></i>
                <span>WhatsApp</span>
            </a>
            <a href="/admin/settings/appearance.php" class="nav-item <?php echo $active_page === 'appearance' ? 'active' : ''; ?>">
                <i class="bi bi-palette"></i>
                <span>المظهر</span>
            </a>
            <a href="/admin/settings/chatbot.php" class="nav-item <?php echo $active_page === 'chatbot' ? 'active' : ''; ?>">
                <i class="bi bi-robot"></i>
                <span>ChatBot</span>
            </a>
            <?php endif; ?>
        </nav>
    </aside>
    
    <!-- Overlay for mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
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
                <a href="/" target="_blank" class="btn-icon" title="عرض المتجر">
                    <i class="bi bi-shop"></i>
                </a>
                
                <div class="user-menu">
                    <button class="user-btn" id="userMenuBtn">
                        <i class="bi bi-person-circle"></i>
                        <span class="user-name"><?php echo escape($admin_name); ?></span>
                        <span class="role-badge"><?php echo escape($role_label); ?></span>
                    </button>
                    <div class="dropdown-menu" id="userDropdown">
                        <?php if ($admin_role === 'admin'): ?>
                        <a href="/admin/settings/"><i class="bi bi-gear"></i> الإعدادات</a>
                        <?php endif; ?>
                        <a href="/admin/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> تسجيل الخروج</a>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <?php
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success"><i class="bi bi-check-circle"></i> ' . escape($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> ' . escape($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            ?>