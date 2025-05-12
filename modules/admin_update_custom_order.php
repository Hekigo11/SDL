<?php
session_start();
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    echo "Unauthorized";
    exit;
}

include("dbconi.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['custom_order_id'] ?? null;
    $menu_preferences = $_POST['menu_preferences'] ?? '';
    $num_persons = $_POST['num_persons'] ?? 0;
    $quote_amount = $_POST['quote_amount'] ?? null;
    
    // Decode selected menu items
    $selectedItems = isset($_POST['selectedItems']) ? json_decode($_POST['selectedItems'], true) : [];
    $services = isset($_POST['services']) ? explode(',', $_POST['services']) : [];
    
    $needs_setup = in_array('setup', $services) ? 1 : 0;
    $needs_tablesandchairs = in_array('tables', $services) ? 1 : 0;
    $needs_decoration = in_array('decoration', $services) ? 1 : 0;

    // Calculate total amount including additional services
    $total_amount = $quote_amount;
    if ($needs_setup) $total_amount += 2000;
    if ($needs_tablesandchairs) $total_amount += 3500;
    if ($needs_decoration) $total_amount += 5000;

    mysqli_begin_transaction($dbc);

    try {
        $query = "UPDATE custom_catering_orders 
                  SET menu_preferences = ?, 
                      num_persons = ?,
                      quote_amount = ?,
                      needs_setup = ?,
                      needs_tablesandchairs = ?,
                      needs_decoration = ?
                  WHERE custom_order_id = ?";

        $stmt = mysqli_prepare($dbc, $query);
        mysqli_stmt_bind_param($stmt, 'sidiiis', 
            $menu_preferences,
            $num_persons,
            $quote_amount,
            $needs_setup,
            $needs_tablesandchairs,
            $needs_decoration,
            $order_id
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error updating order details: " . mysqli_error($dbc));
        }

        // Clear existing menu items
        $clear_items = "DELETE FROM cust_catering_order_items WHERE custom_order_id = ?";
        $clear_stmt = mysqli_prepare($dbc, $clear_items);
        mysqli_stmt_bind_param($clear_stmt, "i", $order_id);
        if (!mysqli_stmt_execute($clear_stmt)) {
            throw new Exception("Error clearing existing menu items: " . mysqli_error($dbc));
        }

        // Insert new menu items if any are selected
        if (!empty($selectedItems)) {
            $insert_items = "INSERT INTO cust_catering_order_items (custom_order_id, product_id, category_id) SELECT ?, p.product_id, p.prod_cat_id FROM products p WHERE p.product_id = ?";
            $insert_stmt = mysqli_prepare($dbc, $insert_items);

            foreach ($selectedItems as $productId) {
                mysqli_stmt_bind_param($insert_stmt, "ii", $order_id, $productId);
                if (!mysqli_stmt_execute($insert_stmt)) {
                    throw new Exception("Error inserting menu item: " . mysqli_error($dbc));
                }
            }
        }

        mysqli_commit($dbc);
        echo "success";
    } catch (Exception $e) {
        mysqli_rollback($dbc);
        echo "Error: " . $e->getMessage();
    } finally {
        if (isset($stmt)) mysqli_stmt_close($stmt);
        if (isset($clear_stmt)) mysqli_stmt_close($clear_stmt);
        if (isset($insert_stmt)) mysqli_stmt_close($insert_stmt);
    }
} else {
    echo "Invalid request method";
}
?>
