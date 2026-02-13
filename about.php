<?php
require_once 'config.php';
$pageTitle = 'About Us';

$story = $pdo->prepare("SELECT content FROM page_content WHERE page_slug = 'about' AND section_name = 'story'");
$story->execute();
$storyContent = $story->fetchColumn();

$mission = $pdo->prepare("SELECT content FROM page_content WHERE page_slug = 'about' AND section_name = 'mission'");
$mission->execute();
$missionContent = $mission->fetchColumn();

$vision = $pdo->prepare("SELECT content FROM page_content WHERE page_slug = 'about' AND section_name = 'vision'");
$vision->execute();
$visionContent = $vision->fetchColumn();

$divisions = $pdo->query("SELECT * FROM divisions WHERE status = 'active' ORDER BY id")->fetchAll();

require_once 'includes/header.php';
?>

    <section class="page-banner">
        <div class="container">
            <h1>About Renew Empire</h1>
            <p>Our story, vision, and the values that drive us forward.</p>
            <div class="breadcrumb">
                <a href="<?= SITE_URL ?>">Home</a>
                <span>/</span>
                <span>About Us</span>
            </div>
        </div>
    </section>

    <!-- Our Story -->
    <section class="section">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <span class="overline">Our Story</span>
                    <h2>From Vision to Empire</h2>
                    <?= $storyContent ?>
                </div>
                <div class="about-image">
                    <i class="fas fa-landmark"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="section section-dark">
        <div class="container">
            <div class="about-content" style="gap: 40px;">
                <div>
                    <div style="background: rgba(255,255,255,0.05); padding: 40px; border-radius: var(--radius-lg); border-left: 4px solid var(--accent-orange);">
                        <span class="overline" style="color: var(--accent-orange);">Our Mission</span>
                        <p style="color: rgba(255,255,255,0.85); font-size: 1.15rem; line-height: 1.7; margin-top: 10px;"><?= sanitize($missionContent) ?></p>
                    </div>
                </div>
                <div>
                    <div style="background: rgba(255,255,255,0.05); padding: 40px; border-radius: var(--radius-lg); border-left: 4px solid var(--secondary);">
                        <span class="overline" style="color: var(--secondary);">Our Vision</span>
                        <p style="color: rgba(255,255,255,0.85); font-size: 1.15rem; line-height: 1.7; margin-top: 10px;"><?= sanitize($visionContent) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Core Values -->
    <section class="section section-light">
        <div class="container">
            <div class="section-header">
                <span class="overline">What We Stand For</span>
                <h2>Our Core Values</h2>
            </div>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-trophy"></i></div>
                    <h4>Excellence</h4>
                    <p>We pursue the highest standards in everything we do.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-lightbulb"></i></div>
                    <h4>Innovation</h4>
                    <p>We embrace new ideas and technologies to stay ahead.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-handshake"></i></div>
                    <h4>Integrity</h4>
                    <p>We conduct business with transparency and ethical principles.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-leaf"></i></div>
                    <h4>Sustainability</h4>
                    <p>We build for the long term, respecting people and planet.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Group Structure -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <span class="overline">Group Structure</span>
                <h2>Our Business Divisions</h2>
                <p>Four specialized divisions united under one corporate vision.</p>
            </div>
            <div class="divisions-grid" style="grid-template-columns: repeat(2, 1fr);">
                <?php
                $icons = ['fas fa-fist-raised', 'fas fa-music', 'fas fa-hotel', 'fas fa-bolt'];
                $links = ['fight-championship', 'entertainment', 'hotels', 'energy'];
                foreach ($divisions as $i => $div):
                ?>
                <div class="division-card" style="border-left: 4px solid <?= $div['accent_color'] ?>;">
                    <div class="division-card-body">
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                            <div class="division-card-icon" style="background: <?= $div['accent_color'] ?>; position: static; width: 45px; height: 45px;">
                                <i class="<?= $icons[$i] ?>"></i>
                            </div>
                            <h3 style="margin: 0;"><?= sanitize($div['division_name']) ?></h3>
                        </div>
                        <p style="margin-bottom: 10px;"><strong><?= sanitize($div['tagline']) ?></strong></p>
                        <p><?= truncateText($div['description'], 200) ?></p>
                        <a href="<?= SITE_URL ?>/<?= $links[$i] ?>.php" class="division-card-link" style="color: <?= $div['accent_color'] ?>;">
                            Learn More <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="cta-content">
            <h2>Join the Empire</h2>
            <p>We're always looking for talented individuals who share our passion for excellence and innovation.</p>
            <a href="<?= SITE_URL ?>/careers.php" class="btn btn-primary btn-lg">View Open Positions <i class="fas fa-arrow-right"></i></a>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
