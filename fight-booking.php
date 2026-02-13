<?php
require_once 'config.php';

$eventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

// Fetch event
$stmt = $pdo->prepare("SELECT * FROM fight_events WHERE id = ? AND status = 'upcoming'");
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    header('Location: ' . SITE_URL . '/fight-championship.php');
    exit;
}

$pageTitle = 'Book Tickets - ' . $event['event_name'];
$errors = [];
$success = false;
$bookingRef = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    }

    $customerName = sanitize($_POST['customer_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $ticketType = sanitize($_POST['ticket_type'] ?? 'regular');
    $ticketQuantity = (int)($_POST['ticket_quantity'] ?? 1);
    $totalAmount = (float)($_POST['total_amount'] ?? 0);

    // Validate inputs
    if (empty($customerName)) $errors[] = 'Full name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
    if (empty($phone)) $errors[] = 'Phone number is required.';
    if (!in_array($ticketType, ['regular', 'vip'])) $errors[] = 'Invalid ticket type.';
    if ($ticketQuantity < 1 || $ticketQuantity > 10) $errors[] = 'Ticket quantity must be between 1 and 10.';

    // Verify price server-side
    $unitPrice = ($ticketType === 'vip') ? $event['vip_price'] : $event['ticket_price'];
    $calculatedTotal = $unitPrice * $ticketQuantity;

    // Check availability
    if ($ticketQuantity > $event['available_tickets']) {
        $errors[] = 'Sorry, only ' . $event['available_tickets'] . ' tickets are available.';
    }

    if (empty($errors)) {
        $bookingRef = generateBookingRef('RF');

        $stmt = $pdo->prepare("INSERT INTO fight_bookings (event_id, customer_name, email, phone, ticket_type, ticket_quantity, total_amount, booking_reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $event['id'],
            $customerName,
            $email,
            $phone,
            $ticketType,
            $ticketQuantity,
            $calculatedTotal,
            $bookingRef
        ]);

        // Reduce available tickets
        $pdo->prepare("UPDATE fight_events SET available_tickets = available_tickets - ? WHERE id = ?")->execute([$ticketQuantity, $event['id']]);

        $success = true;

        // Regenerate CSRF token after successful submission
        unset($_SESSION['csrf_token']);
    }
}

$csrfToken = generateCSRFToken();
require_once 'includes/header.php';
?>

    <!-- Page Banner -->
    <section class="page-banner" style="background: linear-gradient(135deg, #1a1a2e, #0a0a1a);">
        <div class="container">
            <h1>Book Tickets</h1>
            <p><?= sanitize($event['event_name']) ?></p>
            <div class="breadcrumb">
                <a href="<?= SITE_URL ?>">Home</a>
                <span>/</span>
                <a href="<?= SITE_URL ?>/fight-championship.php">Fight Championship</a>
                <span>/</span>
                <a href="<?= SITE_URL ?>/fight-details.php?slug=<?= $event['slug'] ?>"><?= sanitize($event['event_name']) ?></a>
                <span>/</span>
                <span>Book Tickets</span>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <?php if ($success): ?>
            <!-- Booking Success -->
            <div class="success-page">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h1>Booking Confirmed!</h1>
                <p>Your tickets for <strong><?= sanitize($event['event_name']) ?></strong> have been reserved successfully.</p>
                <p>Your booking reference is:</p>
                <div class="booking-ref"><?= $bookingRef ?></div>
                <p style="font-size: 0.9rem;">A confirmation email will be sent to your email address. Please save your booking reference for check-in.</p>
                <div style="display: flex; gap: 15px; justify-content: center; margin-top: 25px; flex-wrap: wrap;">
                    <a href="<?= SITE_URL ?>/fight-championship.php" class="btn btn-fight">Browse More Events <i class="fas fa-arrow-right"></i></a>
                    <a href="<?= SITE_URL ?>" class="btn btn-dark">Back to Home</a>
                </div>
            </div>
            <?php else: ?>
            <!-- Booking Form -->
            <div class="form-card">
                <h2><i class="fas fa-ticket-alt" style="color: var(--fight); margin-right: 10px;"></i> Book Your Tickets</h2>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <?php foreach ($errors as $error): ?>
                        <p><?= $error ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Event Summary -->
                <div style="background: var(--light); padding: 20px; border-radius: var(--radius); margin-bottom: 25px; display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <h3 style="font-size: 1.1rem; margin-bottom: 5px;"><?= sanitize($event['event_name']) ?></h3>
                        <p style="font-size: 0.85rem; color: var(--text-light);"><i class="far fa-calendar"></i> <?= date('M d, Y \a\t g:i A', strtotime($event['event_date'])) ?></p>
                        <p style="font-size: 0.85rem; color: var(--text-light);"><i class="fas fa-map-marker-alt"></i> <?= sanitize($event['venue']) ?>, <?= sanitize($event['location']) ?></p>
                    </div>
                    <div class="countdown" data-countdown="<?= date('Y-m-d\TH:i:s', strtotime($event['event_date'])) ?>"></div>
                </div>

                <form method="POST" data-validate>
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="customer_name">Full Name *</label>
                            <input type="text" id="customer_name" name="customer_name" class="form-control" required value="<?= isset($customerName) ? $customerName : '' ?>" placeholder="Enter your full name">
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" class="form-control" required value="<?= isset($email) ? $email : '' ?>" placeholder="Enter your email">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" class="form-control" required value="<?= isset($phone) ? $phone : '' ?>" placeholder="e.g. +234 800 000 0000">
                        </div>
                        <div class="form-group">
                            <label for="ticket_type">Ticket Type *</label>
                            <select id="ticket_type" name="ticket_type" class="form-control" required>
                                <option value="regular" data-price="<?= $event['ticket_price'] ?>" <?= (isset($ticketType) && $ticketType === 'regular') ? 'selected' : '' ?>>Regular - <?= formatPrice($event['ticket_price']) ?></option>
                                <option value="vip" data-price="<?= $event['vip_price'] ?>" <?= (isset($ticketType) && $ticketType === 'vip') ? 'selected' : '' ?>>VIP - <?= formatPrice($event['vip_price']) ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ticket_quantity">Number of Tickets * <small style="color: var(--text-light);">(max 10)</small></label>
                        <input type="number" id="ticket_quantity" name="ticket_quantity" class="form-control" required min="1" max="10" value="<?= isset($ticketQuantity) ? $ticketQuantity : 1 ?>">
                    </div>

                    <input type="hidden" id="total_amount" name="total_amount" value="0">

                    <!-- Booking Summary -->
                    <div class="booking-summary">
                        <h3><i class="fas fa-receipt" style="margin-right: 8px;"></i> Booking Summary</h3>
                        <div class="summary-row">
                            <span>Event</span>
                            <span><?= sanitize($event['event_name']) ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Date</span>
                            <span><?= date('M d, Y', strtotime($event['event_date'])) ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Venue</span>
                            <span><?= sanitize($event['venue']) ?></span>
                        </div>
                        <div class="summary-row summary-total">
                            <span>Total Amount</span>
                            <span id="total_display"><?= formatPrice($event['ticket_price']) ?></span>
                        </div>
                    </div>

                    <div class="text-center mt-30">
                        <button type="submit" class="btn btn-fight btn-lg" style="width: 100%;">
                            <i class="fas fa-lock"></i> Confirm Booking
                        </button>
                        <p style="font-size: 0.8rem; color: var(--text-light); margin-top: 10px;">
                            <i class="fas fa-shield-alt"></i> Your booking information is secure. Payment will be collected at the venue.
                        </p>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
