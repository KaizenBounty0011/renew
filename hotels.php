<?php
require_once 'config.php';
$pageTitle = 'Renew Hotels';

// Fetch division info
$stmt = $pdo->prepare("SELECT * FROM divisions WHERE slug = 'hotels' AND status = 'active'");
$stmt->execute();
$division = $stmt->fetch();

// Fetch active hotels with lowest room price
$hotels = $pdo->query("
    SELECT h.*, MIN(r.price_per_night) as min_price
    FROM hotels h
    LEFT JOIN rooms r ON h.id = r.hotel_id AND r.status = 'available'
    WHERE h.status = 'active'
    GROUP BY h.id
    ORDER BY h.id
")->fetchAll();

$accentColor = '#1abc9c';

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
                    <a href="#hotels-listing" class="btn btn-hotels">Explore Our Hotels <i class="fas fa-arrow-right"></i></a>
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
                <span class="overline" style="color: <?= $accentColor ?>;">Luxury Redefined</span>
                <h2>Renew Hotels</h2>
                <p><?= sanitize($division['description'] ?? '') ?></p>
            </div>
        </div>
    </section>

    <!-- Hotels Listing -->
    <section class="section section-light" id="hotels-listing">
        <div class="container">
            <div class="section-header">
                <span class="overline" style="color: <?= $accentColor ?>;">Our Properties</span>
                <h2>Explore Our Hotels</h2>
            </div>

            <?php if (empty($hotels)): ?>
                <p class="text-center">No hotels available at the moment. Please check back later.</p>
            <?php else: ?>
            <div class="events-grid">
                <?php foreach ($hotels as $hotel): ?>
                <div class="hotel-card">
                    <div class="hotel-card-img" style="background-image: url('<?= SITE_URL ?>/<?= $hotel['featured_image'] ?>');">
                        <div class="hotel-stars">
                            <?php for ($s = 0; $s < (int)$hotel['star_rating']; $s++): ?>
                            <i class="fas fa-star"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="hotel-card-body">
                        <h3><?= sanitize($hotel['hotel_name']) ?></h3>
                        <div class="hotel-location">
                            <i class="fas fa-map-marker-alt"></i> <?= sanitize($hotel['location']) ?>
                        </div>
                        <div class="hotel-amenities">
                            <?php
                            $amenities = array_filter(array_map('trim', explode(',', $hotel['amenities'] ?? '')));
                            $displayAmenities = array_slice($amenities, 0, 4);
                            foreach ($displayAmenities as $amenity):
                            ?>
                            <span class="hotel-amenity"><?= sanitize($amenity) ?></span>
                            <?php endforeach; ?>
                            <?php if (count($amenities) > 4): ?>
                            <span class="hotel-amenity">+<?= count($amenities) - 4 ?> more</span>
                            <?php endif; ?>
                        </div>
                        <div class="hotel-card-footer">
                            <div class="room-price">
                                <?php if ($hotel['min_price']): ?>
                                    <?= formatPrice($hotel['min_price']) ?> <small>/night</small>
                                <?php else: ?>
                                    <small>Contact for pricing</small>
                                <?php endif; ?>
                            </div>
                            <a href="<?= SITE_URL ?>/hotel-details.php?slug=<?= urlencode($hotel['slug']) ?>" class="btn btn-sm btn-hotels">
                                View Details <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-content">
            <h2>Experience Luxury</h2>
            <p>Book your stay at any of our premium properties and enjoy world-class hospitality with authentic African warmth.</p>
            <a href="<?= SITE_URL ?>/contact.php" class="btn btn-primary btn-lg">Contact Us <i class="fas fa-arrow-right"></i></a>
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
