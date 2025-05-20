<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {        
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Unauthorized access</div>';
    exit;
}

include("dbconi.php");

if (!isset($_GET['order_id'])) {
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Order ID is required</div>';
    exit;
}

try {
    $order_id = intval($_GET['order_id']);
    
    // First, check if the order exists
    $check_order = "SELECT status FROM orders WHERE order_id = ?";
    $check_stmt = mysqli_prepare($dbc, $check_order);
    mysqli_stmt_bind_param($check_stmt, "i", $order_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) === 0) {
        throw new Exception('Order not found');
    }

    // Get the checklist items with ingredient type information
    $query = "SELECT 
        oc.order_id,
        oc.ingredient_id,
        i.name as ingredient_name,
        i.unit,
        it.type_name as station_name,
        oc.quantity_needed,
        oc.is_ready,
        oc.checked_by,
        oc.checked_at,
        IFNULL(u.fname, '') as checker_name
    FROM order_checklist oc
    JOIN ingredients i ON oc.ingredient_id = i.ingredient_id
    JOIN ingredient_types it ON i.type_id = it.type_id
    LEFT JOIN users u ON oc.checked_by = u.user_id
    WHERE oc.order_id = ?
    ORDER BY it.type_name, i.name ASC";

    $stmt = mysqli_prepare($dbc, $query);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare query: " . mysqli_error($dbc));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to execute query: " . mysqli_error($dbc));
    }
    
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 0) {
        echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No ingredients to prepare for this order.</div>';
        exit;
    }

    $current_station = '';
    echo '<div class="checklist-container">';
    
    while ($row = mysqli_fetch_assoc($result)) {
        if ($current_station != $row['station_name']) {
            if ($current_station != '') {
                echo '</div>'; // Close previous station group
            }
            $current_station = $row['station_name'];
            echo '<div class="station-group mb-3">
                    <h6 class="mb-2 text-muted"><i class="fas fa-utensils"></i> ' . htmlspecialchars($row['station_name']) . '</h6>';
        }
        
        $status_class = $row['is_ready'] ? 'success' : 'secondary';
        $status_icon = $row['is_ready'] ? 'check-circle' : 'clock';
        
        echo '<div class="checklist-item mb-2">
                <div class="d-flex align-items-center">
                    <i class="fas fa-' . $status_icon . ' text-' . $status_class . ' mr-2"></i>
                    <div>
                        <div class="font-weight-medium">
                            ' . htmlspecialchars($row['ingredient_name']) . ' 
                            <span class="text-muted">(' . number_format($row['quantity_needed'], 2) . ' ' . htmlspecialchars($row['unit']) . ')</span>
                        </div>';
        
        if ($row['is_ready'] && $row['checked_at']) {
            $checked_at = new DateTime($row['checked_at']);
            $checked_at->setTimezone(new DateTimeZone('Asia/Manila'));
            echo '<small class="text-success">
                    Prepared by ' . htmlspecialchars($row['checker_name']) . 
                    ' at ' . $checked_at->format('M j, Y g:i A') . 
                  '</small>';
        }
        
        echo '</div></div></div>';
    }
    
    if ($current_station != '') {
        echo '</div>'; // Close last station group
    }
    echo '</div>';

} catch (Exception $e) {
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($e->getMessage()) . '</div>';
} finally {
    if (isset($stmt)) mysqli_stmt_close($stmt);
    if (isset($check_stmt)) mysqli_stmt_close($check_stmt);
    if (isset($dbc)) mysqli_close($dbc);
}
?>