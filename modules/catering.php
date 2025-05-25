<?php
require_once __DIR__ . '/../config.php';
require_once 'dbconi.php';
session_start();

if (isset($_SESSION['loginok']) && ($_SESSION['role'] == 1 || $_SESSION['role'] == 3)) {
    header('Location: ' . BASE_URL . '/modules/admindashboard.php');
	if (!headers_sent()) {
        header('Location: ' . BASE_URL . '/modules/admindashboard.php');
        exit;
    } else {
        echo '<script>window.location.href="' . BASE_URL . '/modules/admindashboard.php";</script>';
        exit;
    }
}

if (!isset($_SESSION['loginok'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Redirect to the new step1.php
header('Location: ' . BASE_URL . '/modules/catering/step1.php');
exit;
?>
