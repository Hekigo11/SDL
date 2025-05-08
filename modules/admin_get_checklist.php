<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
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

    $order = mysqli_fetch_assoc($check_result);
    if ($order['status'] === 'cancelled') {
        echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Cannot modify checklist for cancelled orders</div>';
        exit;
    }

    // Get the checklist items
    $query = "SELECT 
        oc.order_id,
        oc.ingredient_id,
        i.name as ingredient_name, 
        i.unit,
        SUM(oc.quantity_needed) as quantity_needed,
        oc.is_ready,
        oc.checked_by,
        oc.checked_at,
        IFNULL(u.fname, '') as checker_name
    FROM order_checklist oc
    JOIN ingredients i ON oc.ingredient_id = i.ingredient_id
    LEFT JOIN users u ON oc.checked_by = u.user_id
    WHERE oc.order_id = ?
    GROUP BY oc.ingredient_id, i.name, i.unit, oc.is_ready, oc.checked_by, oc.checked_at, u.fname
    ORDER BY i.name ASC";

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

    echo '<div class="checklist-container">';
    while ($row = mysqli_fetch_assoc($result)) {
        $checked = $row['is_ready'] ? 'checked' : '';
        $checkedInfo = '';
        
        if ($row['is_ready'] && $row['checked_at']) {
            $checked_at = new DateTime($row['checked_at']);
            $checked_at->setTimezone(new DateTimeZone('Asia/Manila')); // Convert to Philippine timezone
            
            $checkedInfo = '<small class="text-success ml-2">
                <i class="fas fa-check"></i> ' . htmlspecialchars($row['checker_name']) . 
                ' at ' . $checked_at->format('M j, Y g:i A') . 
                '</small>';
        }
        
        echo '<div class="checklist-item mb-3">';
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
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($e->getMessage()) . '</div>';
} finally {
    if (isset($stmt)) mysqli_stmt_close($stmt);
    if (isset($check_stmt)) mysqli_stmt_close($check_stmt);
    if (isset($dbc)) mysqli_close($dbc);
}
?>