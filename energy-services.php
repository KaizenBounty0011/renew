<?php
require_once 'config.php';
$pageTitle = 'Energy Services';

// Fetch all active energy services
$services = $pdo->query("SELECT * FROM energy_services WHERE status = 'active' ORDER BY id")->fetchAll();

require_once 'includes/header.php';
?>

    <!-- Page Banner -->
    <section class="page-banner">
        <div class="container">
            <h1>Energy Services</h1>
            <p>Comprehensive sustainable energy solutions for homes, businesses, and communities.</p>
            <div class="breadcrumb">
                <a href="<?= SITE_URL ?>">Home</a>
                <span>/</span>
                <a href="<?= SITE_URL ?>/energy.php">Energy</a>
                <span>/</span>
                <span>Services</span>
            </div>
        </div>
    </section>

    <!-- Services Listing -->
    <section class="section">
        <div class="container">
            <?php if (empty($services)): ?>
                <p class="text-center">No services available at the moment. Please check back later.</p>
            <?php else: ?>
                <?php foreach ($services as $i => $service): ?>
                <div id="service-<?= $service['id'] ?>" class="division-about" style="margin-bottom: 80px; <?= $i % 2 ? 'direction: rtl;' : '' ?>">
                    <div class="content" style="direction: ltr;">
                        <span class="overline" style="color: var(--energy);">
                            <i class="fas <?= sanitize($service['icon'] ?? 'fa-bolt') ?>"></i>
                            Energy Service
                        </span>
                        <h2><?= sanitize($service['service_name']) ?></h2>
                        <p><?= sanitize($service['description']) ?></p>

                        <?php if (!empty($service['detailed_content'])): ?>
                        <div class="detail-content" style="max-width: 100%;">
                            <?= $service['detailed_content'] ?>
                        </div>
                        <?php endif; ?>

                        <a href="<?= SITE_URL ?>/service-inquiry.php?service_id=<?= $service['id'] ?>" class="btn btn-energy" style="margin-top: 20px;">
                            Request This Service <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div style="direction: ltr;">
                        <div class="about-image" style="background: var(--energy);">
                            <?php if (!empty($service['featured_image'])): ?>
                            <img src="<?= SITE_URL ?>/<?= $service['featured_image'] ?>" alt="<?= sanitize($service['service_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                            <i class="fas <?= sanitize($service['icon'] ?? 'fa-bolt') ?>" style="color: rgba(255,255,255,0.3);"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="cta-content">
            <h2>Need a Custom Energy Solution?</h2>
            <p>Our team of energy experts is ready to design the perfect solution tailored to your specific needs and budget.</p>
            <a href="<?= SITE_URL ?>/service-inquiry.php" class="btn btn-primary btn-lg">Request a Service <i class="fas fa-arrow-right"></i></a>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
