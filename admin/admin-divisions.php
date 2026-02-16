<?php
require_once 'auth.php';
$pageTitle = 'Divisions';
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$success = $_GET['success'] ?? '';

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission.';
    } else {
        $editId = (int)($_POST['edit_id'] ?? 0);
        $division_name = trim($_POST['division_name'] ?? '');
        $slug = createSlug($division_name);
        $tagline = sanitize($_POST['tagline'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $content = $_POST['content'] ?? '';
        $accent_color = $_POST['accent_color'] ?? '#e8491d';
        $status = $_POST['status'] ?? 'active';

        // Handle 3 hero images
        $heroImages = [];
        for ($i = 1; $i <= 3; $i++) {
            $heroImages[$i] = $_POST["existing_hero{$i}"] ?? '';
            if (!empty($_FILES["hero_image{$i}"]['name']) && $_FILES["hero_image{$i}"]['error'] === UPLOAD_ERR_OK) {
                if ($_FILES["hero_image{$i}"]['size'] > 5 * 1024 * 1024) {
                    $error = "Hero image {$i} must be under 5MB.";
                    break;
                }
                $ext = strtolower(pathinfo($_FILES["hero_image{$i}"]['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
                    $filename = 'div_' . $slug . '_' . $i . '_' . time() . '.' . $ext;
                    $dest = __DIR__ . '/../uploads/divisions/' . $filename;
                    if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0777, true);
                    move_uploaded_file($_FILES["hero_image{$i}"]['tmp_name'], $dest);
                    $heroImages[$i] = 'uploads/divisions/' . $filename;
                }
            }
        }

        if (!$division_name) {
            $error = 'Division name is required.';
        } elseif (empty($error)) {
            $stmt = $pdo->prepare("UPDATE divisions SET division_name=?, slug=?, tagline=?, description=?, hero_image1=?, hero_image2=?, hero_image3=?, content=?, accent_color=?, status=? WHERE id=?");
            $stmt->execute([$division_name, $slug, $tagline, $description, $heroImages[1], $heroImages[2], $heroImages[3], $content, $accent_color, $status, $editId]);
            header('Location: admin-divisions.php?success=saved');
            exit;
        }
    }
}

$editData = null;
if ($action === 'edit' && $id > 0) {
    $editData = $pdo->prepare("SELECT * FROM divisions WHERE id = ?");
    $editData->execute([$id]);
    $editData = $editData->fetch();
    if (!$editData) { header('Location: admin-divisions.php'); exit; }
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page-header">
    <h1><?= $action === 'edit' ? 'Edit Division' : 'Divisions' ?></h1>
    <?php if ($action === 'edit'): ?>
    <a href="admin-divisions.php" class="btn-admin btn-admin-outline"><i class="fas fa-arrow-left"></i> Back to List</a>
    <?php endif; ?>
</div>

<?php if ($success === 'saved'): ?>
<div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Division saved successfully.</div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="admin-alert admin-alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
<?php endif; ?>

<?php if ($action === 'edit' && $editData): ?>
<div class="admin-card">
    <div class="admin-card-body">
        <form method="POST" class="admin-form" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="edit_id" value="<?= $editData['id'] ?>">
            <input type="hidden" name="existing_hero1" value="<?= sanitize($editData['hero_image1'] ?? '') ?>">
            <input type="hidden" name="existing_hero2" value="<?= sanitize($editData['hero_image2'] ?? '') ?>">
            <input type="hidden" name="existing_hero3" value="<?= sanitize($editData['hero_image3'] ?? '') ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Division Name *</label>
                    <input type="text" name="division_name" class="form-control" value="<?= sanitize($editData['division_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Tagline</label>
                    <input type="text" name="tagline" class="form-control" value="<?= sanitize($editData['tagline'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Accent Color</label>
                    <div style="display:flex;gap:10px;align-items:center;">
                        <input type="color" name="accent_color" value="<?= $editData['accent_color'] ?? '#e8491d' ?>" style="width:50px;height:38px;border:1px solid var(--admin-border);border-radius:8px;cursor:pointer;">
                        <span style="color:var(--admin-gray);font-size:0.85rem;"><?= $editData['accent_color'] ?? '#e8491d' ?></span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="active" <?= $editData['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $editData['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"><?= sanitize($editData['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Detailed Content</label>
                <textarea name="content" class="form-control" rows="8"><?= htmlspecialchars($editData['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                <div class="form-hint">HTML supported. Shown on division detail pages.</div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;">
                <?php for ($i = 1; $i <= 3; $i++): ?>
                <div class="form-group">
                    <label>Hero Image <?= $i ?></label>
                    <input type="file" name="hero_image<?= $i ?>" class="form-control" accept="image/*">
                    <?php if (!empty($editData["hero_image{$i}"])): ?>
                    <div class="form-hint">Current: <?= $editData["hero_image{$i}"] ?></div>
                    <?php endif; ?>
                </div>
                <?php endfor; ?>
            </div>

            <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> Update Division</button>
        </form>
    </div>
</div>

<?php else: ?>
<?php $divisions = $pdo->query("SELECT * FROM divisions ORDER BY id")->fetchAll(); ?>

<div class="admin-card">
    <div class="admin-card-body">
        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Color</th>
                        <th>Division</th>
                        <th>Tagline</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($divisions as $div): ?>
                    <tr>
                        <td><span class="color-swatch" style="background:<?= sanitize($div['accent_color']) ?>;"></span></td>
                        <td><strong><?= sanitize($div['division_name']) ?></strong></td>
                        <td><?= sanitize($div['tagline'] ?? '—') ?></td>
                        <td><span class="badge <?= $div['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= ucfirst($div['status']) ?></span></td>
                        <td>
                            <a href="?action=edit&id=<?= $div['id'] ?>" class="btn-admin btn-admin-sm btn-admin-outline"><i class="fas fa-edit"></i> Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/admin-footer.php'; ?>
