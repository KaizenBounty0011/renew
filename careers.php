<?php
require_once 'config.php';
$pageTitle = 'Careers';

$dept = $_GET['department'] ?? '';
$careers = [];

if ($dept) {
    $stmt = $pdo->prepare("SELECT c.*, d.division_name FROM careers c LEFT JOIN divisions d ON c.division_id = d.id WHERE c.status = 'open' AND c.department = ? ORDER BY c.created_at DESC");
    $stmt->execute([$dept]);
} else {
    $stmt = $pdo->query("SELECT c.*, d.division_name FROM careers c LEFT JOIN divisions d ON c.division_id = d.id WHERE c.status = 'open' ORDER BY c.created_at DESC");
}
$careers = $stmt->fetchAll();

$departments = $pdo->query("SELECT DISTINCT department FROM careers WHERE status = 'open' ORDER BY department")->fetchAll(PDO::FETCH_COLUMN);

require_once 'includes/header.php';
?>

    <section class="page-banner">
        <div class="container">
            <h1>Join Our Team</h1>
            <p>Build your career with Africa's most innovative conglomerate.</p>
            <div class="breadcrumb">
                <a href="<?= SITE_URL ?>">Home</a><span>/</span><span>Careers</span>
            </div>
        </div>
    </section>

    <!-- Why Work With Us -->
    <section class="section section-light">
        <div class="container">
            <div class="section-header">
                <span class="overline">Why Renew Empire</span>
                <h2>Build Something Extraordinary</h2>
            </div>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-rocket"></i></div>
                    <h4>Growth</h4>
                    <p>Accelerate your career with diverse opportunities across four industries.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-globe-africa"></i></div>
                    <h4>Impact</h4>
                    <p>Make a real difference in communities across Africa and beyond.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-users"></i></div>
                    <h4>Culture</h4>
                    <p>Join a diverse, inclusive team that values collaboration and innovation.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-award"></i></div>
                    <h4>Benefits</h4>
                    <p>Competitive compensation, health coverage, and professional development.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Open Positions -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <span class="overline">Open Positions</span>
                <h2>Current Opportunities</h2>
            </div>

            <div class="filter-bar">
                <a href="<?= SITE_URL ?>/careers.php" class="filter-btn <?= !$dept ? 'active' : '' ?>">All Departments</a>
                <?php foreach ($departments as $d): ?>
                <a href="<?= SITE_URL ?>/careers.php?department=<?= urlencode($d) ?>" class="filter-btn <?= $dept === $d ? 'active' : '' ?>"><?= sanitize($d) ?></a>
                <?php endforeach; ?>
            </div>

            <?php if (empty($careers)): ?>
                <div class="text-center" style="padding: 60px 0;">
                    <i class="fas fa-briefcase" style="font-size: 3rem; color: var(--gray-light); margin-bottom: 15px;"></i>
                    <h3>No open positions at this time</h3>
                    <p style="color: var(--text-light);">Check back soon or send us your resume.</p>
                </div>
            <?php else: ?>
                <?php foreach ($careers as $job): ?>
                <div class="career-card">
                    <h3><a href="<?= SITE_URL ?>/career-details.php?slug=<?= $job['slug'] ?>"><?= sanitize($job['job_title']) ?></a></h3>
                    <div class="career-meta">
                        <span><i class="fas fa-building"></i> <?= sanitize($job['department']) ?></span>
                        <span><i class="fas fa-map-marker-alt"></i> <?= sanitize($job['location']) ?></span>
                        <span><i class="fas fa-clock"></i> <?= ucfirst(str_replace('_', ' ', $job['employment_type'])) ?></span>
                        <?php if ($job['division_name']): ?>
                        <span><i class="fas fa-layer-group"></i> <?= sanitize($job['division_name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <p><?= truncateText($job['description'], 200) ?></p>
                    <div class="career-card-footer">
                        <span class="salary-range"><?= sanitize($job['salary_range']) ?></span>
                        <a href="<?= SITE_URL ?>/career-details.php?slug=<?= $job['slug'] ?>" class="btn btn-primary btn-sm">View & Apply <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
