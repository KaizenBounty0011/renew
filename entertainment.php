<?php
require_once 'config.php';
$pageTitle = 'Entertainment';

// Fetch division info
$stmt = $pdo->prepare("SELECT * FROM divisions WHERE slug = 'entertainment' AND status = 'active'");
$stmt->execute();
$division = $stmt->fetch();

if (!$division) {
    header('Location: ' . SITE_URL . '/businesses.php');
    exit;
}

// Fetch upcoming shows
$shows = $pdo->query("SELECT * FROM entertainment_shows WHERE status = 'upcoming' ORDER BY show_date ASC")->fetchAll();

require_once 'includes/header.php';
?>

    <!-- Division Slider -->
    <div class="division-slider">
        <?php
        $heroImages = [$division['hero_image1'], $division['hero_image2'], $division['hero_image3']];
        foreach ($heroImages as $i => $img):
            if (empty($img)) continue;
        ?>
        <div class="slide <?= $i === 0 ? 'active' : '' ?>" style="background-image: url('<?= SITE_URL ?>/<?= $img ?>');">
            <div class="slide-overlay">
                <div class="slide-content">
                    <h2><?= sanitize($division['division_name']) ?></h2>
                    <p><?= sanitize($division['tagline']) ?></p>
                    <a href="#upcoming-shows" class="btn btn-entertainment">View Upcoming Shows <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="slider-dots">
            <?php foreach ($heroImages as $i => $img): if (empty($img)) continue; ?>
            <button class="slider-dot <?= $i === 0 ? 'active' : '' ?>"></button>
            <?php endforeach; ?>
        </div>

        <button class="slider-arrow prev"><i class="fas fa-chevron-left"></i></button>
        <button class="slider-arrow next"><i class="fas fa-chevron-right"></i></button>
    </div>

    <!-- Division Description -->
    <section class="section">
        <div class="container">
            <div class="division-about">
                <div class="content">
                    <span class="overline" style="color: <?= $division['accent_color'] ?>;"><?= sanitize($division['tagline']) ?></span>
                    <h2><?= sanitize($division['division_name']) ?></h2>
                    <p><?= $division['description'] ?></p>
                    <?= $division['content'] ?>
                </div>
                <div>
                    <div class="about-image" style="background: <?= $division['accent_color'] ?>20;">
                        <i class="fas fa-music" style="color: <?= $division['accent_color'] ?>;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Shows -->
    <section class="section section-light" id="upcoming-shows">
        <div class="container">
            <div class="section-header">
                <span class="overline" style="color: <?= $division['accent_color'] ?>;">Upcoming Shows</span>
                <h2>What's Coming Up</h2>
                <p>Discover our lineup of spectacular shows, concerts, and live entertainment experiences.</p>
            </div>

            <?php if (empty($shows)): ?>
                <div class="text-center">
                    <p style="color: var(--text-light); font-size: 1.1rem;">No upcoming shows at the moment. Check back soon!</p>
                </div>
            <?php else: ?>
            <div class="events-grid">
                <?php foreach ($shows as $show): ?>
                <div class="event-card">
                    <div class="event-card-img" style="<?= $show['featured_image'] ? "background-image: url('" . SITE_URL . "/" . $show['featured_image'] . "');" : '' ?>">
                        <div class="event-date-badge" style="background: <?= $division['accent_color'] ?>;">
                            <span class="day"><?= date('d', strtotime($show['show_date'])) ?></span>
                            <span class="month"><?= date('M', strtotime($show['show_date'])) ?></span>
                        </div>
                    </div>
                    <div class="event-card-body">
                        <h3><a href="<?= SITE_URL ?>/show-details.php?slug=<?= $show['slug'] ?>"><?= sanitize($show['show_name']) ?></a></h3>
                        <div class="event-meta">
                            <span><i class="fas fa-map-marker-alt"></i> <?= sanitize($show['venue']) ?>, <?= sanitize($show['location']) ?></span>
                            <span><i class="far fa-clock"></i> <?= date('g:i A', strtotime($show['show_date'])) ?></span>
                            <span><i class="fas fa-ticket-alt"></i> <?= number_format($show['available_tickets']) ?> tickets available</span>
                        </div>
                        <div class="countdown" data-countdown="<?= date('Y-m-d\TH:i:s', strtotime($show['show_date'])) ?>"></div>
                        <div class="event-card-footer">
                            <div class="event-price" style="color: <?= $division['accent_color'] ?>;">
                                <?= formatPrice($show['ticket_price']) ?> <small>/ Regular</small>
                            </div>
                            <a href="<?= SITE_URL ?>/show-booking.php?show_id=<?= $show['id'] ?>" class="btn btn-sm btn-entertainment">Book Now <i class="fas fa-arrow-right"></i></a>
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
            <h2>Experience the Extraordinary</h2>
            <p>From world-class concerts to intimate performances, Renew Entertainment delivers unforgettable moments. Get your tickets today.</p>
            <a href="#upcoming-shows" class="btn btn-entertainment btn-lg">Browse Shows <i class="fas fa-arrow-right"></i></a>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
