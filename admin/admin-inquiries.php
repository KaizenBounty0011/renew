<?php
require_once 'auth.php';
$pageTitle = 'Inquiries';
$tab = $_GET['tab'] ?? 'contact';
$success = $_GET['success'] ?? '';

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inq_id'], $_POST['new_status'], $_POST['inq_type'])) {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $type = $_POST['inq_type'];
        if ($type === 'contact') {
            $pdo->prepare("UPDATE contact_inquiries SET status = ? WHERE id = ?")->execute([$_POST['new_status'], (int)$_POST['inq_id']]);
        } else {
            $pdo->prepare("UPDATE service_inquiries SET status = ? WHERE id = ?")->execute([$_POST['new_status'], (int)$_POST['inq_id']]);
        }
        header("Location: admin-inquiries.php?tab={$tab}&success=updated");
        exit;
    }
}

// Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (verifyCSRFToken($_GET['token'] ?? '')) {
        if ($tab === 'contact') {
            $pdo->prepare("DELETE FROM contact_inquiries WHERE id = ?")->execute([(int)$_GET['id']]);
        } else {
            $pdo->prepare("DELETE FROM service_inquiries WHERE id = ?")->execute([(int)$_GET['id']]);
        }
        header("Location: admin-inquiries.php?tab={$tab}&success=deleted");
        exit;
    }
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page-header">
    <h1>Inquiries</h1>
</div>

<?php if ($success === 'updated'): ?>
<div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Status updated.</div>
<?php elseif ($success === 'deleted'): ?>
<div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Inquiry deleted.</div>
<?php endif; ?>

<div class="admin-tabs">
    <a href="?tab=contact" class="admin-tab <?= $tab === 'contact' ? 'active' : '' ?>">Contact Inquiries</a>
    <a href="?tab=service" class="admin-tab <?= $tab === 'service' ? 'active' : '' ?>">Service Inquiries</a>
</div>

<div class="admin-card">
    <div class="admin-card-body">
<?php if ($tab === 'contact'): ?>
<?php
$inquiries = $pdo->query("SELECT * FROM contact_inquiries ORDER BY submitted_at DESC")->fetchAll();
?>
    <?php if (empty($inquiries)): ?>
    <div class="empty-state"><i class="fas fa-envelope"></i><h3>No contact inquiries yet</h3></div>
    <?php else: ?>
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead><tr><th>Name</th><th>Email</th><th>Department</th><th>Subject</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($inquiries as $inq): ?>
                <tr>
                    <td><strong><?= sanitize($inq['name']) ?></strong><div class="form-hint"><?= sanitize($inq['phone'] ?: '') ?></div></td>
                    <td><a href="mailto:<?= sanitize($inq['email']) ?>"><?= sanitize($inq['email']) ?></a></td>
                    <td><?= sanitize($inq['department'] ?: '—') ?></td>
                    <td><?= sanitize($inq['subject'] ?: '—') ?></td>
                    <td>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <input type="hidden" name="inq_id" value="<?= $inq['id'] ?>">
                            <input type="hidden" name="inq_type" value="contact">
                            <select name="new_status">
                                <?php foreach (['new','read','responded'] as $s): ?>
                                <option value="<?= $s ?>" <?= $inq['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit"><i class="fas fa-check"></i></button>
                        </form>
                    </td>
                    <td><?= date('M j, Y', strtotime($inq['submitted_at'])) ?></td>
                    <td>
                        <div class="actions">
                            <button class="btn-admin btn-admin-sm btn-admin-outline" onclick="showMessage(<?= htmlspecialchars(json_encode([
                                'name' => $inq['name'],
                                'email' => $inq['email'],
                                'phone' => $inq['phone'],
                                'department' => $inq['department'],
                                'subject' => $inq['subject'],
                                'message' => $inq['message'],
                                'date' => date('M j, Y g:ia', strtotime($inq['submitted_at']))
                            ]), ENT_QUOTES, 'UTF-8') ?>)"><i class="fas fa-eye"></i></button>
                            <a href="?tab=contact&action=delete&id=<?= $inq['id'] ?>&token=<?= generateCSRFToken() ?>" class="btn-admin btn-admin-sm btn-admin-danger" data-confirm="Delete this inquiry?"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

<?php else: ?>
<?php
$inquiries = $pdo->query("SELECT si.*, es.service_name FROM service_inquiries si LEFT JOIN energy_services es ON si.service_id = es.id ORDER BY si.inquiry_date DESC")->fetchAll();
?>
    <?php if (empty($inquiries)): ?>
    <div class="empty-state"><i class="fas fa-bolt"></i><h3>No service inquiries yet</h3></div>
    <?php else: ?>
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead><tr><th>Contact Person</th><th>Company</th><th>Email</th><th>Service</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($inquiries as $inq): ?>
                <tr>
                    <td><strong><?= sanitize($inq['contact_person']) ?></strong><div class="form-hint"><?= sanitize($inq['phone'] ?: '') ?></div></td>
                    <td><?= sanitize($inq['company_name'] ?: '—') ?></td>
                    <td><a href="mailto:<?= sanitize($inq['email']) ?>"><?= sanitize($inq['email']) ?></a></td>
                    <td><?= sanitize($inq['service_name'] ?? 'General') ?></td>
                    <td>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <input type="hidden" name="inq_id" value="<?= $inq['id'] ?>">
                            <input type="hidden" name="inq_type" value="service">
                            <select name="new_status">
                                <?php foreach (['new','responded','closed'] as $s): ?>
                                <option value="<?= $s ?>" <?= $inq['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit"><i class="fas fa-check"></i></button>
                        </form>
                    </td>
                    <td><?= date('M j, Y', strtotime($inq['inquiry_date'])) ?></td>
                    <td>
                        <div class="actions">
                            <button class="btn-admin btn-admin-sm btn-admin-outline" onclick="showMessage(<?= htmlspecialchars(json_encode([
                                'name' => $inq['contact_person'],
                                'email' => $inq['email'],
                                'phone' => $inq['phone'],
                                'department' => $inq['company_name'] ?: 'N/A',
                                'subject' => $inq['service_name'] ?: 'General Inquiry',
                                'message' => $inq['message'],
                                'date' => date('M j, Y g:ia', strtotime($inq['inquiry_date']))
                            ]), ENT_QUOTES, 'UTF-8') ?>)"><i class="fas fa-eye"></i></button>
                            <a href="?tab=service&action=delete&id=<?= $inq['id'] ?>&token=<?= generateCSRFToken() ?>" class="btn-admin btn-admin-sm btn-admin-danger" data-confirm="Delete this inquiry?"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
<?php endif; ?>
    </div>
</div>

<!-- Message Modal -->
<div class="admin-modal-overlay" id="messageModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3>Inquiry Details</h3>
            <button class="admin-modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="admin-modal-body">
            <div class="detail-row"><span class="detail-label">From</span><span class="detail-value" id="modalName"></span></div>
            <div class="detail-row"><span class="detail-label">Email</span><span class="detail-value" id="modalEmail"></span></div>
            <div class="detail-row"><span class="detail-label">Phone</span><span class="detail-value" id="modalPhone"></span></div>
            <div class="detail-row"><span class="detail-label">Department</span><span class="detail-value" id="modalDept"></span></div>
            <div class="detail-row"><span class="detail-label">Subject</span><span class="detail-value" id="modalSubject"></span></div>
            <div class="detail-row"><span class="detail-label">Date</span><span class="detail-value" id="modalDate"></span></div>
            <div style="margin-top:15px;padding:15px;background:#f8f9fa;border-radius:8px;">
                <strong style="font-size:0.85rem;color:var(--admin-gray);">Message</strong>
                <p id="modalMessage" style="margin-top:8px;white-space:pre-wrap;line-height:1.6;"></p>
            </div>
        </div>
    </div>
</div>

<script>
function showMessage(data) {
    document.getElementById('modalName').textContent = data.name || '—';
    document.getElementById('modalEmail').textContent = data.email || '—';
    document.getElementById('modalPhone').textContent = data.phone || '—';
    document.getElementById('modalDept').textContent = data.department || '—';
    document.getElementById('modalSubject').textContent = data.subject || '—';
    document.getElementById('modalDate').textContent = data.date || '—';
    document.getElementById('modalMessage').textContent = data.message || '—';
    document.getElementById('messageModal').classList.add('active');
}

function closeModal() {
    document.getElementById('messageModal').classList.remove('active');
}

document.getElementById('messageModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php require_once 'includes/admin-footer.php'; ?>
