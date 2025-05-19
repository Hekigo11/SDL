<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/dbconi.php'; // Ensure $dbc is available

error_reporting(E_ALL);
ini_set('display_errors', 1); // Display errors if run via browser, log if via CLI

// For command-line execution, you might want to set a specific timezone if not already set globally
// date_default_timezone_set('Asia/Manila');

echo "Starting cleanup of old temporary orders...\n";

$refs_to_delete = [];
$deleted_items_count = 0;
$deleted_orders_count = 0;

try {
    mysqli_begin_transaction($dbc);

    // Select payment_reference of temp_orders to be deleted
    // Criteria:
    // 1. Status is 'expired' or 'failed', and the record hasn't been updated in the last 24 hours.
    // 2. Status is one of the initial/pending states ('awaiting_payment', 'pending_confirmation', 'unpaid'),
    //    but the order is very old (created > 24h ago) AND its expiry time (if set) has long passed (e.g., >1 hour ago).
    $select_sql = "SELECT payment_reference FROM temp_orders
                   WHERE 
                     (status IN ('expired', 'failed') AND updated_at < DATE_SUB(NOW(), INTERVAL 24 HOUR))
                     OR 
                     (status IN ('awaiting_payment', 'pending_confirmation', 'unpaid') 
                      AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
                      AND (expires_at IS NULL OR expires_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)) 
                     )";
    
    $result = mysqli_query($dbc, $select_sql);
    if (!$result) {
        throw new Exception("Error selecting temp orders for deletion: " . mysqli_error($dbc));
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $refs_to_delete[] = $row['payment_reference'];
    }
    mysqli_free_result($result);

    if (empty($refs_to_delete)) {
        echo "No old temporary orders found to delete.\n";
        mysqli_commit($dbc); // Commit even if nothing to do, to end transaction
        exit;
    }

    echo "Found " . count($refs_to_delete) . " temporary order references to process for deletion.\n";

    // Prepare statements for deletion
    $delete_items_sql = "DELETE FROM temp_order_items WHERE temp_order_payment_reference = ?";
    $stmt_delete_items = mysqli_prepare($dbc, $delete_items_sql);
    if (!$stmt_delete_items) {
        throw new Exception("Prepare statement failed (delete temp_order_items): " . mysqli_error($dbc));
    }

    $delete_order_sql = "DELETE FROM temp_orders WHERE payment_reference = ?";
    $stmt_delete_order = mysqli_prepare($dbc, $delete_order_sql);
    if (!$stmt_delete_order) {
        throw new Exception("Prepare statement failed (delete temp_orders): " . mysqli_error($dbc));
    }

    foreach ($refs_to_delete as $ref) {
        // Delete from temp_order_items
        mysqli_stmt_bind_param($stmt_delete_items, "s", $ref);
        if (!mysqli_stmt_execute($stmt_delete_items)) {
            error_log("Failed to delete items for temp_order_payment_reference: $ref. Error: " . mysqli_stmt_error($stmt_delete_items));
            // Continue to try deleting others, or throw exception to rollback all
        }
        $deleted_items_count += mysqli_stmt_affected_rows($stmt_delete_items);

        // Delete from temp_orders
        mysqli_stmt_bind_param($stmt_delete_order, "s", $ref);
        if (!mysqli_stmt_execute($stmt_delete_order)) {
            error_log("Failed to delete temp_order with payment_reference: $ref. Error: " . mysqli_stmt_error($stmt_delete_order));
            // Continue to try deleting others, or throw exception to rollback all
        }
        $deleted_orders_count += mysqli_stmt_affected_rows($stmt_delete_order);
        echo "Processed reference for deletion: $ref\n";
    }

    mysqli_stmt_close($stmt_delete_items);
    mysqli_stmt_close($stmt_delete_order);

    mysqli_commit($dbc);
    echo "Cleanup successful.\n";
    echo "Total temporary order items deleted: $deleted_items_count\n";
    echo "Total temporary orders deleted: $deleted_orders_count\n";

} catch (Exception $e) {
    if (isset($dbc) && mysqli_ping($dbc)) {
       mysqli_rollback($dbc);
    }
    $error_message = "Error during temporary order cleanup: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine();
    error_log($error_message); // Log to PHP error log
    echo $error_message . "\n"; // Output to console/browser if run manually
} finally {
    if (isset($dbc)) {
        mysqli_close($dbc);
    }
    echo "Cleanup script finished.\n";
}
?> 