<?php
require_once __DIR__ . '/../config.php';

try {
    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$dbc) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    die("A database error occurred. Please try again later.");
}
?>