<?php
require_once 'auth.php';
$pageTitle = 'Settings';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $success = 'error';
    } else {
        $settings = [
            'site_name', 'site_tagline', 'footer_text', 'meta_description',
            'site_email', 'site_phone', 'site_address',
            'facebook', 'twitter', 'instagram', 'linkedin', 'youtube',
            'admin_security_key'
        ];
        $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
        foreach ($settings as $key) {
            if (isset($_POST[$key])) {
                $stmt->execute([trim($_POST[$key]), $key]);
            }
        }
        $success = 'saved';
    }
}

$allSettings = [];
$rows = $pdo->query("SELECT setting_key, setting_value, category FROM site_settings ORDER BY id")->fetchAll();
foreach ($rows as $row) {
    $allSettings[$row['setting_key']] = $row;
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page-header">
    <h1>Site Settings</h1>
</div>

<?php if ($success === 'saved'): ?>
<div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Settings saved successfully.</div>
<?php elseif ($success === 'error'): ?>
<div class="admin-alert admin-alert-error"><i class="fas fa-exclamation-circle"></i> Invalid form submission.</div>
<?php endif; ?>

<form method="POST" class="admin-form">
    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

    <div class="admin-card">
        <div class="admin-card-header"><h2>General</h2></div>
        <div class="admin-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Site Name</label>
                    <input type="text" name="site_name" class="form-control" value="<?= sanitize($allSettings['site_name']['setting_value'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Tagline</label>
                    <input type="text" name="site_tagline" class="form-control" value="<?= sanitize($allSettings['site_tagline']['setting_value'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Footer Text</label>
                <input type="text" name="footer_text" class="form-control" value="<?= sanitize($allSettings['footer_text']['setting_value'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Meta Description</label>
                <textarea name="meta_description" class="form-control" rows="3"><?= sanitize($allSettings['meta_description']['setting_value'] ?? '') ?></textarea>
                <div class="form-hint">Used for SEO. Keep under 160 characters.</div>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header"><h2>Contact Information</h2></div>
        <div class="admin-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="site_email" class="form-control" value="<?= sanitize($allSettings['site_email']['setting_value'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="site_phone" class="form-control" value="<?= sanitize($allSettings['site_phone']['setting_value'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea name="site_address" class="form-control" rows="2"><?= sanitize($allSettings['site_address']['setting_value'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header"><h2>Social Media</h2></div>
        <div class="admin-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fab fa-facebook-f"></i> Facebook URL</label>
                    <input type="url" name="facebook" class="form-control" value="<?= sanitize($allSettings['facebook']['setting_value'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label><i class="fab fa-x-twitter"></i> Twitter / X URL</label>
                    <input type="url" name="twitter" class="form-control" value="<?= sanitize($allSettings['twitter']['setting_value'] ?? '') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fab fa-instagram"></i> Instagram URL</label>
                    <input type="url" name="instagram" class="form-control" value="<?= sanitize($allSettings['instagram']['setting_value'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label><i class="fab fa-linkedin-in"></i> LinkedIn URL</label>
                    <input type="url" name="linkedin" class="form-control" value="<?= sanitize($allSettings['linkedin']['setting_value'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label><i class="fab fa-youtube"></i> YouTube URL</label>
                <input type="url" name="youtube" class="form-control" value="<?= sanitize($allSettings['youtube']['setting_value'] ?? '') ?>">
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header"><h2><i class="fas fa-shield-alt"></i> Security</h2></div>
        <div class="admin-card-body">
            <div class="form-group">
                <label>Admin Security Key</label>
                <input type="text" name="admin_security_key" class="form-control" value="<?= sanitize($allSettings['admin_security_key']['setting_value'] ?? '') ?>" style="font-family:'Courier New',monospace;letter-spacing:1px;">
                <div class="form-hint">Required during admin login. Share only with authorized personnel.</div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> Save Settings</button>
</form>

<?php require_once 'includes/admin-footer.php'; ?>
