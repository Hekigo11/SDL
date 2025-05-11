<?php
require_once __DIR__ . '/../config.php';
require_once 'dbconi.php';
session_start();

if (!isset($_SESSION['loginok'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Redirect to the new step1.php
header('Location: ' . BASE_URL . '/modules/catering/step1.php');
exit;
?>
