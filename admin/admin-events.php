<?php
require_once 'auth.php';
$pageTitle = 'Events & Shows';
$tab = $_GET['tab'] ?? 'fights';
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$success = $_GET['success'] ?? '';

$table = $tab === 'shows' ? 'entertainment_shows' : 'fight_events';
$nameCol = $tab === 'shows' ? 'show_name' : 'event_name';
$dateCol = $tab === 'shows' ? 'show_date' : 'event_date';
$label = $tab === 'shows' ? 'Show' : 'Fight Event';

// Delete
if ($action === 'delete' && $id > 0) {
    if (verifyCSRFToken($_GET['token'] ?? '')) {
        $pdo->prepare("DELETE FROM {$table} WHERE id = ?")->execute([$id]);
        header("Location: admin-events.php?tab={$tab}&success=deleted");
        exit;
    }
}

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $slug = createSlug($name);
        $event_date = $_POST['event_date'] ?? '';
        $venue = sanitize($_POST['venue'] ?? '');
        $location = sanitize($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $ticket_price = (float)($_POST['ticket_price'] ?? 0);
        $vip_price = (float)($_POST['vip_price'] ?? 0);
        $available_tickets = (int)($_POST['available_tickets'] ?? 0);
        $status = $_POST['status'] ?? 'upcoming';
        $editId = (int)($_POST['edit_id'] ?? 0);
        $postTab = in_array($_POST['tab'] ?? '', ['fights', 'shows']) ? $_POST['tab'] : 'fights';

        $postTable = $postTab === 'shows' ? 'entertainment_shows' : 'fight_events';
        $postNameCol = $postTab === 'shows' ? 'show_name' : 'event_name';
        $postDateCol = $postTab === 'shows' ? 'show_date' : 'event_date';

        // Image upload
        $featured_image = $_POST['existing_image'] ?? '';
        if (!empty($_FILES['featured_image']['name']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['featured_image']['size'] > 5 * 1024 * 1024) {
                $error = 'Image must be under 5MB.';
            } else {
                $ext = strtolower(pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
                    $filename = $postTab . '_' . time() . '_' . rand(100,999) . '.' . $ext;
                    $dest = __DIR__ . '/../uploads/divisions/' . $filename;
                    if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0777, true);
                    move_uploaded_file($_FILES['featured_image']['tmp_name'], $dest);
                    $featured_image = 'uploads/divisions/' . $filename;
                }
            }
        }

        if (!$name) {
            $error = 'Name is required.';
        } elseif (empty($error)) {
            if ($editId > 0) {
                $stmt = $pdo->prepare("UPDATE {$postTable} SET {$postNameCol}=?, slug=?, {$postDateCol}=?, venue=?, location=?, description=?, featured_image=?, ticket_price=?, vip_price=?, available_tickets=?, status=? WHERE id=?");
                $stmt->execute([$name, $slug, $event_date, $venue, $location, $description, $featured_image, $ticket_price, $vip_price, $available_tickets, $status, $editId]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO {$postTable} ({$postNameCol}, slug, {$postDateCol}, venue, location, description, featured_image, ticket_price, vip_price, available_tickets, status) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$name, $slug, $event_date, $venue, $location, $description, $featured_image, $ticket_price, $vip_price, $available_tickets, $status]);
            }
            header("Location: admin-events.php?tab={$postTab}&success=saved");
            exit;
        }
    }
}

// Edit — load record
$editData = null;
if ($action === 'edit' && $id > 0) {
    $editData = $pdo->prepare("SELECT * FROM {$table} WHERE id = ?");
    $editData->execute([$id]);
    $editData = $editData->fetch();
    if (!$editData) { header("Location: admin-events.php?tab={$tab}"); exit; }
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page-header">
    <h1><?= $action === 'add' ? "Add {$label}" : ($action === 'edit' ? "Edit {$label}" : 'Events & Shows') ?></h1>
    <?php if ($action === 'list'): ?>
    <a href="?tab=<?= $tab ?>&action=add" class="btn-admin btn-admin-primary"><i class="fas fa-plus"></i> Add <?= $label ?></a>
    <?php else: ?>
    <a href="admin-events.php?tab=<?= $tab ?>" class="btn-admin btn-admin-outline"><i class="fas fa-arrow-left"></i> Back to List</a>
    <?php endif; ?>
</div>

<?php if ($success === 'saved'): ?>
<div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> <?= $label ?> saved successfully.</div>
<?php elseif ($success === 'deleted'): ?>
<div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> <?= $label ?> deleted.</div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="admin-alert admin-alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
<!-- Tabs -->
<div class="admin-tabs">
    <a href="?tab=fights" class="admin-tab <?= $tab === 'fights' ? 'active' : '' ?>">Fight Events</a>
    <a href="?tab=shows" class="admin-tab <?= $tab === 'shows' ? 'active' : '' ?>">Entertainment Shows</a>
</div>
<?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="admin-card">
    <div class="admin-card-body">
        <form method="POST" class="admin-form" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="edit_id" value="<?= $editData['id'] ?? 0 ?>">
            <input type="hidden" name="tab" value="<?= $tab ?>">
            <input type="hidden" name="existing_image" value="<?= sanitize($editData['featured_image'] ?? '') ?>">

            <div class="form-row">
                <div class="form-group">
                    <label><?= $label ?> Name *</label>
                    <input type="text" name="name" class="form-control" value="<?= sanitize($editData[$nameCol] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Date & Time</label>
                    <input type="datetime-local" name="event_date" class="form-control" value="<?= isset($editData[$dateCol]) ? date('Y-m-d\TH:i', strtotime($editData[$dateCol])) : '' ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Venue</label>
                    <input type="text" name="venue" class="form-control" value="<?= sanitize($editData['venue'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control" value="<?= sanitize($editData['location'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="5"><?= sanitize($editData['description'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Regular Ticket Price (₦)</label>
                    <input type="number" name="ticket_price" class="form-control" step="0.01" value="<?= $editData['ticket_price'] ?? 0 ?>">
                </div>
                <div class="form-group">
                    <label>VIP Ticket Price (₦)</label>
                    <input type="number" name="vip_price" class="form-control" step="0.01" value="<?= $editData['vip_price'] ?? 0 ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Available Tickets</label>
                    <input type="number" name="available_tickets" class="form-control" value="<?= $editData['available_tickets'] ?? 0 ?>">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <?php foreach (['upcoming','completed','cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($editData['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Featured Image</label>
                <input type="file" name="featured_image" class="form-control" accept="image/*">
                <?php if (!empty($editData['featured_image'])): ?>
                <div class="form-hint">Current: <?= $editData['featured_image'] ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> <?= $editData ? 'Update' : 'Create' ?> <?= $label ?></button>
        </form>
    </div>
</div>

<?php else: ?>
<?php
$events = $pdo->query("SELECT * FROM {$table} ORDER BY {$dateCol} DESC")->fetchAll();
?>

<div class="admin-card">
    <div class="admin-card-body">
        <?php if (empty($events)): ?>
        <div class="empty-state">
            <i class="fas fa-calendar-alt"></i>
            <h3>No <?= strtolower($label) ?>s yet</h3>
            <p>Click "Add <?= $label ?>" to create one.</p>
        </div>
        <?php else: ?>
        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Venue</th>
                        <th>Regular / VIP</th>
                        <th>Tickets</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $ev): ?>
                    <tr>
                        <td><strong><?= sanitize($ev[$nameCol]) ?></strong></td>
                        <td><?= date('M j, Y g:ia', strtotime($ev[$dateCol])) ?></td>
                        <td><?= sanitize($ev['venue'] ?: '—') ?></td>
                        <td><?= formatPrice($ev['ticket_price']) ?> / <?= formatPrice($ev['vip_price']) ?></td>
                        <td><?= number_format($ev['available_tickets']) ?></td>
                        <td>
                            <?php
                            $sBadge = match($ev['status']) {
                                'upcoming' => 'badge-info',
                                'completed' => 'badge-success',
                                'cancelled' => 'badge-danger',
                                default => 'badge-info',
                            };
                            ?>
                            <span class="badge <?= $sBadge ?>"><?= ucfirst($ev['status']) ?></span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="?tab=<?= $tab ?>&action=edit&id=<?= $ev['id'] ?>" class="btn-admin btn-admin-sm btn-admin-outline"><i class="fas fa-edit"></i></a>
                                <a href="?tab=<?= $tab ?>&action=delete&id=<?= $ev['id'] ?>&token=<?= generateCSRFToken() ?>" class="btn-admin btn-admin-sm btn-admin-danger" data-confirm="Delete this <?= strtolower($label) ?>?"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/admin-footer.php'; ?>
