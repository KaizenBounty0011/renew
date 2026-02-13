<?php
require_once 'config.php';
$pageTitle = 'Energy Product Catalogue';

// Fetch all active products
$products = $pdo->query("SELECT * FROM energy_products WHERE status = 'active' ORDER BY category, id")->fetchAll();

// Get unique categories
$categories = [];
foreach ($products as $p) {
    if (!empty($p['category']) && !in_array($p['category'], $categories)) {
        $categories[] = $p['category'];
    }
}

require_once 'includes/header.php';
?>

    <!-- Page Banner -->
    <section class="page-banner">
        <div class="container">
            <h1>Energy Product Catalogue</h1>
            <p>Browse our range of premium energy products and solutions.</p>
            <div class="breadcrumb">
                <a href="<?= SITE_URL ?>">Home</a>
                <span>/</span>
                <a href="<?= SITE_URL ?>/energy.php">Energy</a>
                <span>/</span>
                <span>Product Catalogue</span>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <!-- Search Bar -->
            <div class="search-bar">
                <input type="text" id="product_search" onkeyup="searchProducts()" placeholder="Search products...">
                <button type="button"><i class="fas fa-search"></i></button>
            </div>

            <!-- Filter Bar -->
            <?php if (!empty($categories)): ?>
            <div class="filter-bar">
                <button class="filter-btn active" data-filter="all" onclick="filterProducts('all', this)">All Products</button>
                <?php foreach ($categories as $cat): ?>
                <button class="filter-btn" data-filter="<?= sanitize($cat) ?>" onclick="filterProducts('<?= sanitize($cat) ?>', this)"><?= sanitize($cat) ?></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Products Grid -->
            <?php if (empty($products)): ?>
                <p class="text-center">No products available at the moment. Please check back later.</p>
            <?php else: ?>
            <div class="products-grid" id="productsGrid">
                <?php foreach ($products as $product): ?>
                <div class="product-card" data-name="<?= strtolower(sanitize($product['product_name'])) ?>" data-category="<?= sanitize($product['category']) ?>">
                    <div class="product-card-img" style="<?= !empty($product['product_image']) ? "background-image: url('" . SITE_URL . "/" . $product['product_image'] . "');" : '' ?>">
                        <?php if (empty($product['product_image'])): ?>
                        <i class="fas fa-box-open"></i>
                        <?php endif; ?>
                    </div>
                    <div class="product-card-body">
                        <div class="category"><?= sanitize($product['category']) ?></div>
                        <h3><?= sanitize($product['product_name']) ?></h3>
                        <p><?= truncateText($product['description'], 120) ?></p>

                        <?php if (!empty($product['specifications'])): ?>
                        <div style="margin-bottom: 15px;">
                            <strong style="font-size: 0.85rem; color: var(--text);">Specifications:</strong>
                            <ul style="list-style: none; padding: 0; margin: 8px 0 0;">
                                <?php
                                $specs = array_filter(array_map('trim', explode("\n", $product['specifications'])));
                                $displaySpecs = array_slice($specs, 0, 4);
                                foreach ($displaySpecs as $spec):
                                ?>
                                <li style="font-size: 0.8rem; color: var(--text-light); padding: 2px 0;">
                                    <i class="fas fa-check" style="color: var(--energy); margin-right: 5px; font-size: 0.7rem;"></i> <?= sanitize($spec) ?>
                                </li>
                                <?php endforeach; ?>
                                <?php if (count($specs) > 4): ?>
                                <li style="font-size: 0.8rem; color: var(--text-light); padding: 2px 0;">
                                    <em>+<?= count($specs) - 4 ?> more specs</em>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px solid var(--gray-light);">
                            <div class="product-price"><?= formatPrice($product['price']) ?></div>
                            <a href="<?= SITE_URL ?>/service-inquiry.php" class="btn btn-sm btn-energy">Inquire <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="cta-content">
            <h2>Need Help Choosing?</h2>
            <p>Our energy consultants can help you select the right products for your specific requirements.</p>
            <a href="<?= SITE_URL ?>/service-inquiry.php" class="btn btn-primary btn-lg">Contact Our Experts <i class="fas fa-arrow-right"></i></a>
        </div>
    </section>

<?php
$extraJS = '
<script>
function searchProducts() {
    var query = document.getElementById("product_search").value.toLowerCase();
    var cards = document.querySelectorAll(".product-card");

    cards.forEach(function(card) {
        var name = card.getAttribute("data-name") || "";
        var category = (card.getAttribute("data-category") || "").toLowerCase();
        if (name.indexOf(query) > -1 || category.indexOf(query) > -1) {
            card.style.display = "";
        } else {
            card.style.display = "none";
        }
    });
}

function filterProducts(category, btn) {
    var cards = document.querySelectorAll(".product-card");
    var buttons = document.querySelectorAll(".filter-btn");

    // Update active button
    buttons.forEach(function(b) { b.classList.remove("active"); });
    btn.classList.add("active");

    // Clear search
    document.getElementById("product_search").value = "";

    // Filter cards
    cards.forEach(function(card) {
        if (category === "all" || card.getAttribute("data-category") === category) {
            card.style.display = "";
        } else {
            card.style.display = "none";
        }
    });
}
</script>';
require_once 'includes/footer.php';
?>
