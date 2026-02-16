<?php
require_once 'auth.php';
$pageTitle = 'News & Press';
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$success = $_GET['success'] ?? '';

$divisions = $pdo->query("SELECT id, division_name FROM divisions WHERE status = 'active' ORDER BY id")->fetchAll();

// Delete
if ($action === 'delete' && $id > 0) {
    if (!verifyCSRFToken($_GET['token'] ?? '')) {
        header('Location: admin-news.php?success=error');
        exit;
    }
    $pdo->prepare("DELETE FROM news WHERE id = ?")->execute([$id]);
    header('Location: admin-news.php?success=deleted');
    exit;
}

// Save (add/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $slug = createSlug($title);
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = $_POST['content'] ?? '';
        $category = sanitize($_POST['category'] ?? '');
        $division_id = $_POST['division_id'] ? (int)$_POST['division_id'] : null;
        $published_date = $_POST['published_date'] ?? date('Y-m-d');
        $status = $_POST['status'] ?? 'draft';
        $editId = (int)($_POST['edit_id'] ?? 0);

        // Handle image upload
        $featured_image = $_POST['existing_image'] ?? '';
        if (!empty($_FILES['featured_image']['name']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['featured_image']['size'] > 5 * 1024 * 1024) {
                $error = 'Image must be under 5MB.';
            } else {
                $ext = strtolower(pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
                    $filename = 'news_' . time() . '_' . rand(100,999) . '.' . $ext;
                    $dest = __DIR__ . '/../uploads/news/' . $filename;
                    if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0777, true);
                    move_uploaded_file($_FILES['featured_image']['tmp_name'], $dest);
                    $featured_image = 'uploads/news/' . $filename;
                }
            }
        }

        if (!$title) {
            $error = 'Title is required.';
        } elseif (empty($error)) {
            if ($editId > 0) {
                $stmt = $pdo->prepare("UPDATE news SET title=?, slug=?, excerpt=?, content=?, featured_image=?, category=?, division_id=?, published_date=?, status=? WHERE id=?");
                $stmt->execute([$title, $slug, $excerpt, $content, $featured_image, $category, $division_id, $published_date, $status, $editId]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO news (title, slug, excerpt, content, featured_image, author_id, category, division_id, published_date, status) VALUES (?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$title, $slug, $excerpt, $content, $featured_image, $currentAdmin['id'], $category, $division_id, $published_date, $status]);
            }
            header('Location: admin-news.php?success=saved');
            exit;
        }
    }
}

// Edit — load record
$editData = null;
if ($action === 'edit' && $id > 0) {
    $editData = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $editData->execute([$id]);
    $editData = $editData->fetch();
    if (!$editData) { header('Location: admin-news.php'); exit; }
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page-header">
    <h1><?= $action === 'add' ? 'Add Article' : ($action === 'edit' ? 'Edit Article' : 'News & Press') ?></h1>
    <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn-admin btn-admin-primary"><i class="fas fa-plus"></i> Add Article</a>
    <?php else: ?>
    <a href="admin-news.php" class="btn-admin btn-admin-outline"><i class="fas fa-arrow-left"></i> Back to List</a>
    <?php endif; ?>
</div>

<?php if ($success === 'saved'): ?>
<div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Article saved successfully.</div>
<?php elseif ($success === 'deleted'): ?>
<div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Article deleted.</div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="admin-alert admin-alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
<?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<!-- ADD / EDIT FORM -->
<div class="admin-card">
    <div class="admin-card-body">
        <form method="POST" class="admin-form" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="edit_id" value="<?= $editData['id'] ?? 0 ?>">
            <input type="hidden" name="existing_image" value="<?= sanitize($editData['featured_image'] ?? '') ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" class="form-control" value="<?= sanitize($editData['title'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" class="form-control">
                        <option value="">Select Category</option>
                        <?php foreach (['Corporate','Fight Championship','Entertainment','Hotels','Energy'] as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($editData['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Division</label>
                    <select name="division_id" class="form-control">
                        <option value="">None (Corporate)</option>
                        <?php foreach ($divisions as $div): ?>
                        <option value="<?= $div['id'] ?>" <?= ($editData['division_id'] ?? '') == $div['id'] ? 'selected' : '' ?>><?= sanitize($div['division_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Published Date</label>
                    <input type="date" name="published_date" class="form-control" value="<?= $editData['published_date'] ?? date('Y-m-d') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Excerpt</label>
                <textarea name="excerpt" class="form-control" rows="2"><?= sanitize($editData['excerpt'] ?? '') ?></textarea>
                <div class="form-hint">Short summary shown in news listings.</div>
            </div>

            <div class="form-group">
                <label>Content</label>
                <textarea name="content" class="form-control" rows="10"><?= htmlspecialchars($editData['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                <div class="form-hint">HTML content is supported.</div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Featured Image</label>
                    <input type="file" name="featured_image" class="form-control" accept="image/*">
                    <?php if (!empty($editData['featured_image'])): ?>
                    <div class="form-hint">Current: <?= $editData['featured_image'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="draft" <?= ($editData['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= ($editData['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> <?= $editData ? 'Update' : 'Publish' ?> Article</button>
        </form>
    </div>
</div>

<?php else: ?>
<!-- LIST -->
<?php
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;
$total = $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
$stmt = $pdo->prepare("SELECT n.*, a.full_name as author_name FROM news n LEFT JOIN admins a ON n.author_id = a.id ORDER BY n.created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$perPage, $offset]);
$articles = $stmt->fetchAll();
?>

<div class="admin-card">
    <div class="admin-card-body">
        <?php if (empty($articles)): ?>
        <div class="empty-state">
            <i class="fas fa-newspaper"></i>
            <h3>No articles yet</h3>
            <p>Click "Add Article" to create your first news post.</p>
        </div>
        <?php else: ?>
        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                    <tr>
                        <td>
                            <strong><?= sanitize($article['title']) ?></strong>
                            <div class="form-hint"><?= sanitize($article['author_name'] ?? 'Unknown') ?></div>
                        </td>
                        <td><?= sanitize($article['category'] ?: '—') ?></td>
                        <td>
                            <span class="badge <?= $article['status'] === 'published' ? 'badge-success' : 'badge-warning' ?>">
                                <?= ucfirst($article['status']) ?>
                            </span>
                        </td>
                        <td><?= number_format($article['views']) ?></td>
                        <td><?= $article['published_date'] ? date('M j, Y', strtotime($article['published_date'])) : '—' ?></td>
                        <td>
                            <div class="actions">
                                <a href="?action=edit&id=<?= $article['id'] ?>" class="btn-admin btn-admin-sm btn-admin-outline"><i class="fas fa-edit"></i></a>
                                <a href="?action=delete&id=<?= $article['id'] ?>&token=<?= generateCSRFToken() ?>" class="btn-admin btn-admin-sm btn-admin-danger" data-confirm="Delete this article?"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($total > $perPage): ?>
        <div class="admin-pagination">
            <?php for ($i = 1; $i <= ceil($total / $perPage); $i++): ?>
                <?php if ($i == $page): ?>
                <span><?= $i ?></span>
                <?php else: ?>
                <a href="?page=<?= $i ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/admin-footer.php'; ?>
