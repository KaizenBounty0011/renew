    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-top">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-col footer-about">
                        <a href="<?= SITE_URL ?>" class="footer-logo">
                            <span class="logo-renew">RENEW</span><span class="logo-empire">EMPIRE</span>
                        </a>
                        <p>A diversified conglomerate leading innovation across Fight Championship, Entertainment, Hotels, and Energy sectors across Africa.</p>
                        <div class="footer-social">
                            <a href="<?= getSetting('facebook') ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                            <a href="<?= getSetting('twitter') ?>" target="_blank"><i class="fab fa-x-twitter"></i></a>
                            <a href="<?= getSetting('instagram') ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                            <a href="<?= getSetting('linkedin') ?>" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                            <a href="<?= getSetting('youtube') ?>" target="_blank"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                    <div class="footer-col">
                        <h4>Quick Links</h4>
                        <ul>
                            <li><a href="<?= SITE_URL ?>/about.php">About Us</a></li>
                            <li><a href="<?= SITE_URL ?>/businesses.php">Our Businesses</a></li>
                            <li><a href="<?= SITE_URL ?>/news.php">News & Press</a></li>
                            <li><a href="<?= SITE_URL ?>/careers.php">Careers</a></li>
                            <li><a href="<?= SITE_URL ?>/contact.php">Contact Us</a></li>
                        </ul>
                    </div>
                    <div class="footer-col">
                        <h4>Our Divisions</h4>
                        <ul>
                            <li><a href="<?= SITE_URL ?>/fight-championship.php">Renew Fight Championship</a></li>
                            <li><a href="<?= SITE_URL ?>/entertainment.php">Renew Entertainment</a></li>
                            <li><a href="<?= SITE_URL ?>/hotels.php">Renew Hotels</a></li>
                            <li><a href="<?= SITE_URL ?>/energy.php">Renew Energy</a></li>
                        </ul>
                    </div>
                    <div class="footer-col">
                        <h4>Contact Info</h4>
                        <ul class="footer-contact">
                            <li><i class="fas fa-map-marker-alt"></i> <?= getSetting('site_address') ?></li>
                            <li><i class="fas fa-phone"></i> <?= getSetting('site_phone') ?></li>
                            <li><i class="fas fa-envelope"></i> <?= getSetting('site_email') ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p><?= getSetting('footer_text') ?></p>
            </div>
        </div>
    </footer>

    <script src="<?= SITE_URL ?>/assets/js/script.js"></script>
    <?php if (isset($extraJS)) echo $extraJS; ?>
</body>
</html>
