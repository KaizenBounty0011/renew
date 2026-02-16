<?php
require_once 'config.php';
$pageTitle = 'Media & Events';

$filter = $_GET['filter'] ?? '';
$division = $_GET['division'] ?? '';

// Fetch fight events
$fightEvents = $pdo->query("SELECT id, event_name AS title, slug, event_date AS event_date, venue, location, description, featured_image, status, 'fight' AS division FROM fight_events ORDER BY event_date DESC")->fetchAll();

// Fetch entertainment shows
$entEvents = $pdo->query("SELECT id, show_name AS title, slug, show_date AS event_date, venue, location, description, featured_image, status, 'entertainment' AS division FROM entertainment_shows ORDER BY show_date DESC")->fetchAll();

// Merge and sort by date descending
$allEvents = array_merge($fightEvents, $entEvents);
usort($allEvents, function($a, $b) {
    return strtotime($b['event_date']) - strtotime($a['event_date']);
});

// Determine current vs past: upcoming/cancelled = current, completed = past
foreach ($allEvents as &$event) {
    $event['time_tag'] = ($event['status'] === 'completed') ? 'past' : 'current';
}
unset($event);

// Apply filters
if ($filter === 'current') {
    $allEvents = array_filter($allEvents, fn($e) => $e['time_tag'] === 'current');
} elseif ($filter === 'past') {
    $allEvents = array_filter($allEvents, fn($e) => $e['time_tag'] === 'past');
}
if ($division === 'fight') {
    $allEvents = array_filter($allEvents, fn($e) => $e['division'] === 'fight');
} elseif ($division === 'entertainment') {
    $allEvents = array_filter($allEvents, fn($e) => $e['division'] === 'entertainment');
}

$allEvents = array_values($allEvents);

require_once 'includes/header.php';
?>

    <section class="page-banner">
        <div class="container">
            <h1>Media & Events</h1>
            <p>Past and upcoming events from Renew Fight Championship and Renew Entertainment.</p>
            <div class="breadcrumb">
                <a href="<?= SITE_URL ?>">Home</a><span>/</span><span>Media & Events</span>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <!-- Filter Bar -->
            <div class="filter-bar" style="margin-bottom: 30px;">
                <a href="<?= SITE_URL ?>/media.php" class="filter-btn <?= !$filter && !$division ? 'active' : '' ?>">All</a>
                <a href="<?= SITE_URL ?>/media.php?filter=current" class="filter-btn <?= $filter === 'current' ? 'active' : '' ?>">Current / Upcoming</a>
                <a href="<?= SITE_URL ?>/media.php?filter=past" class="filter-btn <?= $filter === 'past' ? 'active' : '' ?>">Past Events</a>
                <a href="<?= SITE_URL ?>/media.php?division=fight" class="filter-btn <?= $division === 'fight' ? 'active' : '' ?>" style="border-left: 3px solid var(--fight);"><i class="fas fa-fist-raised"></i> Fight Championship</a>
                <a href="<?= SITE_URL ?>/media.php?division=entertainment" class="filter-btn <?= $division === 'entertainment' ? 'active' : '' ?>" style="border-left: 3px solid var(--entertainment);"><i class="fas fa-music"></i> Entertainment</a>
            </div>

            <?php if (empty($allEvents)): ?>
            <div class="text-center" style="padding: 60px 0;">
                <i class="fas fa-calendar-times" style="font-size: 3rem; color: var(--gray-light); margin-bottom: 15px;"></i>
                <h3>No events found</h3>
                <p style="color: var(--text-light);">Try a different filter or check back soon.</p>
            </div>
            <?php else: ?>
            <div class="media-events-grid">
                <?php foreach ($allEvents as $event):
                    $divColor = $event['division'] === 'fight' ? 'var(--fight)' : 'var(--entertainment)';
                    $divLabel = $event['division'] === 'fight' ? 'Fight Championship' : 'Entertainment';
                    $imgUrl = $event['featured_image'] ? SITE_URL . '/' . $event['featured_image'] : SITE_URL . '/assets/images/hero-bg.jpg';
                ?>
                <div class="event-media-card">
                    <div class="event-media-card-img" style="background-image: url('<?= $imgUrl ?>');">
                        <span class="event-tag <?= $event['time_tag'] ?>"><?= ucfirst($event['time_tag']) ?></span>
                        <span class="event-division" style="background: <?= $divColor ?>;"><?= $divLabel ?></span>
                    </div>
                    <div class="event-media-card-body">
                        <h3><?= sanitize($event['title']) ?></h3>
                        <div class="event-meta">
                            <span><i class="far fa-calendar"></i> <?= date('M d, Y', strtotime($event['event_date'])) ?></span>
                            <?php if ($event['venue']): ?>
                            <span><i class="fas fa-map-marker-alt"></i> <?= sanitize($event['venue']) ?></span>
                            <?php endif; ?>
                            <?php if ($event['location']): ?>
                            <span><i class="fas fa-globe-africa"></i> <?= sanitize($event['location']) ?></span>
                            <?php endif; ?>
                        </div>
                        <p><?= truncateText($event['description'], 120) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
