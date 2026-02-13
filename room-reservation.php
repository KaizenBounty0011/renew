<?php
require_once 'config.php';
$pageTitle = 'Room Reservation';

$hotel_id = (int)($_GET['hotel_id'] ?? 0);
$room_id = (int)($_GET['room_id'] ?? 0);

// Fetch hotel
$stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ? AND status = 'active'");
$stmt->execute([$hotel_id]);
$hotel = $stmt->fetch();

if (!$hotel) {
    header('Location: ' . SITE_URL . '/hotels.php');
    exit;
}

// Fetch all available rooms for this hotel
$roomsStmt = $pdo->prepare("SELECT * FROM rooms WHERE hotel_id = ? AND status = 'available' ORDER BY price_per_night ASC");
$roomsStmt->execute([$hotel_id]);
$rooms = $roomsStmt->fetchAll();

if (empty($rooms)) {
    header('Location: ' . SITE_URL . '/hotel-details.php?slug=' . urlencode($hotel['slug']));
    exit;
}

$pageTitle = 'Reserve at ' . $hotel['hotel_name'];
$success = false;
$bookingRef = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    }

    $guest_name = sanitize($_POST['guest_name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone = sanitize($_POST['phone'] ?? '');
    $post_room_id = (int)($_POST['room_id'] ?? 0);
    $check_in = $_POST['check_in_date'] ?? '';
    $check_out = $_POST['check_out_date'] ?? '';
    $guests_count = (int)($_POST['guests_count'] ?? 1);
    $special_requests = sanitize($_POST['special_requests'] ?? '');

    // Validate
    if (empty($guest_name)) $errors[] = 'Guest name is required.';
    if (!$email) $errors[] = 'Valid email address is required.';
    if (empty($phone)) $errors[] = 'Phone number is required.';
    if (empty($post_room_id)) $errors[] = 'Please select a room.';
    if (empty($check_in)) $errors[] = 'Check-in date is required.';
    if (empty($check_out)) $errors[] = 'Check-out date is required.';

    // Validate dates
    if (!empty($check_in) && !empty($check_out)) {
        $checkInDate = new DateTime($check_in);
        $checkOutDate = new DateTime($check_out);
        $today = new DateTime('today');

        if ($checkInDate < $today) {
            $errors[] = 'Check-in date cannot be in the past.';
        }
        if ($checkOutDate <= $checkInDate) {
            $errors[] = 'Check-out date must be after check-in date.';
        }
    }

    // Fetch the selected room
    $selectedRoom = null;
    if ($post_room_id > 0) {
        $roomCheck = $pdo->prepare("SELECT * FROM rooms WHERE id = ? AND hotel_id = ? AND status = 'available'");
        $roomCheck->execute([$post_room_id, $hotel_id]);
        $selectedRoom = $roomCheck->fetch();
        if (!$selectedRoom) {
            $errors[] = 'Selected room is not available.';
        }
    }

    // Validate guest count against capacity
    if ($selectedRoom && $guests_count > $selectedRoom['capacity']) {
        $errors[] = 'Guest count exceeds room capacity of ' . $selectedRoom['capacity'] . '.';
    }

    if (empty($errors) && $selectedRoom) {
        // Calculate nights and total server-side
        $checkInDate = new DateTime($check_in);
        $checkOutDate = new DateTime($check_out);
        $total_nights = (int)$checkInDate->diff($checkOutDate)->days;
        $total_amount = $total_nights * $selectedRoom['price_per_night'];
        $bookingRef = generateBookingRef('RH');

        $insertStmt = $pdo->prepare("
            INSERT INTO room_reservations (room_id, hotel_id, guest_name, email, phone, check_in_date, check_out_date, guests_count, total_nights, total_amount, booking_reference, payment_status, special_requests, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, 'confirmed')
        ");
        $insertStmt->execute([
            $post_room_id,
            $hotel_id,
            $guest_name,
            $email,
            $phone,
            $check_in,
            $check_out,
            $guests_count,
            $total_nights,
            $total_amount,
            $bookingRef,
            $special_requests
        ]);

        $success = true;
    }
}

$csrfToken = generateCSRFToken();
require_once 'includes/header.php';
?>

    <!-- Page Banner -->
    <section class="page-banner">
        <div class="container">
            <h1>Room Reservation</h1>
            <p><?= sanitize($hotel['hotel_name']) ?></p>
            <div class="breadcrumb">
                <a href="<?= SITE_URL ?>">Home</a>
                <span>/</span>
                <a href="<?= SITE_URL ?>/hotels.php">Hotels</a>
                <span>/</span>
                <a href="<?= SITE_URL ?>/hotel-details.php?slug=<?= urlencode($hotel['slug']) ?>"><?= sanitize($hotel['hotel_name']) ?></a>
                <span>/</span>
                <span>Reserve</span>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <?php if ($success): ?>
            <!-- Success Page -->
            <div class="success-page">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h1>Reservation Confirmed!</h1>
                <p>Thank you for choosing <?= sanitize($hotel['hotel_name']) ?>. Your reservation has been received and confirmed.</p>
                <p>Your booking reference is:</p>
                <div class="booking-ref"><?= $bookingRef ?></div>
                <p>A confirmation email will be sent to your email address. Please save your booking reference for future reference.</p>
                <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                    <a href="<?= SITE_URL ?>/hotel-details.php?slug=<?= urlencode($hotel['slug']) ?>" class="btn btn-hotels">Back to Hotel <i class="fas fa-arrow-right"></i></a>
                    <a href="<?= SITE_URL ?>/hotels.php" class="btn btn-dark">View All Hotels</a>
                </div>
            </div>
            <?php else: ?>
            <!-- Reservation Form -->
            <div class="form-card">
                <h2><i class="fas fa-bed" style="color: var(--hotels); margin-right: 10px;"></i> Reserve Your Room</h2>

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

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <div class="form-group">
                        <label for="guest_name">Full Name *</label>
                        <input type="text" id="guest_name" name="guest_name" class="form-control" required value="<?= sanitize($_POST['guest_name'] ?? '') ?>" placeholder="Enter your full name">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" class="form-control" required value="<?= sanitize($_POST['email'] ?? '') ?>" placeholder="your@email.com">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" class="form-control" required value="<?= sanitize($_POST['phone'] ?? '') ?>" placeholder="+234 800 000 0000">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="room_id">Select Room *</label>
                        <select id="room_id" name="room_id" class="form-control" required>
                            <option value="">-- Select a Room --</option>
                            <?php foreach ($rooms as $r): ?>
                            <option value="<?= $r['id'] ?>" data-price="<?= $r['price_per_night'] ?>" data-capacity="<?= $r['capacity'] ?>" <?= ($room_id == $r['id'] || (isset($_POST['room_id']) && $_POST['room_id'] == $r['id'])) ? 'selected' : '' ?>>
                                <?= sanitize($r['room_type']) ?> - <?= formatPrice($r['price_per_night']) ?>/night (Max: <?= $r['capacity'] ?> guests)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="check_in_date">Check-in Date *</label>
                            <input type="date" id="check_in_date" name="check_in_date" class="form-control" required value="<?= sanitize($_POST['check_in_date'] ?? '') ?>" min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label for="check_out_date">Check-out Date *</label>
                            <input type="date" id="check_out_date" name="check_out_date" class="form-control" required value="<?= sanitize($_POST['check_out_date'] ?? '') ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="guests_count">Number of Guests *</label>
                        <input type="number" id="guests_count" name="guests_count" class="form-control" min="1" max="10" required value="<?= (int)($_POST['guests_count'] ?? 1) ?>">
                    </div>

                    <div class="form-group">
                        <label for="special_requests">Special Requests (Optional)</label>
                        <textarea id="special_requests" name="special_requests" class="form-control" placeholder="Any special requests or preferences..."><?= sanitize($_POST['special_requests'] ?? '') ?></textarea>
                    </div>

                    <!-- Booking Summary -->
                    <div class="booking-summary">
                        <h3><i class="fas fa-receipt"></i> Booking Summary</h3>
                        <div class="summary-row">
                            <span>Hotel:</span>
                            <span><?= sanitize($hotel['hotel_name']) ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Number of Nights:</span>
                            <span id="nights_display">0</span>
                        </div>
                        <div class="summary-row summary-total">
                            <span>Total Amount:</span>
                            <span id="room_total_display">&#8358;0</span>
                        </div>
                        <input type="hidden" id="total_nights" name="total_nights" value="0">
                        <input type="hidden" id="room_total_amount" name="room_total_amount" value="0">
                    </div>

                    <div style="margin-top: 25px;">
                        <button type="submit" class="btn btn-hotels btn-lg" style="width: 100%; justify-content: center;">
                            <i class="fas fa-lock"></i> Confirm Reservation
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </section>

<?php
$extraJS = '
<script>
function calculateTotal() {
    var checkIn = document.getElementById("check_in_date").value;
    var checkOut = document.getElementById("check_out_date").value;
    var roomSelect = document.getElementById("room_id");
    var selectedOption = roomSelect.options[roomSelect.selectedIndex];
    var pricePerNight = selectedOption ? parseFloat(selectedOption.getAttribute("data-price")) || 0 : 0;

    var nightsDisplay = document.getElementById("nights_display");
    var totalDisplay = document.getElementById("room_total_display");
    var totalNightsInput = document.getElementById("total_nights");
    var totalAmountInput = document.getElementById("room_total_amount");

    if (checkIn && checkOut && pricePerNight > 0) {
        var start = new Date(checkIn);
        var end = new Date(checkOut);
        var diffTime = end.getTime() - start.getTime();
        var nights = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (nights > 0) {
            var total = nights * pricePerNight;
            nightsDisplay.textContent = nights + " night" + (nights > 1 ? "s" : "");
            totalDisplay.textContent = "\u20A6" + total.toLocaleString();
            totalNightsInput.value = nights;
            totalAmountInput.value = total;
        } else {
            nightsDisplay.textContent = "0";
            totalDisplay.textContent = "\u20A60";
            totalNightsInput.value = 0;
            totalAmountInput.value = 0;
        }
    } else {
        nightsDisplay.textContent = "0";
        totalDisplay.textContent = "\u20A60";
        totalNightsInput.value = 0;
        totalAmountInput.value = 0;
    }
}

document.getElementById("check_in_date").addEventListener("change", function() {
    var checkOut = document.getElementById("check_out_date");
    var nextDay = new Date(this.value);
    nextDay.setDate(nextDay.getDate() + 1);
    checkOut.min = nextDay.toISOString().split("T")[0];
    if (checkOut.value && checkOut.value <= this.value) {
        checkOut.value = nextDay.toISOString().split("T")[0];
    }
    calculateTotal();
});
document.getElementById("check_out_date").addEventListener("change", calculateTotal);
document.getElementById("room_id").addEventListener("change", calculateTotal);

// Calculate on page load if values exist
calculateTotal();
</script>';
require_once 'includes/footer.php';
?>
