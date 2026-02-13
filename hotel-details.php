<?php
require_once 'config.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    header('Location: ' . SITE_URL . '/hotels.php');
    exit;
}

// Fetch hotel
$stmt = $pdo->prepare("SELECT * FROM hotels WHERE slug = ? AND status = 'active'");
$stmt->execute([$slug]);
$hotel = $stmt->fetch();

if (!$hotel) {
    header('Location: ' . SITE_URL . '/hotels.php');
    exit;
}

$pageTitle = $hotel['hotel_name'];

// Fetch rooms for this hotel
$roomStmt = $pdo->prepare("SELECT * FROM rooms WHERE hotel_id = ? AND status = 'available' ORDER BY price_per_night ASC");
$roomStmt->execute([$hotel['id']]);
$rooms = $roomStmt->fetchAll();

require_once 'includes/header.php';
?>

    <!-- Page Banner -->
    <section class="page-banner">
        <div class="container">
            <h1><?= sanitize($hotel['hotel_name']) ?></h1>
            <p><i class="fas fa-map-marker-alt"></i> <?= sanitize($hotel['location']) ?></p>
            <div class="breadcrumb">
                <a href="<?= SITE_URL ?>">Home</a>
                <span>/</span>
                <a href="<?= SITE_URL ?>/hotels.php">Hotels</a>
                <span>/</span>
                <span><?= sanitize($hotel['hotel_name']) ?></span>
            </div>
        </div>
    </section>

    <!-- Hotel Details -->
    <section class="section">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <span class="overline" style="color: var(--hotels);">About This Property</span>
                    <h2><?= sanitize($hotel['hotel_name']) ?></h2>

                    <!-- Star Rating -->
                    <div style="margin-bottom: 15px; color: var(--accent-gold); font-size: 1.1rem;">
                        <?php for ($s = 0; $s < (int)$hotel['star_rating']; $s++): ?>
                        <i class="fas fa-star"></i>
                        <?php endfor; ?>
                        <span style="color: var(--text-light); font-size: 0.9rem; margin-left: 8px;"><?= $hotel['star_rating'] ?>-Star Hotel</span>
                    </div>

                    <p><?= nl2br(sanitize($hotel['description'])) ?></p>

                    <?php if (!empty($hotel['address'])): ?>
                    <p style="margin-top: 10px;"><strong><i class="fas fa-map-marker-alt" style="color: var(--hotels);"></i> Address:</strong> <?= sanitize($hotel['address']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="about-image" style="background: var(--hotels);">
                    <?php if (!empty($hotel['featured_image'])): ?>
                    <img src="<?= SITE_URL ?>/<?= $hotel['featured_image'] ?>" alt="<?= sanitize($hotel['hotel_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                    <i class="fas fa-hotel"></i>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Amenities -->
    <?php
    $amenities = array_filter(array_map('trim', explode(',', $hotel['amenities'] ?? '')));
    if (!empty($amenities)):
    ?>
    <section class="section section-light">
        <div class="container">
            <div class="section-header">
                <span class="overline" style="color: var(--hotels);">What We Offer</span>
                <h2>Hotel Amenities</h2>
            </div>
            <div class="values-grid">
                <?php foreach ($amenities as $amenity): ?>
                <div class="value-card">
                    <div class="value-icon" style="background: linear-gradient(135deg, var(--hotels), #2ecc71);">
                        <i class="fas fa-check"></i>
                    </div>
                    <h4><?= sanitize($amenity) ?></h4>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Rooms -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <span class="overline" style="color: var(--hotels);">Accommodation</span>
                <h2>Our Rooms & Suites</h2>
                <p>Choose from our selection of thoughtfully designed rooms and suites.</p>
            </div>

            <?php if (empty($rooms)): ?>
                <p class="text-center">No rooms available at the moment. Please check back later.</p>
            <?php else: ?>
            <div class="rooms-grid">
                <?php foreach ($rooms as $room): ?>
                <div class="room-card">
                    <div class="room-card-body">
                        <h4><?= sanitize($room['room_type']) ?></h4>
                        <div class="room-capacity">
                            <i class="fas fa-users"></i> Up to <?= (int)$room['capacity'] ?> guests
                            <?php if ($room['available_rooms'] > 0): ?>
                            <span style="margin-left: 10px; color: var(--energy);"><i class="fas fa-check-circle"></i> <?= (int)$room['available_rooms'] ?> available</span>
                            <?php endif; ?>
                        </div>
                        <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 12px;"><?= sanitize($room['description']) ?></p>

                        <?php
                        $roomAmenities = array_filter(array_map('trim', explode(',', $room['amenities'] ?? '')));
                        if (!empty($roomAmenities)):
                        ?>
                        <div class="room-amenities">
                            <?php foreach ($roomAmenities as $ra): ?>
                            <span><?= sanitize($ra) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <div class="hotel-card-footer">
                            <div class="room-price">
                                <?= formatPrice($room['price_per_night']) ?> <small>/night</small>
                            </div>
                            <a href="<?= SITE_URL ?>/room-reservation.php?hotel_id=<?= $hotel['id'] ?>&room_id=<?= $room['id'] ?>" class="btn btn-sm btn-hotels">
                                Reserve Now <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="cta-content">
            <h2>Ready to Book Your Stay?</h2>
            <p>Experience the finest in African hospitality at <?= sanitize($hotel['hotel_name']) ?>.</p>
            <a href="<?= SITE_URL ?>/contact.php" class="btn btn-primary btn-lg">Contact Us <i class="fas fa-arrow-right"></i></a>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
