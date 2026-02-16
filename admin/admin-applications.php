<?php
require_once 'auth.php';
$pageTitle = 'Applications';
$career_id = (int)($_GET['career_id'] ?? 0);
$success = $_GET['success'] ?? '';

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_id'], $_POST['new_status'])) {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $stmt = $pdo->prepare("UPDATE job_applications SET status = ? WHERE id = ?");
        $stmt->execute([$_POST['new_status'], (int)$_POST['app_id']]);
        $redir = 'admin-applications.php?success=updated';
        if ($career_id) $redir .= '&career_id=' . $career_id;
        header('Location: ' . $redir);
        exit;
    }
}

// Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (verifyCSRFToken($_GET['token'] ?? '')) {
        $pdo->prepare("DELETE FROM job_applications WHERE id = ?")->execute([(int)$_GET['id']]);
        $redir = 'admin-applications.php?success=deleted';
        if ($career_id) $redir .= '&career_id=' . $career_id;
        header('Location: ' . $redir);
        exit;
    }
}

// Fetch applications
if ($career_id > 0) {
    $stmt = $pdo->prepare("SELECT ja.*, c.job_title FROM job_applications ja JOIN careers c ON ja.career_id = c.id WHERE ja.career_id = ? ORDER BY ja.applied_at DESC");
    $stmt->execute([$career_id]);
    $apps = $stmt->fetchAll();
    $jobTitle = $apps[0]['job_title'] ?? '';
    if (!$jobTitle) {
        $jt = $pdo->prepare("SELECT job_title FROM careers WHERE id = ?");
        $jt->execute([$career_id]);
        $jobTitle = $jt->fetchColumn() ?: 'Unknown';
    }
} else {
    $apps = $pdo->query("SELECT ja.*, c.job_title FROM job_applications ja JOIN careers c ON ja.career_id = c.id ORDER BY ja.applied_at DESC")->fetchAll();
    $jobTitle = '';
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page-header">
    <h1>Applications<?= $jobTitle ? ' — ' . sanitize($jobTitle) : '' ?></h1>
    <div>
        <?php if ($career_id): ?>
        <a href="admin-applications.php" class="btn-admin btn-admin-outline btn-admin-sm"><i class="fas fa-list"></i> All Applications</a>
        <?php endif; ?>
        <a href="admin-careers.php" class="btn-admin btn-admin-outline btn-admin-sm"><i class="fas fa-arrow-left"></i> Back to Careers</a>
    </div>
</div>

<?php if ($success === 'updated'): ?>
<div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Application status updated.</div>
<?php elseif ($success === 'deleted'): ?>
<div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Application deleted.</div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-body">
        <?php if (empty($apps)): ?>
        <div class="empty-state">
            <i class="fas fa-file-alt"></i>
            <h3>No applications yet</h3>
            <p>Applications will appear here when candidates apply.</p>
        </div>
        <?php else: ?>
        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <?php if (!$career_id): ?><th>Position</th><?php endif; ?>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Resume</th>
                        <th>Status</th>
                        <th>Applied</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($apps as $app): ?>
                    <tr>
                        <td><strong><?= sanitize($app['applicant_name']) ?></strong></td>
                        <?php if (!$career_id): ?>
                        <td><a href="?career_id=<?= $app['career_id'] ?>" style="color:var(--admin-accent);"><?= sanitize($app['job_title']) ?></a></td>
                        <?php endif; ?>
                        <td><a href="mailto:<?= sanitize($app['email']) ?>"><?= sanitize($app['email']) ?></a></td>
                        <td><?= sanitize($app['phone'] ?: '—') ?></td>
                        <td>
                            <?php if ($app['resume_path']): ?>
                            <a href="<?= SITE_URL ?>/<?= $app['resume_path'] ?>" target="_blank" class="btn-admin btn-admin-sm btn-admin-outline"><i class="fas fa-download"></i></a>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" class="status-form">
                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                                <?php if ($career_id): ?><input type="hidden" name="career_id_redir" value="<?= $career_id ?>"><?php endif; ?>
                                <select name="new_status">
                                    <?php foreach (['received','reviewing','shortlisted','rejected'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $app['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit"><i class="fas fa-check"></i></button>
                            </form>
                        </td>
                        <td><?= date('M j, Y', strtotime($app['applied_at'])) ?></td>
                        <td>
                            <a href="?action=delete&id=<?= $app['id'] ?>&token=<?= generateCSRFToken() ?><?= $career_id ? '&career_id='.$career_id : '' ?>" class="btn-admin btn-admin-sm btn-admin-danger" data-confirm="Delete this application?"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
