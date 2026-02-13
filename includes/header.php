<?php
if (!isset($pdo)) require_once __DIR__ . '/../config.php';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= getSetting('meta_description') ?>">
    <meta name="theme-color" content="#1a1a2e">
    <meta property="og:title" content="<?= $pageTitle ?? SITE_NAME ?>">
    <meta property="og:description" content="<?= getSetting('meta_description') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= SITE_URL ?>">
    <title><?= isset($pageTitle) ? $pageTitle . ' | ' . SITE_NAME : SITE_NAME . ' - Building Tomorrow, Today' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <?php if (isset($extraCSS)) echo $extraCSS; ?>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <div class="top-bar-left">
                    <span><i class="fas fa-envelope"></i> <?= getSetting('site_email') ?></span>
                    <span><i class="fas fa-phone"></i> <?= getSetting('site_phone') ?></span>
                </div>
                <div class="top-bar-right">
                    <a href="<?= getSetting('facebook') ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="<?= getSetting('twitter') ?>" target="_blank"><i class="fab fa-x-twitter"></i></a>
                    <a href="<?= getSetting('instagram') ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="<?= getSetting('linkedin') ?>" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="container">
            <a href="<?= SITE_URL ?>" class="logo">
                <span class="logo-renew">RENEW</span><span class="logo-empire">EMPIRE</span>
            </a>
            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                <span></span><span></span><span></span>
            </button>
            <ul class="nav-menu" id="navMenu">
                <li><a href="<?= SITE_URL ?>/index.php" class="<?= $currentPage === 'index' ? 'active' : '' ?>">Home</a></li>
                <li><a href="<?= SITE_URL ?>/about.php" class="<?= $currentPage === 'about' ? 'active' : '' ?>">About</a></li>
                <li class="dropdown">
                    <a href="<?= SITE_URL ?>/businesses.php" class="<?= in_array($currentPage, ['businesses','fight-championship','entertainment','hotels','energy']) ? 'active' : '' ?>">
                        Businesses <i class="fas fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="<?= SITE_URL ?>/fight-championship.php"><i class="fas fa-fist-raised"></i> Fight Championship</a></li>
                        <li><a href="<?= SITE_URL ?>/entertainment.php"><i class="fas fa-music"></i> Entertainment</a></li>
                        <li><a href="<?= SITE_URL ?>/hotels.php"><i class="fas fa-hotel"></i> Hotels</a></li>
                        <li><a href="<?= SITE_URL ?>/energy.php"><i class="fas fa-bolt"></i> Energy</a></li>
                    </ul>
                </li>
                <li><a href="<?= SITE_URL ?>/news.php" class="<?= $currentPage === 'news' ? 'active' : '' ?>">News</a></li>
                <li><a href="<?= SITE_URL ?>/careers.php" class="<?= $currentPage === 'careers' ? 'active' : '' ?>">Careers</a></li>
                <li><a href="<?= SITE_URL ?>/media.php" class="<?= $currentPage === 'media' ? 'active' : '' ?>">Media</a></li>
                <li><a href="<?= SITE_URL ?>/contact.php" class="<?= $currentPage === 'contact' ? 'active' : '' ?>">Contact</a></li>
            </ul>
        </div>
    </nav>
