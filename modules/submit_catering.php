<?php
require_once __DIR__ . '/../config.php';
require_once 'dbconi.php';

session_start();
if (!isset($_SESSION['loginok'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['client_name']) || empty($_POST['contact_info']) || empty($_POST['event_date']) || 
        empty($_POST['num_persons']) || empty($_POST['venue']) || empty($_POST['occasion'])) {
        $_SESSION['error'] = 'Please fill in all required fields';
        header('Location: ' . BASE_URL . '/modules/catering.php');
        exit;
    }

    try {
        mysqli_begin_transaction($dbc);

        // Calculate total amount
        $total_amount = 0;
        $num_persons = intval($_POST['num_persons']);
        
        // Add menu package cost
        switch($_POST['menu_bundle']) {
            case 'basic':
                $total_amount += $num_persons * 250;
                break;
            case 'premium':
                $total_amount += $num_persons * 450;
                break;
            case 'executive':
                $total_amount += $num_persons * 650;
                break;
        }

        // Add additional services cost
        $options = isset($_POST['options']) ? $_POST['options'] : array();
        if (!empty($options)) {
            foreach($options as $option) {
                switch($option) {
                    case 'setup':
                        $total_amount += 2000;
                        break;
                    case 'tables':
                        $total_amount += 3500;
                        break;
                    case 'decoration':
                        $total_amount += 5000;
                        break;
                }
            }
        }

        // Process options flags
        $has_setup = in_array('setup', $options);
        $has_tables = in_array('tables', $options);
        $has_decoration = in_array('decoration', $options);
        $other_requests = isset($_POST['other_requests']) ? $_POST['other_requests'] : '';

        $query = "INSERT INTO catering_orders (user_id, full_name, phone, event_date, num_persons, 
                  venue, occasion, setup_type, needs_tables, needs_decoration, 
                  menu_package, special_requests, total_amount, payment_method, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')";

        $stmt = mysqli_prepare($dbc, $query);
        mysqli_stmt_bind_param($stmt, "isssississssd", 
            $_SESSION['user_id'],
            $_POST['client_name'],
            $_POST['contact_info'],
            $_POST['event_date'],
            $num_persons,
            $_POST['venue'],
            $_POST['occasion'],
            $_POST['menu_bundle'],
            $has_setup,
            $has_tables,
            $has_decoration,
            $other_requests,
            $total_amount
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error creating catering order");
        }

        mysqli_commit($dbc);
        $_SESSION['success'] = 'Catering request submitted successfully!';
        header('Location: ' . BASE_URL . '/modules/orders.php');
        exit;

    } catch (Exception $e) {
        mysqli_rollback($dbc);
        error_log("Catering Order Error: " . $e->getMessage());
        $_SESSION['error'] = 'Error submitting request. Please try again.';
        header('Location: ' . BASE_URL . '/modules/catering.php');
        exit;
    } finally {
        mysqli_close($dbc);
    }
}
?>