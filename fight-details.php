<?php
require_once 'config.php';

$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: ' . SITE_URL . '/fight-championship.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM fight_events WHERE slug = ?");
$stmt->execute([$slug]);
$event = $stmt->fetch();

if (!$event) {
    header('Location: ' . SITE_URL . '/fight-championship.php');
    exit;
}

$pageTitle = $event['event_name'];
require_once 'includes/header.php';
?>

    <!-- Page Banner -->
    <section class="page-banner" style="background: linear-gradient(135deg, #1a1a2e, #0a0a1a);">
        <div class="container">
            <h1><?= sanitize($event['event_name']) ?></h1>
            <p><?= date('l, F j, Y', strtotime($event['event_date'])) ?></p>
            <div class="breadcrumb">
                <a href="<?= SITE_URL ?>">Home</a>
                <span>/</span>
                <a href="<?= SITE_URL ?>/fight-championship.php">Fight Championship</a>
                <span>/</span>
                <span><?= sanitize($event['event_name']) ?></span>
            </div>
        </div>
    </section>

    <!-- Event Details -->
    <section class="section">
        <div class="container">
            <div class="detail-content">
                <?php if ($event['featured_image']): ?>
                <div style="border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 30px; max-height: 400px;">
                    <img src="<?= SITE_URL ?>/<?= $event['featured_image'] ?>" alt="<?= sanitize($event['event_name']) ?>" style="width: 100%; object-fit: cover;">
                </div>
                <?php endif; ?>

                <!-- Event Info Cards -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div style="background: var(--light); padding: 20px; border-radius: var(--radius); text-align: center;">
                        <i class="far fa-calendar-alt" style="font-size: 1.5rem; color: var(--fight); margin-bottom: 8px;"></i>
                        <h4 style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 4px;">Date</h4>
                        <p style="font-weight: 700; color: var(--text);"><?= date('M d, Y', strtotime($event['event_date'])) ?></p>
                    </div>
                    <div style="background: var(--light); padding: 20px; border-radius: var(--radius); text-align: center;">
                        <i class="far fa-clock" style="font-size: 1.5rem; color: var(--fight); margin-bottom: 8px;"></i>
                        <h4 style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 4px;">Time</h4>
                        <p style="font-weight: 700; color: var(--text);"><?= date('g:i A', strtotime($event['event_date'])) ?></p>
                    </div>
                    <div style="background: var(--light); padding: 20px; border-radius: var(--radius); text-align: center;">
                        <i class="fas fa-map-marker-alt" style="font-size: 1.5rem; color: var(--fight); margin-bottom: 8px;"></i>
                        <h4 style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 4px;">Venue</h4>
                        <p style="font-weight: 700; color: var(--text);"><?= sanitize($event['venue']) ?></p>
                    </div>
                    <div style="background: var(--light); padding: 20px; border-radius: var(--radius); text-align: center;">
                        <i class="fas fa-map-pin" style="font-size: 1.5rem; color: var(--fight); margin-bottom: 8px;"></i>
                        <h4 style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 4px;">Location</h4>
                        <p style="font-weight: 700; color: var(--text);"><?= sanitize($event['location']) ?></p>
                    </div>
                </div>

                <!-- Countdown -->
                <?php if ($event['status'] === 'upcoming'): ?>
                <div style="text-align: center; margin-bottom: 30px;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 10px; color: var(--text-light);">Event Starts In</h3>
                    <div class="countdown" data-countdown="<?= date('Y-m-d\TH:i:s', strtotime($event['event_date'])) ?>" style="justify-content: center;"></div>
                </div>
                <?php endif; ?>

                <!-- Description -->
                <h2>About This Event</h2>
                <p><?= nl2br(sanitize($event['description'])) ?></p>

                <!-- Ticket Pricing -->
                <h2>Ticket Pricing</h2>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                    <div style="background: var(--light); padding: 25px; border-radius: var(--radius); border-left: 4px solid var(--fight);">
                        <h4 style="margin-bottom: 5px;">Regular Ticket</h4>
                        <p class="event-price" style="font-size: 1.5rem;"><?= formatPrice($event['ticket_price']) ?></p>
                        <p style="font-size: 0.85rem; color: var(--text-light); margin-top: 5px;">General admission seating</p>
                    </div>
                    <div style="background: var(--primary); padding: 25px; border-radius: var(--radius); color: var(--white); border-left: 4px solid var(--accent-gold);">
                        <h4 style="margin-bottom: 5px; color: var(--accent-gold);">VIP Ticket</h4>
                        <p style="font-size: 1.5rem; font-weight: 800; color: var(--accent-gold);"><?= formatPrice($event['vip_price']) ?></p>
                        <p style="font-size: 0.85rem; opacity: 0.8; margin-top: 5px;">Premium ringside experience</p>
                    </div>
                </div>

                <p style="font-size: 0.9rem; color: var(--text-light);"><i class="fas fa-ticket-alt" style="color: var(--fight); margin-right: 6px;"></i> <strong><?= number_format($event['available_tickets']) ?></strong> tickets still available</p>

                <!-- CTA -->
                <?php if ($event['status'] === 'upcoming' && $event['available_tickets'] > 0): ?>
                <div class="text-center mt-30">
                    <a href="<?= SITE_URL ?>/fight-booking.php?event_id=<?= $event['id'] ?>" class="btn btn-fight btn-lg">Book Tickets Now <i class="fas fa-arrow-right"></i></a>
                </div>
                <?php elseif ($event['available_tickets'] <= 0): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> This event is sold out.
                </div>
                <?php endif; ?>

                <!-- Share -->
                <div style="margin-top: 30px;">
                    <h4 style="margin-bottom: 10px; font-size: 0.95rem;">Share This Event</h4>
                    <div class="social-share">
                        <a href="https://facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . '/fight-details.php?slug=' . $event['slug']) ?>" target="_blank" class="facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode(SITE_URL . '/fight-details.php?slug=' . $event['slug']) ?>&text=<?= urlencode($event['event_name']) ?>" target="_blank" class="twitter"><i class="fab fa-x-twitter"></i></a>
                        <a href="https://wa.me/?text=<?= urlencode($event['event_name'] . ' - ' . SITE_URL . '/fight-details.php?slug=' . $event['slug']) ?>" target="_blank" class="whatsapp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
