<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    http_response_code(403);
    exit('Unauthorized');
}
include('dbconi.php');
if (!isset($_GET['order_id'])) {
    http_response_code(400);
    exit('Missing order_id');
}
$order_id = intval($_GET['order_id']);
$query = "SELECT status_updates FROM orders WHERE order_id = ?";
$stmt = mysqli_prepare($dbc, $query);
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
if (!$row || empty($row['status_updates'])) {
    echo '<div class="alert alert-info">No status history available.</div>';
    exit;
}
$updates = explode("\n", $row['status_updates']);
echo '<div class="mt-3"><h6>Status Update History:</h6><ul class="list-group">';
foreach ($updates as $update) {
    // Extract date and message using regex
    if (preg_match('/\[(.*?)\]\s*(.*)/', $update, $matches)) {
        $dateStr = trim($matches[1]);
        $message = trim($matches[2]);
        $dateObj = DateTime::createFromFormat('Y-m-d H:i:s', $dateStr);
        if (!$dateObj) {
            // Try alternative format if needed
            $dateObj = DateTime::createFromFormat('M j, Y g:i A', $dateStr);
        }
        if ($dateObj) {
            $formatted = $dateObj->format('F j, Y g:i A');
            echo '<li class="list-group-item small"><i class="fas fa-comment"></i> [' . $formatted . '] ' . htmlspecialchars($message) . '</li>';
        } else {
            // Fallback: show raw
            echo '<li class="list-group-item small"><i class="fas fa-comment"></i> [' . htmlspecialchars($dateStr) . '] ' . htmlspecialchars($message) . '</li>';
        }
    } else {
        echo '<li class="list-group-item small">' . htmlspecialchars($update) . '</li>';
    }
}
echo '</ul></div>';
?>
