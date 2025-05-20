<?php
// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once __DIR__ . '/../config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {    
    http_response_code(403);
    echo 'Unauthorized access';
    exit;
}

// Include database connection
include("dbconi.php");

// Check if required fields are present
if (!isset($_POST['order_id'])) {
    http_response_code(400);
    echo 'Missing required fields';
    exit;
}

try {
    // Start transaction
    mysqli_begin_transaction($dbc);

    // Process form data with validation
    $order_id = intval($_POST['order_id']);
    $menu_package = isset($_POST['menu_package']) ? mysqli_real_escape_string($dbc, $_POST['menu_package']) : '';
    $num_persons = isset($_POST['num_persons']) ? intval($_POST['num_persons']) : 0;
    $event_date = isset($_POST['event_date']) ? mysqli_real_escape_string($dbc, $_POST['event_date']) : '';
    $venue = isset($_POST['venue']) ? mysqli_real_escape_string($dbc, $_POST['venue']) : '';
    $status = isset($_POST['status']) ? mysqli_real_escape_string($dbc, $_POST['status']) : 'pending';
    $staff_notes = isset($_POST['staff_notes']) ? mysqli_real_escape_string($dbc, $_POST['staff_notes']) : '';
    $special_requests = isset($_POST['special_requests']) ? mysqli_real_escape_string($dbc, $_POST['special_requests']) : '';
    
    // Calculate total amount based on package price from database
    $total_amount = 0;
    
    // Parse services
    $services = isset($_POST['services']) ? explode(',', $_POST['services']) : [];
    $needs_setup = in_array('setup', $services) ? 1 : 0;
    $needs_tablesandchairs = in_array('tables', $services) ? 1 : 0;
    $needs_decoration = in_array('decoration', $services) ? 1 : 0;
    
    // Additional services costs
    $setupCost = $needs_setup ? 2000 : 0;
    $tablesCost = $needs_tablesandchairs ? 3500 : 0;
    $decorationCost = $needs_decoration ? 5000 : 0;
    $servicesCost = $setupCost + $tablesCost + $decorationCost;
    
    // Get package base price
    $package_query = "SELECT base_price FROM packages WHERE name = ? AND is_active = 1";
    $stmt = mysqli_prepare($dbc, $package_query);
    mysqli_stmt_bind_param($stmt, "s", $menu_package);
    mysqli_stmt_execute($stmt);
    $package_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($package_result) === 0) {
        throw new Exception("Package not found or not active");
    }
    
    $package_data = mysqli_fetch_assoc($package_result);
    $base_price = floatval($package_data['base_price']);
    
    // Calculate package cost based on per-person rate
    $package_cost = $base_price * $num_persons;
    
    // Calculate total cost
    $total_amount = $package_cost + $servicesCost;
    
    // Debugging
    error_log("Order ID: $order_id, Package: $menu_package, Persons: $num_persons");
    error_log("Base price: $base_price, Package cost: $package_cost, Services cost: $servicesCost");
    error_log("Total amount: $total_amount");

    // Get current order status
    $current_status_query = "SELECT status FROM catering_orders WHERE catering_id = ?";
    $stmt = mysqli_prepare($dbc, $current_status_query);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        throw new Exception("Order not found");
    }

    $order = mysqli_fetch_assoc($result);

    // Update the order
    $update_query = "UPDATE catering_orders 
                    SET menu_package = ?, 
                        num_persons = ?,
                        event_date = ?,
                        venue = ?,
                        needs_setup = ?,
                        needs_tablesandchairs = ?,
                        needs_decoration = ?,
                        special_requests = ?,
                        total_amount = ?,
                        status = ?, 
                        staff_notes = CASE 
                            WHEN ? != '' THEN CONCAT(COALESCE(staff_notes, ''), 
                                IF(LENGTH(COALESCE(staff_notes, '')) > 0, '\n', ''),
                                '[', NOW(), '] ', ?)
                            ELSE staff_notes
                        END
                    WHERE catering_id = ?";
                    
    $stmt = mysqli_prepare($dbc, $update_query);
    mysqli_stmt_bind_param($stmt, "sissiiisdsssi", 
        $menu_package,
        $num_persons,
        $event_date,
        $venue,
        $needs_setup,
        $needs_tablesandchairs,
        $needs_decoration,
        $special_requests,
        $total_amount,
        $status,
        $staff_notes,
        $staff_notes,
        $order_id
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to update order: " . mysqli_error($dbc));
    }

    mysqli_commit($dbc);
    echo "success";

} catch (Exception $e) {
    mysqli_rollback($dbc);
    error_log("Error updating standard catering order: " . $e->getMessage());
    http_response_code(500);
    echo $e->getMessage();
}
?>
