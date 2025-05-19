<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/dbconi.php';
session_start();

try {
    $payment_ref = $_GET['reference'] ?? null;
    error_log('Payment Failed - Reference: ' . $payment_ref);

    if ($payment_ref) {
        mysqli_begin_transaction($dbc);

        // Update delivery orders
        $query = "UPDATE orders 
                 SET status = 'cancelled',
                     payment_status = 'failed',
                     updated_at = NOW()
                 WHERE payment_reference = ?";

        $stmt = mysqli_prepare($dbc, $query);
        mysqli_stmt_bind_param($stmt, "s", $payment_ref);
        mysqli_stmt_execute($stmt);

        // Update catering orders
        $query = "UPDATE catering_orders 
                 SET status = 'cancelled',
                     payment_status = 'failed',
                     updated_at = NOW()
                 WHERE payment_reference = ?";

        $stmt = mysqli_prepare($dbc, $query);
        mysqli_stmt_bind_param($stmt, "s", $payment_ref);
        mysqli_stmt_execute($stmt);

        mysqli_commit($dbc);
    }

    $_SESSION['error_message'] = 'Payment was not completed. Please try again.';
    echo "<script>
            alert('Payment failed. Redirecting back to cart...');
            window.opener.location.href = '" . BASE_URL . "/modules/cart.php';
            window.close();
          </script>";
    exit;

} catch (Exception $e) {
    if (isset($dbc)) {
        mysqli_rollback($dbc);
    }
    error_log('Payment Failed Error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Payment failed. Please try again.';
    echo "<script>
            alert('Error processing payment. Please try again.');
            window.opener.location.href = '" . BASE_URL . "/modules/cart.php';
            window.close();
          </script>";
    exit;
}