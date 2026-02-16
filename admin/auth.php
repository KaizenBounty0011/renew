<?php
require_once __DIR__ . '/../config.php';

if (!isAdminLoggedIn()) {
    header('Location: ' . SITE_URL . '/admin/login.php');
    exit;
}

$currentAdmin = getCurrentAdmin();
if (!$currentAdmin) {
    session_destroy();
    header('Location: ' . SITE_URL . '/admin/login.php');
    exit;
}
