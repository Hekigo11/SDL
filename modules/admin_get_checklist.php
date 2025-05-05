<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    echo '<p class="text-danger">Unauthorized access</p>';
    exit;
}

include("dbconi.php");

if (!isset($_GET['order_id'])) {
    echo '<p class="text-danger">Order ID is required</p>';
    exit;
}

try {
    $order_id = intval($_GET['order_id']);

    $query = "SELECT oc.*, i.name as ingredient_name, i.unit, IFNULL(u.fname, '') as checker_name
              FROM order_checklist oc
              JOIN ingredients i ON oc.ingredient_id = i.ingredient_id
              LEFT JOIN users u ON oc.checked_by = u.user_id
              WHERE oc.order_id = ?";

    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 0) {
        echo '<p class="text-muted">No ingredients to prepare for this order.</p>';
        exit;
    }

    echo '<div class="checklist-container">';
    while ($row = mysqli_fetch_assoc($result)) {
        $checked = $row['is_ready'] ? 'checked' : '';
        $checkedInfo = $row['is_ready'] ? '<small class="text-muted ml-2">✓ ' . htmlspecialchars($row['checker_name']) . '</small>' : '';
        
        echo '<div class="checklist-item mb-2">';
        echo '<div class="custom-control custom-checkbox">';
        echo '<input type="checkbox" class="custom-control-input checklist-checkbox" 
                     id="check_' . $row['order_id'] . '_' . $row['ingredient_id'] . '" 
                     data-id="' . $row['order_id'] . '-' . $row['ingredient_id'] . '" ' . $checked . '>';
        echo '<label class="custom-control-label" for="check_' . $row['order_id'] . '_' . $row['ingredient_id'] . '">';
        echo htmlspecialchars($row['ingredient_name']) . ' (' . number_format($row['quantity_needed'], 2) . ' ' . htmlspecialchars($row['unit']) . ')';
        echo '</label>';
        echo $checkedInfo;
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';

} catch (Exception $e) {
    echo '<p class="text-danger">Error loading checklist: ' . htmlspecialchars($e->getMessage()) . '</p>';
} finally {
    mysqli_close($dbc);
}
?>