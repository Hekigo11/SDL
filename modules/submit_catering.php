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
        empty($_POST['num_persons']) || empty($_POST['venue']) || empty($_POST['occasion']) ||
        empty($_POST['payment_method']) || empty($_POST['menu_bundle']) || empty($_POST['email'])) {
        $_SESSION['error'] = 'Please fill in all required fields';
        header('Location: ' . BASE_URL . '/modules/catering.php');
        exit;
    }

    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Please enter a valid email address';
        header('Location: ' . BASE_URL . '/modules/catering.php');
        exit;
    }

    // Validate event date - comparing dates only (not time)
    $event_date = new DateTime($_POST['event_date']);
    $event_date->setTime(0, 0, 0);
    
    $today = new DateTime('today');
    $today->setTime(0, 0, 0);
    
    $num_persons = intval($_POST['num_persons']);
    $min_days = $num_persons >= 100 ? 14 : 3; // Adding 1 to get full days
    
    $min_date = clone $today;
    $min_date->modify("+$min_days days");
    
    if ($event_date < $min_date) {
        $_SESSION['error'] = $num_persons >= 100 
            ? "For groups of 100 or more, please book at least 14 days in advance (earliest available date is " . $min_date->format('M j, Y') . ")"
            : "Please book at least 3 days in advance (earliest available date is " . $min_date->format('M j, Y') . ")";
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
            case 'Basic Filipino Package':
                $total_amount += $num_persons * 250;
                break;
            case 'Premium Filipino Package':
                $total_amount += $num_persons * 450;
                break;
            case 'Executive Package':
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
        $has_tablesandchairs = in_array('tables', $options);
        $has_setup = in_array('setup', $options);
        $has_decoration = in_array('decoration', $options);
        $other_requests = isset($_POST['other_requests']) ? $_POST['other_requests'] : '';

        $query = "INSERT INTO catering_orders (
            user_id, full_name, phone, email, event_date, num_persons, 
            venue, occasion, menu_package, needs_tablesandchairs, needs_setup,
            needs_decoration, special_requests, total_amount, payment_method, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

        $stmt = mysqli_prepare($dbc, $query);
        mysqli_stmt_bind_param($stmt, "issssisssiiisds", 
            $_SESSION['user_id'],
            $_POST['client_name'],
            $_POST['contact_info'],
            $_POST['email'],
            $_POST['event_date'],
            $num_persons,
            $_POST['venue'],
            $_POST['occasion'],
            $_POST['menu_bundle'],
            $has_tablesandchairs,
            $has_setup,
            $has_decoration,
            $other_requests,
            $total_amount,
            $_POST['payment_method']
        );

        if (!mysqli_stmt_execute($stmt)) {
            error_log("Catering Order SQL Error: " . mysqli_stmt_error($stmt));
            throw new Exception("Error creating catering order: " . mysqli_stmt_error($stmt));
        }

        mysqli_commit($dbc);
        $_SESSION['success'] = 'Catering request submitted successfully! <a href="' . BASE_URL . '/modules/orders.php" class="alert-link">View your order</a>';
        header('Location: ' . BASE_URL . '/modules/catering.php');
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