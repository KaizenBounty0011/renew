<?php
require_once 'config.php';
$pageTitle = 'Renew Energy';

// Fetch division info
$stmt = $pdo->prepare("SELECT * FROM divisions WHERE slug = 'energy' AND status = 'active'");
$stmt->execute();
$division = $stmt->fetch();

// Fetch energy services
$services = $pdo->query("SELECT * FROM energy_services WHERE status = 'active' ORDER BY id")->fetchAll();

// Fetch featured products (first 3)
$products = $pdo->query("SELECT * FROM energy_products WHERE status = 'active' ORDER BY id LIMIT 3")->fetchAll();

$accentColor = '#27ae60';

require_once 'includes/header.php';
?>

    <!-- Division Slider -->
    <?php if ($division): ?>
    <section class="division-slider" id="divisionSlider">
        <?php
        $slides = [];
        if (!empty($division['hero_image1'])) $slides[] = $division['hero_image1'];
        if (!empty($division['hero_image2'])) $slides[] = $division['hero_image2'];
        if (!empty($division['hero_image3'])) $slides[] = $division['hero_image3'];
        foreach ($slides as $i => $img):
        ?>
        <div class="slide <?= $i === 0 ? 'active' : '' ?>" style="background-image: url('<?= SITE_URL ?>/<?= $img ?>');">
            <div class="slide-overlay">
                <div class="slide-content">
                    <h2><?= sanitize($division['division_name']) ?></h2>
                    <p><?= sanitize($division['tagline']) ?></p>
                    <a href="#energy-services" class="btn btn-energy">Our Services <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (count($slides) > 1): ?>
        <button class="slider-arrow prev" onclick="changeSlide(-1)"><i class="fas fa-chevron-left"></i></button>
        <button class="slider-arrow next" onclick="changeSlide(1)"><i class="fas fa-chevron-right"></i></button>
        <div class="slider-dots">
            <?php for ($i = 0; $i < count($slides); $i++): ?>
            <button class="slider-dot <?= $i === 0 ? 'active' : '' ?>" onclick="goToSlide(<?= $i ?>)"></button>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>

    <!-- Division Description -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <span class="overline" style="color: <?= $accentColor ?>;">Powering Africa's Future</span>
                <h2>Renew Energy</h2>
                <p><?= sanitize($division['description'] ?? '') ?></p>
            </div>
        </div>
    </section>

    <!-- Services Grid -->
    <section class="section section-light" id="energy-services">
        <div class="container">
            <div class="section-header">
                <span class="overline" style="color: <?= $accentColor ?>;">What We Do</span>
                <h2>Our Energy Services</h2>
                <p>Comprehensive energy solutions for a sustainable future.</p>
            </div>

            <?php if (empty($services)): ?>
                <p class="text-center">No services available at the moment.</p>
            <?php else: ?>
            <div class="services-grid">
                <?php foreach ($services as $service): ?>
                <div class="service-card">
                    <div class="service-card-icon">
                        <i class="fas <?= sanitize($service['icon'] ?? 'fa-bolt') ?>"></i>
                    </div>
                    <h3><?= sanitize($service['service_name']) ?></h3>
                    <p><?= truncateText($service['description'], 150) ?></p>
                    <a href="<?= SITE_URL ?>/energy-services.php#service-<?= $service['id'] ?>" class="btn btn-sm btn-energy">
                        Learn More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-30">
                <a href="<?= SITE_URL ?>/energy-services.php" class="btn btn-dark">View All Services <i class="fas fa-arrow-right"></i></a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Featured Products -->
    <?php if (!empty($products)): ?>
    <section class="section">
        <div class="container">
            <div class="section-header">
                <span class="overline" style="color: <?= $accentColor ?>;">Our Products</span>
                <h2>Featured Energy Products</h2>
                <p>High-quality energy solutions for every need.</p>
            </div>

            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-card-img" style="<?= !empty($product['product_image']) ? "background-image: url('" . SITE_URL . "/" . $product['product_image'] . "');" : '' ?>">
                        <?php if (empty($product['product_image'])): ?>
                        <i class="fas fa-box-open"></i>
                        <?php endif; ?>
                    </div>
                    <div class="product-card-body">
                        <div class="category"><?= sanitize($product['category']) ?></div>
                        <h3><?= sanitize($product['product_name']) ?></h3>
                        <p><?= truncateText($product['description'], 100) ?></p>
                        <div class="product-price"><?= formatPrice($product['price']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-30">
                <a href="<?= SITE_URL ?>/energy-catalogue.php" class="btn btn-energy">View Full Catalogue <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-content">
            <h2>Ready to Go Green?</h2>
            <p>Let our energy experts design the perfect solution for your home or business. Contact us today for a free consultation.</p>
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="<?= SITE_URL ?>/service-inquiry.php" class="btn btn-primary btn-lg">Request a Service <i class="fas fa-arrow-right"></i></a>
                <a href="<?= SITE_URL ?>/energy-catalogue.php" class="btn btn-outline btn-lg">Browse Products</a>
            </div>
        </div>
    </section>

<?php
$extraJS = '
<script>
let currentSlide = 0;
const slides = document.querySelectorAll(".slide");
const dots = document.querySelectorAll(".slider-dot");

function goToSlide(index) {
    slides[currentSlide].classList.remove("active");
    if (dots[currentSlide]) dots[currentSlide].classList.remove("active");
    currentSlide = index;
    slides[currentSlide].classList.add("active");
    if (dots[currentSlide]) dots[currentSlide].classList.add("active");
}

function changeSlide(direction) {
    let next = (currentSlide + direction + slides.length) % slides.length;
    goToSlide(next);
}

if (slides.length > 1) {
    setInterval(function() { changeSlide(1); }, 5000);
}
</script>';
require_once 'includes/footer.php';
?>
