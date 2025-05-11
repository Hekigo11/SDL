<?php
require_once __DIR__ . '/../config.php';
session_start();

// Clear catering-related session variables
unset($_SESSION['catering_form']);
unset($_SESSION['catering_step1']);

$response = ['success' => true];

// If this is an AJAX request, return JSON response
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// If not AJAX, redirect back to referring page or home
$redirect_to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL . '/index.php';
header('Location: ' . $redirect_to);
exit;
?>
