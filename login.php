<?php
// This page does not exist — redirect to homepage
require_once 'config.php';
header('Location: ' . SITE_URL . '/index.php');
exit;
