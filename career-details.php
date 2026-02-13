<?php
require_once 'config.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) { header('Location: ' . SITE_URL . '/careers.php'); exit; }

$stmt = $pdo->prepare("SELECT c.*, d.division_name FROM careers c LEFT JOIN divisions d ON c.division_id = d.id WHERE c.slug = ?");
$stmt->execute([$slug]);
$job = $stmt->fetch();

if (!$job) { header('Location: ' . SITE_URL . '/careers.php'); exit; }

$pageTitle = $job['job_title'];
$success = false;
$error = '';

// Handle application submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $name = sanitize($_POST['applicant_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $coverLetter = sanitize($_POST['cover_letter'] ?? '');

        if (!$name || !$email) {
            $error = 'Please fill in all required fields.';
        } else {
            $resumePath = '';
            if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['pdf', 'doc', 'docx'];
                $ext = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    $error = 'Resume must be PDF, DOC, or DOCX format.';
                } elseif ($_FILES['resume']['size'] > 5 * 1024 * 1024) {
                    $error = 'Resume file must be under 5MB.';
                } else {
                    $filename = 'resume_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $dest = UPLOAD_DIR . 'resumes/' . $filename;
                    if (move_uploaded_file($_FILES['resume']['tmp_name'], $dest)) {
                        $resumePath = 'uploads/resumes/' . $filename;
                    }
                }
            }

            if (!$error) {
                $stmt = $pdo->prepare("INSERT INTO job_applications (career_id, applicant_name, email, phone, resume_path, cover_letter) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$job['id'], $name, $email, $phone, $resumePath, $coverLetter]);
                $success = true;
            }
        }
    }
}

require_once 'includes/header.php';
?>

    <section class="page-banner">
        <div class="container">
            <h1><?= sanitize($job['job_title']) ?></h1>
            <div class="breadcrumb">
                <a href="<?= SITE_URL ?>">Home</a><span>/</span>
                <a href="<?= SITE_URL ?>/careers.php">Careers</a><span>/</span>
                <span><?= sanitize($job['job_title']) ?></span>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <?php if ($success): ?>
            <div class="success-page">
                <div class="success-icon"><i class="fas fa-check"></i></div>
                <h1>Application Submitted!</h1>
                <p>Thank you for applying for <strong><?= sanitize($job['job_title']) ?></strong>.</p>
                <p>We'll review your application and get back to you soon.</p>
                <a href="<?= SITE_URL ?>/careers.php" class="btn btn-primary" style="margin-top: 20px;">Browse More Jobs</a>
            </div>
            <?php else: ?>
            <div style="display: grid; grid-template-columns: 1fr 380px; gap: 40px;">
                <!-- Job Details -->
                <div class="detail-content" style="max-width: none;">
                    <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 25px;">
                        <span class="tag tag-open"><?= $job['status'] === 'open' ? 'Accepting Applications' : 'Closed' ?></span>
                    </div>

                    <div class="career-meta" style="margin-bottom: 25px;">
                        <span><i class="fas fa-building"></i> <?= sanitize($job['department']) ?></span>
                        <span><i class="fas fa-map-marker-alt"></i> <?= sanitize($job['location']) ?></span>
                        <span><i class="fas fa-clock"></i> <?= ucfirst(str_replace('_', ' ', $job['employment_type'])) ?></span>
                        <span><i class="fas fa-money-bill-wave"></i> <?= sanitize($job['salary_range']) ?></span>
                        <?php if ($job['application_deadline']): ?>
                        <span><i class="fas fa-calendar-times"></i> Deadline: <?= date('M d, Y', strtotime($job['application_deadline'])) ?></span>
                        <?php endif; ?>
                    </div>

                    <h2>Description</h2>
                    <p><?= nl2br(sanitize($job['description'])) ?></p>

                    <?php if ($job['responsibilities']): ?>
                    <h2>Responsibilities</h2>
                    <ul>
                        <?php foreach (explode("\n", $job['responsibilities']) as $line): ?>
                        <?php if (trim($line)): ?><li><?= sanitize(trim($line)) ?></li><?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                    <?php if ($job['requirements']): ?>
                    <h2>Requirements</h2>
                    <ul>
                        <?php foreach (explode("\n", $job['requirements']) as $line): ?>
                        <?php if (trim($line)): ?><li><?= sanitize(trim($line)) ?></li><?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>

                <!-- Application Form -->
                <div>
                    <div class="form-card" style="max-width: none; position: sticky; top: 90px;">
                        <h2>Apply Now</h2>
                        <?php if ($error): ?>
                        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
                        <?php endif; ?>
                        <form method="POST" enctype="multipart/form-data" data-validate>
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" name="applicant_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="tel" name="phone" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Resume (PDF, DOC, DOCX - Max 5MB)</label>
                                <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx">
                            </div>
                            <div class="form-group">
                                <label>Cover Letter</label>
                                <textarea name="cover_letter" class="form-control" rows="5" placeholder="Tell us why you're a great fit..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Application <i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
