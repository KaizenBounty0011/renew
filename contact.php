<?php
require_once 'config.php';
$pageTitle = 'Contact Us';
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission.';
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $department = sanitize($_POST['department'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        if (!$name || !$email || !$message) {
            $error = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO contact_inquiries (name, email, phone, department, subject, message) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $department, $subject, $message]);
            $success = true;
        }
    }
}

require_once 'includes/header.php';
?>

    <section class="page-banner">
        <div class="container">
            <h1>Contact Us</h1>
            <p>We'd love to hear from you. Reach out to our team.</p>
            <div class="breadcrumb">
                <a href="<?= SITE_URL ?>">Home</a><span>/</span><span>Contact</span>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <?php if ($success): ?>
            <div class="success-page">
                <div class="success-icon"><i class="fas fa-check"></i></div>
                <h1>Message Sent!</h1>
                <p>Thank you for reaching out. Our team will respond to your inquiry within 24-48 hours.</p>
                <a href="<?= SITE_URL ?>" class="btn btn-primary" style="margin-top: 20px;">Back to Home</a>
            </div>
            <?php else: ?>
            <div class="contact-grid">
                <!-- Contact Info -->
                <div>
                    <h2 style="font-family: var(--font-display); margin-bottom: 25px;">Get In Touch</h2>
                    <p style="color: var(--text-light); margin-bottom: 30px;">Have a question, partnership inquiry, or feedback? Fill out the form and our team will get back to you promptly.</p>

                    <div class="contact-info-card">
                        <div class="contact-info-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <h4>Head Office</h4>
                            <p><?= getSetting('site_address') ?></p>
                        </div>
                    </div>
                    <div class="contact-info-card">
                        <div class="contact-info-icon"><i class="fas fa-phone"></i></div>
                        <div>
                            <h4>Phone</h4>
                            <p><?= getSetting('site_phone') ?></p>
                        </div>
                    </div>
                    <div class="contact-info-card">
                        <div class="contact-info-icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <h4>Email</h4>
                            <p><?= getSetting('site_email') ?></p>
                        </div>
                    </div>
                    <div class="contact-info-card">
                        <div class="contact-info-icon"><i class="fas fa-clock"></i></div>
                        <div>
                            <h4>Business Hours</h4>
                            <p>Mon - Fri: 8:00 AM - 6:00 PM<br>Sat: 9:00 AM - 2:00 PM</p>
                        </div>
                    </div>

                    <div class="map-placeholder">
                        <div style="text-align: center;">
                            <i class="fas fa-map-marked-alt" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <p>Google Maps Integration</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div>
                    <div class="form-card" style="max-width: none;">
                        <h2>Send Us a Message</h2>
                        <?php if ($error): ?>
                        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
                        <?php endif; ?>
                        <form method="POST" data-validate>
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Full Name *</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Email *</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="tel" name="phone" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Department</label>
                                    <select name="department" class="form-control">
                                        <option value="">Select Department</option>
                                        <option value="General">General Inquiry</option>
                                        <option value="Fight Championship">Fight Championship</option>
                                        <option value="Entertainment">Entertainment</option>
                                        <option value="Hotels">Hotels</option>
                                        <option value="Energy">Energy</option>
                                        <option value="Careers">Careers</option>
                                        <option value="Partnerships">Partnerships & Investment</option>
                                        <option value="Media">Media & Press</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Subject</label>
                                <input type="text" name="subject" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Message *</label>
                                <textarea name="message" class="form-control" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Send Message <i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
