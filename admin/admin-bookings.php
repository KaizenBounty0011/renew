<?php
require_once 'auth.php';
$pageTitle = 'Bookings';
$tab = $_GET['tab'] ?? 'fights';
$success = $_GET['success'] ?? '';

// Update payment status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['new_status'], $_POST['booking_type'])) {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $type = $_POST['booking_type'];
        $tables = [
            'fight' => 'fight_bookings',
            'show' => 'show_bookings',
            'hotel' => 'room_reservations',
        ];
        if (isset($tables[$type])) {
            $pdo->prepare("UPDATE {$tables[$type]} SET payment_status = ? WHERE id = ?")->execute([$_POST['new_status'], (int)$_POST['booking_id']]);
        }
        header("Location: admin-bookings.php?tab={$tab}&success=updated");
        exit;
    }
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page-header">
    <h1>Bookings</h1>
</div>

<?php if ($success === 'updated'): ?>
<div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Payment status updated.</div>
<?php endif; ?>

<div class="admin-tabs">
    <a href="?tab=fights" class="admin-tab <?= $tab === 'fights' ? 'active' : '' ?>">Fight Bookings</a>
    <a href="?tab=shows" class="admin-tab <?= $tab === 'shows' ? 'active' : '' ?>">Show Bookings</a>
    <a href="?tab=hotels" class="admin-tab <?= $tab === 'hotels' ? 'active' : '' ?>">Hotel Reservations</a>
</div>

<div class="admin-card">
    <div class="admin-card-body">
<?php if ($tab === 'fights'): ?>
<?php
$bookings = $pdo->query("SELECT fb.*, fe.event_name FROM fight_bookings fb JOIN fight_events fe ON fb.event_id = fe.id ORDER BY fb.booked_at DESC")->fetchAll();
?>
    <?php if (empty($bookings)): ?>
    <div class="empty-state"><i class="fas fa-ticket-alt"></i><h3>No fight bookings yet</h3></div>
    <?php else: ?>
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead><tr><th>Reference</th><th>Customer</th><th>Event</th><th>Type</th><th>Qty</th><th>Amount</th><th>Payment</th><th>Date</th></tr></thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><strong><?= sanitize($b['booking_reference']) ?></strong></td>
                    <td><?= sanitize($b['customer_name']) ?><div class="form-hint"><?= sanitize($b['email']) ?></div></td>
                    <td><?= sanitize($b['event_name']) ?></td>
                    <td><span class="badge badge-info"><?= ucfirst($b['ticket_type']) ?></span></td>
                    <td><?= $b['ticket_quantity'] ?></td>
                    <td><?= formatPrice($b['total_amount']) ?></td>
                    <td>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                            <input type="hidden" name="booking_type" value="fight">
                            <select name="new_status">
                                <?php foreach (['pending','paid','refunded'] as $s): ?>
                                <option value="<?= $s ?>" <?= $b['payment_status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit"><i class="fas fa-check"></i></button>
                        </form>
                    </td>
                    <td><?= date('M j, Y', strtotime($b['booked_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

<?php elseif ($tab === 'shows'): ?>
<?php
$bookings = $pdo->query("SELECT sb.*, es.show_name FROM show_bookings sb JOIN entertainment_shows es ON sb.show_id = es.id ORDER BY sb.booked_at DESC")->fetchAll();
?>
    <?php if (empty($bookings)): ?>
    <div class="empty-state"><i class="fas fa-ticket-alt"></i><h3>No show bookings yet</h3></div>
    <?php else: ?>
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead><tr><th>Reference</th><th>Customer</th><th>Show</th><th>Type</th><th>Qty</th><th>Amount</th><th>Payment</th><th>Date</th></tr></thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><strong><?= sanitize($b['booking_reference']) ?></strong></td>
                    <td><?= sanitize($b['customer_name']) ?><div class="form-hint"><?= sanitize($b['email']) ?></div></td>
                    <td><?= sanitize($b['show_name']) ?></td>
                    <td><span class="badge badge-info"><?= ucfirst($b['ticket_type']) ?></span></td>
                    <td><?= $b['ticket_quantity'] ?></td>
                    <td><?= formatPrice($b['total_amount']) ?></td>
                    <td>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                            <input type="hidden" name="booking_type" value="show">
                            <select name="new_status">
                                <?php foreach (['pending','paid','refunded'] as $s): ?>
                                <option value="<?= $s ?>" <?= $b['payment_status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit"><i class="fas fa-check"></i></button>
                        </form>
                    </td>
                    <td><?= date('M j, Y', strtotime($b['booked_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

<?php else: ?>
<?php
$bookings = $pdo->query("SELECT rr.*, h.hotel_name, r.room_type FROM room_reservations rr JOIN hotels h ON rr.hotel_id = h.id JOIN rooms r ON rr.room_id = r.id ORDER BY rr.booked_at DESC")->fetchAll();
?>
    <?php if (empty($bookings)): ?>
    <div class="empty-state"><i class="fas fa-hotel"></i><h3>No hotel reservations yet</h3></div>
    <?php else: ?>
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead><tr><th>Reference</th><th>Guest</th><th>Hotel</th><th>Room</th><th>Check In/Out</th><th>Amount</th><th>Payment</th><th>Booked</th></tr></thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><strong><?= sanitize($b['booking_reference']) ?></strong></td>
                    <td><?= sanitize($b['guest_name']) ?><div class="form-hint"><?= sanitize($b['email']) ?></div></td>
                    <td><?= sanitize($b['hotel_name']) ?></td>
                    <td><?= sanitize($b['room_type']) ?></td>
                    <td><?= date('M j', strtotime($b['check_in_date'])) ?> — <?= date('M j, Y', strtotime($b['check_out_date'])) ?><div class="form-hint"><?= $b['total_nights'] ?> night(s)</div></td>
                    <td><?= formatPrice($b['total_amount']) ?></td>
                    <td>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                            <input type="hidden" name="booking_type" value="hotel">
                            <select name="new_status">
                                <?php foreach (['pending','paid','refunded'] as $s): ?>
                                <option value="<?= $s ?>" <?= $b['payment_status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit"><i class="fas fa-check"></i></button>
                        </form>
                    </td>
                    <td><?= date('M j, Y', strtotime($b['booked_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
<?php endif; ?>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
