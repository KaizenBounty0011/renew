<?php
$adminPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin' ?> | Renew Empire Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin-style.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <a href="<?= SITE_URL ?>/admin/admin-dashboard.php" class="sidebar-logo">
                    <span class="logo-r">RE</span> Admin
                </a>
                <button class="sidebar-close" id="sidebarClose"><i class="fas fa-times"></i></button>
            </div>
            <nav class="sidebar-nav">
                <a href="<?= SITE_URL ?>/admin/admin-dashboard.php" class="<?= $adminPage === 'admin-dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <div class="sidebar-section">Content</div>
                <a href="<?= SITE_URL ?>/admin/admin-news.php" class="<?= $adminPage === 'admin-news' ? 'active' : '' ?>">
                    <i class="fas fa-newspaper"></i> News & Press
                </a>
                <a href="<?= SITE_URL ?>/admin/admin-careers.php" class="<?= $adminPage === 'admin-careers' ? 'active' : '' ?>">
                    <i class="fas fa-briefcase"></i> Careers
                </a>
                <a href="<?= SITE_URL ?>/admin/admin-applications.php" class="<?= $adminPage === 'admin-applications' ? 'active' : '' ?>">
                    <i class="fas fa-file-alt"></i> Applications
                </a>
                <a href="<?= SITE_URL ?>/admin/admin-divisions.php" class="<?= $adminPage === 'admin-divisions' ? 'active' : '' ?>">
                    <i class="fas fa-layer-group"></i> Divisions
                </a>
                <a href="<?= SITE_URL ?>/admin/admin-events.php" class="<?= $adminPage === 'admin-events' ? 'active' : '' ?>">
                    <i class="fas fa-calendar-alt"></i> Events & Shows
                </a>
                <div class="sidebar-section">Bookings</div>
                <a href="<?= SITE_URL ?>/admin/admin-bookings.php" class="<?= $adminPage === 'admin-bookings' ? 'active' : '' ?>">
                    <i class="fas fa-ticket-alt"></i> Bookings
                </a>
                <div class="sidebar-section">Communication</div>
                <a href="<?= SITE_URL ?>/admin/admin-inquiries.php" class="<?= $adminPage === 'admin-inquiries' ? 'active' : '' ?>">
                    <i class="fas fa-envelope"></i> Inquiries
                </a>
                <div class="sidebar-section">System</div>
                <a href="<?= SITE_URL ?>/admin/admin-settings.php" class="<?= $adminPage === 'admin-settings' ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <a href="<?= SITE_URL ?>" target="_blank">
                    <i class="fas fa-external-link-alt"></i> View Site
                </a>
                <a href="<?= SITE_URL ?>/admin/logout.php" class="logout-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="admin-main">
            <header class="admin-topbar">
                <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                <div class="topbar-right">
                    <span class="admin-greeting">Welcome, <?= sanitize($currentAdmin['full_name']) ?></span>
                </div>
            </header>
            <div class="admin-content">
