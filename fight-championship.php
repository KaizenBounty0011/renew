<?php
require_once 'config.php';
$pageTitle = 'Fight Championship';

// Fetch division info
$stmt = $pdo->prepare("SELECT * FROM divisions WHERE slug = 'fight-championship' AND status = 'active'");
$stmt->execute();
$division = $stmt->fetch();

if (!$division) {
    header('Location: ' . SITE_URL . '/about.php');
    exit;
}

// Fetch upcoming fight events
$events = $pdo->query("SELECT * FROM fight_events WHERE status = 'upcoming' ORDER BY event_date ASC")->fetchAll();

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
                    <a href="#upcoming-events" class="btn btn-fight">View Upcoming Fights <i class="fas fa-arrow-right"></i></a>
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
            <div class="division-about-centered">
                <span class="overline" style="color: <?= $division['accent_color'] ?>;"><?= sanitize($division['tagline']) ?></span>
                <h2><?= sanitize($division['division_name']) ?></h2>
                <p><?= $division['description'] ?></p>
                <?= $division['content'] ?>
            </div>
        </div>
    </section>

    <!-- Upcoming Fights -->
    <section class="section section-light" id="upcoming-events">
        <div class="container">
            <div class="section-header">
                <span class="overline" style="color: <?= $division['accent_color'] ?>;">Upcoming Events</span>
                <h2>Fight Night Schedule</h2>
                <p>Don't miss the action. Book your tickets for the next electrifying fight events.</p>
            </div>

            <?php if (empty($events)): ?>
                <div class="text-center">
                    <p style="color: var(--text-light); font-size: 1.1rem;">No upcoming events at the moment. Check back soon!</p>
                </div>
            <?php else: ?>
            <div class="events-grid">
                <?php foreach ($events as $event): ?>
                <div class="event-card">
                    <div class="event-card-img" style="<?= $event['featured_image'] ? "background-image: url('" . SITE_URL . "/" . $event['featured_image'] . "');" : '' ?>">
                        <div class="event-date-badge" style="background: <?= $division['accent_color'] ?>;">
                            <span class="day"><?= date('d', strtotime($event['event_date'])) ?></span>
                            <span class="month"><?= date('M', strtotime($event['event_date'])) ?></span>
                        </div>
                    </div>
                    <div class="event-card-body">
                        <h3><a href="<?= SITE_URL ?>/fight-details.php?slug=<?= $event['slug'] ?>"><?= sanitize($event['event_name']) ?></a></h3>
                        <div class="event-meta">
                            <span><i class="fas fa-map-marker-alt"></i> <?= sanitize($event['venue']) ?>, <?= sanitize($event['location']) ?></span>
                            <span><i class="far fa-clock"></i> <?= date('g:i A', strtotime($event['event_date'])) ?></span>
                            <span><i class="fas fa-ticket-alt"></i> <?= number_format($event['available_tickets']) ?> tickets available</span>
                        </div>
                        <div class="countdown" data-countdown="<?= date('Y-m-d\TH:i:s', strtotime($event['event_date'])) ?>"></div>
                        <div class="event-card-footer">
                            <div class="event-price">
                                <?= formatPrice($event['ticket_price']) ?> <small>/ Regular</small>
                            </div>
                            <a href="<?= SITE_URL ?>/fight-booking.php?event_id=<?= $event['id'] ?>" class="btn btn-sm btn-fight">Book Now <i class="fas fa-arrow-right"></i></a>
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
            <h2>Ready for Fight Night?</h2>
            <p>Experience the thrill of Africa's premier combat sports promotion. Secure your tickets today and witness history in the making.</p>
            <a href="#upcoming-events" class="btn btn-fight btn-lg">Browse Events <i class="fas fa-arrow-right"></i></a>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
