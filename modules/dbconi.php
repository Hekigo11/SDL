<?php
require_once __DIR__ . '/../config.php';

try {
    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$dbc) {
        throw new Exception("Database connection failed: " . mysqli_connect_error() . " (Error No: " . mysqli_connect_errno() . ")");
    }

    // Set the character set to utf8mb4
    if (!mysqli_set_charset($dbc, "utf8mb4")) {
        throw new Exception("Error loading character set utf8mb4: " . mysqli_error($dbc));
    }

} catch (Exception $e) {
    error_log("Database Connection Error in dbconi.php: " . $e->getMessage());
    // For a production environment, you might want a more user-friendly error page
    // or to avoid die() altogether and handle the error higher up in your application.
    die("A critical database error occurred. We are working to resolve the issue. Please try again later.");
}
?>