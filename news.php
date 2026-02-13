<?php
require_once 'config.php';
$pageTitle = 'News & Press';

$category = $_GET['category'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;

$where = "WHERE status = 'published'";
$params = [];
if ($category) {
    $where .= " AND category = ?";
    $params[] = $category;
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM news $where");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $pdo->prepare("SELECT * FROM news $where ORDER BY published_date DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$articles = $stmt->fetchAll();

$categories = $pdo->query("SELECT DISTINCT category FROM news WHERE status = 'published' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

require_once 'includes/header.php';
?>

    <section class="page-banner">
        <div class="container">
            <h1>News & Press Releases</h1>
            <p>Stay updated with the latest from Renew Empire and its divisions.</p>
            <div class="breadcrumb">
                <a href="<?= SITE_URL ?>">Home</a><span>/</span><span>News</span>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="filter-bar">
                <a href="<?= SITE_URL ?>/news.php" class="filter-btn <?= !$category ? 'active' : '' ?>">All</a>
                <?php foreach ($categories as $cat): ?>
                <a href="<?= SITE_URL ?>/news.php?category=<?= urlencode($cat) ?>" class="filter-btn <?= $category === $cat ? 'active' : '' ?>"><?= sanitize($cat) ?></a>
                <?php endforeach; ?>
            </div>

            <?php if (empty($articles)): ?>
                <div class="text-center" style="padding: 60px 0;">
                    <i class="fas fa-newspaper" style="font-size: 3rem; color: var(--gray-light); margin-bottom: 15px;"></i>
                    <h3>No articles found</h3>
                    <p style="color: var(--text-light);">Check back soon for updates.</p>
                </div>
            <?php else: ?>
            <div class="news-grid">
                <?php foreach ($articles as $article): ?>
                <div class="news-card">
                    <div class="news-card-img" style="background-image: url('<?= SITE_URL ?>/<?= $article['featured_image'] ?>');">
                        <span class="news-card-badge"><?= sanitize($article['category']) ?></span>
                    </div>
                    <div class="news-card-body">
                        <div class="news-card-date"><i class="far fa-calendar"></i> <?= date('M d, Y', strtotime($article['published_date'])) ?></div>
                        <h3><a href="<?= SITE_URL ?>/news-single.php?slug=<?= $article['slug'] ?>"><?= sanitize($article['title']) ?></a></h3>
                        <p><?= truncateText($article['excerpt'], 120) ?></p>
                        <div class="news-card-footer">
                            <span><i class="far fa-eye"></i> <?= number_format($article['views']) ?></span>
                            <a href="<?= SITE_URL ?>/news-single.php?slug=<?= $article['slug'] ?>" style="color: var(--secondary); font-weight: 600;">Read More</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= $category ? '&category=' . urlencode($category) : '' ?>">&laquo; Prev</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?><?= $category ? '&category=' . urlencode($category) : '' ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?><?= $category ? '&category=' . urlencode($category) : '' ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
