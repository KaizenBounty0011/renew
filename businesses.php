<?php
require_once 'config.php';
$pageTitle = 'Our Businesses';
$divisions = $pdo->query("SELECT * FROM divisions WHERE status = 'active' ORDER BY id")->fetchAll();
require_once 'includes/header.php';
?>

    <section class="page-banner">
        <div class="container">
            <h1>Our Businesses</h1>
            <p>Four world-class divisions driving innovation across Africa.</p>
            <div class="breadcrumb">
                <a href="<?= SITE_URL ?>">Home</a><span>/</span><span>Our Businesses</span>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <?php
            $icons = ['fas fa-fist-raised', 'fas fa-music', 'fas fa-hotel', 'fas fa-bolt'];
            $links = ['fight-championship', 'entertainment', 'hotels', 'energy'];
            foreach ($divisions as $i => $div):
            ?>
            <div class="division-about" style="margin-bottom: 80px; <?= $i % 2 ? 'direction: rtl;' : '' ?>">
                <div class="content" style="direction: ltr;">
                    <span class="overline" style="color: <?= $div['accent_color'] ?>;"><?= sanitize($div['tagline']) ?></span>
                    <h2><?= sanitize($div['division_name']) ?></h2>
                    <p><?= $div['description'] ?></p>
                    <?= $div['content'] ?>
                    <a href="<?= SITE_URL ?>/<?= $links[$i] ?>.php" class="btn btn-sm" style="background: <?= $div['accent_color'] ?>; color: #fff; margin-top: 15px;">
                        Explore <?= sanitize($div['division_name']) ?> <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div style="direction: ltr;">
                    <div class="about-image" style="background: <?= $div['accent_color'] ?>20;">
                        <i class="<?= $icons[$i] ?>" style="color: <?= $div['accent_color'] ?>;"></i>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
