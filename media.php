<?php
require_once 'config.php';
$pageTitle = 'Media Gallery';

$filter = $_GET['category'] ?? '';
$where = "WHERE status = 'active'";
$params = [];
if ($filter) {
    $where .= " AND category = ?";
    $params[] = $filter;
}

$stmt = $pdo->prepare("SELECT * FROM media_gallery $where ORDER BY upload_date DESC");
$stmt->execute($params);
$media = $stmt->fetchAll();

$categories = $pdo->query("SELECT DISTINCT category FROM media_gallery WHERE status = 'active' AND category IS NOT NULL ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

require_once 'includes/header.php';
?>

    <section class="page-banner">
        <div class="container">
            <h1>Media Gallery</h1>
            <p>Photos and videos from across the Renew Empire group.</p>
            <div class="breadcrumb">
                <a href="<?= SITE_URL ?>">Home</a><span>/</span><span>Media</span>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <?php if (!empty($categories)): ?>
            <div class="filter-bar">
                <a href="<?= SITE_URL ?>/media.php" class="filter-btn <?= !$filter ? 'active' : '' ?>">All</a>
                <?php foreach ($categories as $cat): ?>
                <a href="<?= SITE_URL ?>/media.php?category=<?= urlencode($cat) ?>" class="filter-btn <?= $filter === $cat ? 'active' : '' ?>"><?= sanitize($cat) ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (empty($media)): ?>
            <div class="text-center" style="padding: 60px 0;">
                <i class="fas fa-images" style="font-size: 3rem; color: var(--gray-light); margin-bottom: 15px;"></i>
                <h3>No media items yet</h3>
                <p style="color: var(--text-light);">Check back soon for photos and videos.</p>
            </div>
            <?php else: ?>
            <div class="gallery-grid">
                <?php foreach ($media as $item): ?>
                <div class="gallery-item" style="background-image: url('<?= SITE_URL ?>/<?= $item['file_path'] ?>');">
                    <div class="gallery-overlay">
                        <?php if ($item['media_type'] === 'video'): ?>
                        <i class="fas fa-play-circle"></i>
                        <?php else: ?>
                        <i class="fas fa-search-plus"></i>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
