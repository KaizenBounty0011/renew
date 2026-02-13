<?php
require_once 'auth.php';

$pageTitle = 'Dashboard';

// ── Stat Cards Queries ──────────────────────────────────────────────

$totalBookings = $pdo->query("
    SELECT (SELECT COUNT(*) FROM fight_bookings)
         + (SELECT COUNT(*) FROM show_bookings)
         + (SELECT COUNT(*) FROM room_reservations) AS total
")->fetch()['total'];

$totalRevenue = $pdo->query("
    SELECT (SELECT COALESCE(SUM(total_amount), 0) FROM fight_bookings WHERE payment_status = 'paid')
         + (SELECT COALESCE(SUM(total_amount), 0) FROM show_bookings WHERE payment_status = 'paid')
         + (SELECT COALESCE(SUM(total_amount), 0) FROM room_reservations WHERE payment_status = 'paid') AS total
")->fetch()['total'];

$activeEvents = $pdo->query("
    SELECT (SELECT COUNT(*) FROM fight_events WHERE status = 'upcoming')
         + (SELECT COUNT(*) FROM entertainment_shows WHERE status = 'upcoming') AS total
")->fetch()['total'];

$openInquiries = $pdo->query("
    SELECT (SELECT COUNT(*) FROM contact_inquiries WHERE status = 'new')
         + (SELECT COUNT(*) FROM service_inquiries WHERE status = 'new') AS total
")->fetch()['total'];

$publishedNews = $pdo->query("
    SELECT COUNT(*) AS total FROM news WHERE status = 'published'
")->fetch()['total'];

$openCareers = $pdo->query("
    SELECT COUNT(*) AS total FROM careers WHERE status = 'open'
")->fetch()['total'];

// ── Recent Bookings (latest 5 combined) ─────────────────────────────

$recentBookings = $pdo->query("
    (
        SELECT fb.booking_reference AS reference, fb.customer_name AS customer,
            'Fight' AS type, fb.total_amount AS amount, fb.payment_status AS status, fb.booked_at AS booked_date
        FROM fight_bookings fb
    )
    UNION ALL
    (
        SELECT sb.booking_reference, sb.customer_name, 'Show', sb.total_amount, sb.payment_status, sb.booked_at
        FROM show_bookings sb
    )
    UNION ALL
    (
        SELECT rr.booking_reference, rr.guest_name, 'Hotel', rr.total_amount, rr.payment_status, rr.booked_at
        FROM room_reservations rr
    )
    ORDER BY booked_date DESC
    LIMIT 5
")->fetchAll();

// ── Recent Activity Feed (latest 8 items) ───────────────────────────

$recentActivity = $pdo->query("
    (
        SELECT CONCAT('New fight booking by ', customer_name, ' — ', booking_reference) AS description,
            booked_at AS activity_date, 'blue' AS color
        FROM fight_bookings
    )
    UNION ALL
    (
        SELECT CONCAT('New show booking by ', customer_name, ' — ', booking_reference),
            booked_at, 'purple'
        FROM show_bookings
    )
    UNION ALL
    (
        SELECT CONCAT('Hotel reservation by ', guest_name, ' — ', booking_reference),
            booked_at, 'teal'
        FROM room_reservations
    )
    UNION ALL
    (
        SELECT CONCAT('Job application from ', applicant_name, ' for position #', career_id),
            applied_at, 'orange'
        FROM job_applications
    )
    UNION ALL
    (
        SELECT CONCAT('Contact inquiry from ', name, ': ', SUBSTRING(subject, 1, 40)),
            submitted_at, 'green'
        FROM contact_inquiries
    )
    UNION ALL
    (
        SELECT CONCAT('Service inquiry from ', contact_person),
            inquiry_date, 'red'
        FROM service_inquiries
    )
    ORDER BY activity_date DESC
    LIMIT 8
")->fetchAll();

require_once 'includes/admin-header.php';
?>

<!-- Page Header -->
<div class="admin-page-header">
    <h1>Dashboard</h1>
</div>

<!-- Stat Cards -->
<div class="stat-cards">
    <div class="stat-card">
        <div class="stat-card-icon blue"><i class="fas fa-ticket-alt"></i></div>
        <div class="stat-card-info">
            <h3><?= number_format($totalBookings) ?></h3>
            <p>Total Bookings</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon green"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-card-info">
            <h3><?= formatPrice($totalRevenue) ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon purple"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-card-info">
            <h3><?= number_format($activeEvents) ?></h3>
            <p>Active Events</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon orange"><i class="fas fa-envelope-open-text"></i></div>
        <div class="stat-card-info">
            <h3><?= number_format($openInquiries) ?></h3>
            <p>Open Inquiries</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon teal"><i class="fas fa-newspaper"></i></div>
        <div class="stat-card-info">
            <h3><?= number_format($publishedNews) ?></h3>
            <p>Published News</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon red"><i class="fas fa-briefcase"></i></div>
        <div class="stat-card-info">
            <h3><?= number_format($openCareers) ?></h3>
            <p>Open Careers</p>
        </div>
    </div>
</div>

<!-- Recent Bookings Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2>Recent Bookings</h2>
        <a href="<?= SITE_URL ?>/admin/admin-bookings.php" class="btn-admin btn-admin-outline btn-admin-sm">View All</a>
    </div>
    <div class="admin-card-body">
        <?php if (empty($recentBookings)): ?>
            <div class="empty-state">
                <i class="fas fa-ticket-alt"></i>
                <h3>No bookings yet</h3>
                <p>Bookings will appear here as they come in.</p>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentBookings as $booking):
                        $typeBadge = match ($booking['type']) {
                            'Fight' => 'badge-danger',
                            'Show'  => 'badge-primary',
                            'Hotel' => 'badge-info',
                            default => 'badge-info',
                        };
                        $statusBadge = match ($booking['status']) {
                            'paid'     => 'badge-success',
                            'pending'  => 'badge-warning',
                            'refunded' => 'badge-danger',
                            default    => 'badge-warning',
                        };
                    ?>
                        <tr>
                            <td><strong><?= sanitize($booking['reference']) ?></strong></td>
                            <td><?= sanitize($booking['customer']) ?></td>
                            <td><span class="badge <?= $typeBadge ?>"><?= sanitize($booking['type']) ?></span></td>
                            <td><?= formatPrice($booking['amount']) ?></td>
                            <td><span class="badge <?= $statusBadge ?>"><?= ucfirst(sanitize($booking['status'])) ?></span></td>
                            <td><?= date('M j, Y', strtotime($booking['booked_date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Activity Feed + Quick Actions -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:25px;">
    <!-- Recent Activity -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Recent Activity</h2>
        </div>
        <div class="admin-card-body">
            <?php if (empty($recentActivity)): ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h3>No activity yet</h3>
                </div>
            <?php else: ?>
                <?php foreach ($recentActivity as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-dot" style="background:var(--admin-<?= $activity['color'] === 'blue' ? 'info' : ($activity['color'] === 'green' ? 'success' : ($activity['color'] === 'red' ? 'danger' : ($activity['color'] === 'orange' ? 'accent' : ($activity['color'] === 'purple' ? 'info' : 'info')))) ?>)"></div>
                        <div>
                            <p><?= sanitize($activity['description']) ?></p>
                            <small><?= timeAgo($activity['activity_date']) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Quick Actions</h2>
        </div>
        <div class="admin-card-body">
            <div class="quick-actions">
                <a href="<?= SITE_URL ?>/admin/admin-news.php?action=add" class="quick-action">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add News</span>
                </a>
                <a href="<?= SITE_URL ?>/admin/admin-careers.php?action=add" class="quick-action">
                    <i class="fas fa-briefcase"></i>
                    <span>Add Job Posting</span>
                </a>
                <a href="<?= SITE_URL ?>/admin/admin-bookings.php" class="quick-action">
                    <i class="fas fa-ticket-alt"></i>
                    <span>View Bookings</span>
                </a>
                <a href="<?= SITE_URL ?>/admin/admin-settings.php" class="quick-action">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
