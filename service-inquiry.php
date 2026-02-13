<?php
require_once 'config.php';
$pageTitle = 'Service Inquiry';

// Fetch all active energy services for dropdown
$services = $pdo->query("SELECT id, service_name FROM energy_services WHERE status = 'active' ORDER BY service_name")->fetchAll();

$preselect_service_id = (int)($_GET['service_id'] ?? 0);

$success = false;
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    }

    $service_id = (int)($_POST['service_id'] ?? 0);
    $company_name = sanitize($_POST['company_name'] ?? '');
    $contact_person = sanitize($_POST['contact_person'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone = sanitize($_POST['phone'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    // Validate
    if (empty($contact_person)) $errors[] = 'Contact person name is required.';
    if (!$email) $errors[] = 'Valid email address is required.';
    if (empty($phone)) $errors[] = 'Phone number is required.';
    if (empty($message)) $errors[] = 'Message is required.';

    // Validate service_id if provided
    if ($service_id > 0) {
        $checkService = $pdo->prepare("SELECT id FROM energy_services WHERE id = ? AND status = 'active'");
        $checkService->execute([$service_id]);
        if (!$checkService->fetch()) {
            $errors[] = 'Selected service is invalid.';
            $service_id = 0;
        }
    }

    if (empty($errors)) {
        $insertStmt = $pdo->prepare("
            INSERT INTO service_inquiries (service_id, company_name, contact_person, email, phone, message, status)
            VALUES (?, ?, ?, ?, ?, ?, 'new')
        ");
        $insertStmt->execute([
            $service_id > 0 ? $service_id : null,
            $company_name,
            $contact_person,
            $email,
            $phone,
            $message
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
            <h1>Service Inquiry</h1>
            <p>Request information about our energy services and solutions.</p>
            <div class="breadcrumb">
                <a href="<?= SITE_URL ?>">Home</a>
                <span>/</span>
                <a href="<?= SITE_URL ?>/energy.php">Energy</a>
                <span>/</span>
                <span>Service Inquiry</span>
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
                <h1>Inquiry Submitted!</h1>
                <p>Thank you for your interest in Renew Energy services. Our team will review your inquiry and get back to you within 24-48 hours.</p>
                <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                    <a href="<?= SITE_URL ?>/energy.php" class="btn btn-energy">Back to Energy <i class="fas fa-arrow-right"></i></a>
                    <a href="<?= SITE_URL ?>/energy-catalogue.php" class="btn btn-dark">Browse Products</a>
                </div>
            </div>
            <?php else: ?>
            <!-- Inquiry Form -->
            <div class="form-card">
                <h2><i class="fas fa-bolt" style="color: var(--energy); margin-right: 10px;"></i> Request a Service</h2>

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
                        <label for="service_id">Service of Interest</label>
                        <select id="service_id" name="service_id" class="form-control">
                            <option value="0">-- General Inquiry --</option>
                            <?php foreach ($services as $svc): ?>
                            <option value="<?= $svc['id'] ?>" <?= ($preselect_service_id == $svc['id'] || (isset($_POST['service_id']) && $_POST['service_id'] == $svc['id'])) ? 'selected' : '' ?>>
                                <?= sanitize($svc['service_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="company_name">Company / Organization Name</label>
                        <input type="text" id="company_name" name="company_name" class="form-control" value="<?= sanitize($_POST['company_name'] ?? '') ?>" placeholder="Your company or organization name">
                    </div>

                    <div class="form-group">
                        <label for="contact_person">Contact Person *</label>
                        <input type="text" id="contact_person" name="contact_person" class="form-control" required value="<?= sanitize($_POST['contact_person'] ?? '') ?>" placeholder="Your full name">
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
                        <label for="message">Message / Requirements *</label>
                        <textarea id="message" name="message" class="form-control" required placeholder="Describe your energy needs, project scope, or any specific questions..."><?= sanitize($_POST['message'] ?? '') ?></textarea>
                    </div>

                    <div style="margin-top: 25px;">
                        <button type="submit" class="btn btn-energy btn-lg" style="width: 100%; justify-content: center;">
                            <i class="fas fa-paper-plane"></i> Submit Inquiry
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
