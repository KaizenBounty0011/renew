<?php
require_once 'config.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) { header('Location: ' . SITE_URL . '/news.php'); exit; }

$stmt = $pdo->prepare("SELECT n.*, a.full_name as author_name FROM news n LEFT JOIN admins a ON n.author_id = a.id WHERE n.slug = ? AND n.status = 'published'");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) { header('Location: ' . SITE_URL . '/news.php'); exit; }

// Increment views
$pdo->prepare("UPDATE news SET views = views + 1 WHERE id = ?")->execute([$article['id']]);

$pageTitle = $article['title'];

// Related news
$related = $pdo->prepare("SELECT * FROM news WHERE status = 'published' AND id != ? AND category = ? ORDER BY published_date DESC LIMIT 3");
$related->execute([$article['id'], $article['category']]);
$relatedArticles = $related->fetchAll();

require_once 'includes/header.php';
?>

    <section class="page-banner">
        <div class="container">
            <h1><?= sanitize($article['title']) ?></h1>
            <div class="breadcrumb">
                <a href="<?= SITE_URL ?>">Home</a><span>/</span>
                <a href="<?= SITE_URL ?>/news.php">News</a><span>/</span>
                <span><?= truncateText($article['title'], 40) ?></span>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="detail-content">
                <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 25px; font-size: 0.9rem; color: var(--text-light);">
                    <span><i class="far fa-calendar"></i> <?= date('F d, Y', strtotime($article['published_date'])) ?></span>
                    <span><i class="far fa-user"></i> <?= sanitize($article['author_name'] ?? 'Admin') ?></span>
                    <span><i class="far fa-folder"></i> <?= sanitize($article['category']) ?></span>
                    <span><i class="far fa-eye"></i> <?= number_format($article['views']) ?> views</span>
                </div>

                <?php if ($article['featured_image']): ?>
                <div style="border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 30px; height: 400px; background: var(--light); background-image: url('<?= SITE_URL ?>/<?= $article['featured_image'] ?>'); background-size: cover; background-position: center;"></div>
                <?php endif; ?>

                <div class="article-content">
                    <?= $article['content'] ?>
                </div>

                <!-- Social Share -->
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--gray-light);">
                    <strong style="font-size: 0.9rem;">Share this article:</strong>
                    <div class="social-share">
                        <a href="https://facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . '/news-single.php?slug=' . $article['slug']) ?>" target="_blank" class="facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode(SITE_URL . '/news-single.php?slug=' . $article['slug']) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" class="twitter"><i class="fab fa-x-twitter"></i></a>
                        <a href="https://linkedin.com/sharing/share-offsite/?url=<?= urlencode(SITE_URL . '/news-single.php?slug=' . $article['slug']) ?>" target="_blank" class="linkedin"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://wa.me/?text=<?= urlencode($article['title'] . ' ' . SITE_URL . '/news-single.php?slug=' . $article['slug']) ?>" target="_blank" class="whatsapp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>

            <?php if (!empty($relatedArticles)): ?>
            <div style="margin-top: 60px;">
                <h2 style="font-family: var(--font-display); margin-bottom: 30px;">Related Articles</h2>
                <div class="news-grid">
                    <?php foreach ($relatedArticles as $rel): ?>
                    <div class="news-card">
                        <div class="news-card-img" style="background-image: url('<?= SITE_URL ?>/<?= $rel['featured_image'] ?>');">
                            <span class="news-card-badge"><?= sanitize($rel['category']) ?></span>
                        </div>
                        <div class="news-card-body">
                            <div class="news-card-date"><i class="far fa-calendar"></i> <?= date('M d, Y', strtotime($rel['published_date'])) ?></div>
                            <h3><a href="<?= SITE_URL ?>/news-single.php?slug=<?= $rel['slug'] ?>"><?= sanitize($rel['title']) ?></a></h3>
                            <p><?= truncateText($rel['excerpt'], 100) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
