<?php
require_once 'config.php';
$pageTitle = 'Home';

// Fetch divisions
$divisions = $pdo->query("SELECT * FROM divisions WHERE status = 'active' ORDER BY id")->fetchAll();

// Fetch latest news
$latestNews = $pdo->query("SELECT * FROM news WHERE status = 'published' ORDER BY published_date DESC LIMIT 3")->fetchAll();

// Fetch upcoming fight events count
$fightCount = $pdo->query("SELECT COUNT(*) FROM fight_events WHERE status = 'upcoming'")->fetchColumn();
$showCount = $pdo->query("SELECT COUNT(*) FROM entertainment_shows WHERE status = 'upcoming'")->fetchColumn();
$hotelCount = $pdo->query("SELECT COUNT(*) FROM hotels WHERE status = 'active'")->fetchColumn();
$careerCount = $pdo->query("SELECT COUNT(*) FROM careers WHERE status = 'open'")->fetchColumn();

require_once 'includes/header.php';
?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <span class="hero-badge">Africa's Leading Conglomerate</span>
                <h1>Building <span>Tomorrow</span>, Today</h1>
                <p>Renew Empire is a diversified corporate group driving innovation and excellence across Fight Championship, Entertainment, Hotels, and Energy sectors.</p>
                <div class="hero-buttons">
                    <a href="<?= SITE_URL ?>/businesses.php" class="btn btn-primary">Explore Our Businesses <i class="fas fa-arrow-right"></i></a>
                    <a href="<?= SITE_URL ?>/about.php" class="btn btn-outline">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Bar -->
    <section class="stats-bar">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <h3>4</h3>
                    <p>Business Divisions</p>
                </div>
                <div class="stat-item">
                    <h3>8+</h3>
                    <p>Years of Excellence</p>
                </div>
                <div class="stat-item">
                    <h3>5K+</h3>
                    <p>Team Members</p>
                </div>
                <div class="stat-item">
                    <h3>12</h3>
                    <p>Countries</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Divisions Section -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <span class="overline">Our Businesses</span>
                <h2>Four Divisions, One Empire</h2>
                <p>Explore our diverse portfolio of world-class businesses across entertainment, hospitality, and sustainable energy.</p>
            </div>
            <div class="divisions-grid">
                <?php
                $icons = ['fas fa-fist-raised', 'fas fa-music', 'fas fa-hotel', 'fas fa-bolt'];
                $links = ['fight-championship', 'entertainment', 'hotels', 'energy'];
                foreach ($divisions as $i => $div):
                ?>
                <a href="<?= SITE_URL ?>/<?= $links[$i] ?>.php" class="division-card">
                    <div class="division-card-img" style="background-image: url('<?= SITE_URL ?>/<?= $div['hero_image1'] ?>'); background-color: <?= $div['accent_color'] ?>20;">
                        <div class="division-card-icon" style="background: <?= $div['accent_color'] ?>;">
                            <i class="<?= $icons[$i] ?>"></i>
                        </div>
                    </div>
                    <div class="division-card-body">
                        <h3><?= sanitize($div['division_name']) ?></h3>
                        <p><?= truncateText($div['description'], 100) ?></p>
                        <span class="division-card-link" style="color: <?= $div['accent_color'] ?>;">
                            Explore <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- About Summary -->
    <section class="section section-dark">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <span class="overline" style="color: var(--accent-orange);">About Renew Empire</span>
                    <h2 style="color: var(--white);">Redefining African Business Excellence</h2>
                    <p style="color: rgba(255,255,255,0.7);">Founded in 2018, Renew Empire has grown from a single venture into a powerhouse conglomerate with four distinct divisions. We are committed to driving economic growth, creating employment, and delivering exceptional experiences across Africa.</p>
                    <p style="color: rgba(255,255,255,0.7);">Our vision is to be Africa's most respected and innovative corporate group, setting global standards in every industry we enter.</p>
                    <a href="<?= SITE_URL ?>/about.php" class="btn btn-primary" style="margin-top: 15px;">Read Our Story <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="about-image">
                    <i class="fas fa-crown"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest News -->
    <section class="section section-light">
        <div class="container">
            <div class="section-header">
                <span class="overline">Latest Updates</span>
                <h2>News & Press Releases</h2>
                <p>Stay informed with the latest developments from across the Renew Empire group.</p>
            </div>
            <div class="news-grid">
                <?php foreach ($latestNews as $article): ?>
                <div class="news-card">
                    <div class="news-card-img" style="background-image: url('<?= SITE_URL ?>/<?= $article['featured_image'] ?>');">
                        <span class="news-card-badge"><?= sanitize($article['category']) ?></span>
                    </div>
                    <div class="news-card-body">
                        <div class="news-card-date"><i class="far fa-calendar"></i> <?= date('M d, Y', strtotime($article['published_date'])) ?></div>
                        <h3><a href="<?= SITE_URL ?>/news-single.php?slug=<?= $article['slug'] ?>"><?= sanitize($article['title']) ?></a></h3>
                        <p><?= truncateText($article['excerpt'], 120) ?></p>
                        <div class="news-card-footer">
                            <span><i class="far fa-eye"></i> <?= number_format($article['views']) ?> views</span>
                            <a href="<?= SITE_URL ?>/news-single.php?slug=<?= $article['slug'] ?>" style="color: var(--secondary); font-weight: 600;">Read More <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-30">
                <a href="<?= SITE_URL ?>/news.php" class="btn btn-dark">View All News <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-content">
            <h2>Partner With Us</h2>
            <p>Join Africa's fastest-growing conglomerate. Whether you're an investor, partner, or talent looking for opportunities, we'd love to hear from you.</p>
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="<?= SITE_URL ?>/contact.php" class="btn btn-primary btn-lg">Get In Touch <i class="fas fa-arrow-right"></i></a>
                <a href="<?= SITE_URL ?>/careers.php" class="btn btn-outline btn-lg">View Careers</a>
            </div>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
