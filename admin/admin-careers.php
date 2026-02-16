<?php
require_once 'auth.php';
$pageTitle = 'Careers';
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$success = $_GET['success'] ?? '';

$divisions = $pdo->query("SELECT id, division_name FROM divisions WHERE status = 'active' ORDER BY id")->fetchAll();

// Delete
if ($action === 'delete' && $id > 0) {
    if (!verifyCSRFToken($_GET['token'] ?? '')) {
        header('Location: admin-careers.php?success=error');
        exit;
    }
    $pdo->prepare("DELETE FROM careers WHERE id = ?")->execute([$id]);
    header('Location: admin-careers.php?success=deleted');
    exit;
}

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission.';
    } else {
        $job_title = trim($_POST['job_title'] ?? '');
        $slug = createSlug($job_title);
        $department = sanitize($_POST['department'] ?? '');
        $division_id = $_POST['division_id'] ? (int)$_POST['division_id'] : null;
        $location = sanitize($_POST['location'] ?? '');
        $employment_type = $_POST['employment_type'] ?? 'full_time';
        $description = trim($_POST['description'] ?? '');
        $requirements = trim($_POST['requirements'] ?? '');
        $responsibilities = trim($_POST['responsibilities'] ?? '');
        $salary_range = sanitize($_POST['salary_range'] ?? '');
        $application_deadline = $_POST['application_deadline'] ?? null;
        $status = $_POST['status'] ?? 'open';
        $editId = (int)($_POST['edit_id'] ?? 0);

        if (!$job_title) {
            $error = 'Job title is required.';
        } else {
            if ($editId > 0) {
                $stmt = $pdo->prepare("UPDATE careers SET job_title=?, slug=?, department=?, division_id=?, location=?, employment_type=?, description=?, requirements=?, responsibilities=?, salary_range=?, application_deadline=?, status=? WHERE id=?");
                $stmt->execute([$job_title, $slug, $department, $division_id, $location, $employment_type, $description, $requirements, $responsibilities, $salary_range, $application_deadline, $status, $editId]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO careers (job_title, slug, department, division_id, location, employment_type, description, requirements, responsibilities, salary_range, application_deadline, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$job_title, $slug, $department, $division_id, $location, $employment_type, $description, $requirements, $responsibilities, $salary_range, $application_deadline, $status]);
            }
            header('Location: admin-careers.php?success=saved');
            exit;
        }
    }
}

$editData = null;
if ($action === 'edit' && $id > 0) {
    $editData = $pdo->prepare("SELECT * FROM careers WHERE id = ?");
    $editData->execute([$id]);
    $editData = $editData->fetch();
    if (!$editData) { header('Location: admin-careers.php'); exit; }
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page-header">
    <h1><?= $action === 'add' ? 'Add Job Posting' : ($action === 'edit' ? 'Edit Job Posting' : 'Careers') ?></h1>
    <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn-admin btn-admin-primary"><i class="fas fa-plus"></i> Add Job</a>
    <?php else: ?>
    <a href="admin-careers.php" class="btn-admin btn-admin-outline"><i class="fas fa-arrow-left"></i> Back to List</a>
    <?php endif; ?>
</div>

<?php if ($success === 'saved'): ?>
<div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Job posting saved.</div>
<?php elseif ($success === 'deleted'): ?>
<div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Job posting deleted.</div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="admin-alert admin-alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
<?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="admin-card">
    <div class="admin-card-body">
        <form method="POST" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="edit_id" value="<?= $editData['id'] ?? 0 ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Job Title *</label>
                    <input type="text" name="job_title" class="form-control" value="<?= sanitize($editData['job_title'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" name="department" class="form-control" value="<?= sanitize($editData['department'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Division</label>
                    <select name="division_id" class="form-control">
                        <option value="">Corporate / General</option>
                        <?php foreach ($divisions as $div): ?>
                        <option value="<?= $div['id'] ?>" <?= ($editData['division_id'] ?? '') == $div['id'] ? 'selected' : '' ?>><?= sanitize($div['division_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control" value="<?= sanitize($editData['location'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Employment Type</label>
                    <select name="employment_type" class="form-control">
                        <?php foreach (['full_time'=>'Full Time','part_time'=>'Part Time','contract'=>'Contract','internship'=>'Internship'] as $val=>$label): ?>
                        <option value="<?= $val ?>" <?= ($editData['employment_type'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Salary Range</label>
                    <input type="text" name="salary_range" class="form-control" value="<?= sanitize($editData['salary_range'] ?? '') ?>" placeholder="e.g. $50,000 - $70,000">
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4"><?= sanitize($editData['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Requirements</label>
                <textarea name="requirements" class="form-control" rows="5"><?= sanitize($editData['requirements'] ?? '') ?></textarea>
                <div class="form-hint">One requirement per line.</div>
            </div>

            <div class="form-group">
                <label>Responsibilities</label>
                <textarea name="responsibilities" class="form-control" rows="5"><?= sanitize($editData['responsibilities'] ?? '') ?></textarea>
                <div class="form-hint">One responsibility per line.</div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Application Deadline</label>
                    <input type="date" name="application_deadline" class="form-control" value="<?= $editData['application_deadline'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="open" <?= ($editData['status'] ?? '') === 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="closed" <?= ($editData['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> <?= $editData ? 'Update' : 'Create' ?> Job Posting</button>
        </form>
    </div>
</div>

<?php else: ?>
<?php
$jobs = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM job_applications WHERE career_id = c.id) as app_count FROM careers c ORDER BY c.created_at DESC")->fetchAll();
?>

<div class="admin-card">
    <div class="admin-card-body">
        <?php if (empty($jobs)): ?>
        <div class="empty-state">
            <i class="fas fa-briefcase"></i>
            <h3>No job postings yet</h3>
            <p>Click "Add Job" to create your first posting.</p>
        </div>
        <?php else: ?>
        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Department</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Applications</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td><strong><?= sanitize($job['job_title']) ?></strong></td>
                        <td><?= sanitize($job['department'] ?: '—') ?></td>
                        <td><?= sanitize($job['location'] ?: '—') ?></td>
                        <td><?= str_replace('_', ' ', ucfirst($job['employment_type'])) ?></td>
                        <td>
                            <a href="admin-applications.php?career_id=<?= $job['id'] ?>" style="color:var(--admin-accent);font-weight:600;">
                                <?= $job['app_count'] ?> <i class="fas fa-external-link-alt" style="font-size:0.7rem;"></i>
                            </a>
                        </td>
                        <td><?= $job['application_deadline'] ? date('M j, Y', strtotime($job['application_deadline'])) : '—' ?></td>
                        <td><span class="badge <?= $job['status'] === 'open' ? 'badge-success' : 'badge-danger' ?>"><?= ucfirst($job['status']) ?></span></td>
                        <td>
                            <div class="actions">
                                <a href="?action=edit&id=<?= $job['id'] ?>" class="btn-admin btn-admin-sm btn-admin-outline"><i class="fas fa-edit"></i></a>
                                <a href="?action=delete&id=<?= $job['id'] ?>&token=<?= generateCSRFToken() ?>" class="btn-admin btn-admin-sm btn-admin-danger" data-confirm="Delete this job posting and all its applications?"><i class="fas fa-trash"></i></a>
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
