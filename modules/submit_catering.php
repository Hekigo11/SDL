<?php
require_once __DIR__ . '/../config.php';
require_once 'dbconi.php';

session_start();
if (!isset($_SESSION['loginok'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic required fields check
    $required_fields = ['client_name', 'contact_info', 'email', 'num_persons', 
                       'venue_street_number', 'venue_street_name', 'venue_barangay', 
                       'venue_city', 'venue_province', 'venue_zip',
                       'occasion', 'payment_method', 'menu_bundle'];
    
    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    // Date/time validation - check if either "quick date" or "specific date" is provided
    $has_date_time = false;
    if (isset($_POST['event_date_type']) && $_POST['event_date_type'] === 'next_available') {
        if (!empty($_POST['quick_date']) && !empty($_POST['quick_event_time'])) {
            $has_date_time = true;
        } else {
            $missing_fields[] = 'quick_date';
            $missing_fields[] = 'quick_event_time';
        }
    } else {
        if (!empty($_POST['event_date']) && !empty($_POST['event_time'])) {
            $has_date_time = true;
        } else {
            $missing_fields[] = 'event_date';
            $missing_fields[] = 'event_time';
        }
    }
    
    if (!empty($missing_fields)) {
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

    // Combine date and time - handle both date selection methods
    $event_datetime = '';
    if (isset($_POST['event_date_type']) && $_POST['event_date_type'] === 'next_available') {
        // Using the recommended date selection
        $event_datetime = $_POST['quick_date'] . ' ' . $_POST['quick_event_time'] . ':00';
    } else {
        // Using the specific date selection
        $event_datetime = $_POST['event_date'] . ' ' . $_POST['event_time'] . ':00';
    }
    
    // Validate event date - comparing dates only (not time)
    $event_date = new DateTime($event_datetime);
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

    // Collect and combine the segmented venue address fields
    $venue_street_number = trim($_POST['venue_street_number'] ?? '');
    $venue_street_name = trim($_POST['venue_street_name'] ?? '');
    $venue_barangay = trim($_POST['venue_barangay'] ?? '');
    $venue_city = trim($_POST['venue_city'] ?? '');
    $venue_province = trim($_POST['venue_province'] ?? '');
    $venue_zip = trim($_POST['venue_zip'] ?? '');
    $venue_details = trim($_POST['venue_details'] ?? '');
    
    // Construct complete venue address
    $venue = $venue_street_number . ' ' . $venue_street_name . ', ' . 
             $venue_barangay . ', ' . $venue_city . ', ' . 
             $venue_province . ' ' . $venue_zip;
    
    // Add venue details if provided
    if (!empty($venue_details)) {
        $venue .= ' (' . $venue_details . ')';
    }

    // Validate menu selections for standard packages
    $menu_package = $_POST['menu_bundle'];
    $is_custom_package = ($menu_package === 'Custom Package');
    if (!$is_custom_package) {
        $package_requirements = json_decode($_POST['packageRequirements'], true);
        if ($package_requirements) {
            $menu_selections = $_POST['menu_selections'] ?? [];
            
            // Validate each category's requirements
            foreach ($package_requirements as $category => $required) {
                $selected = isset($menu_selections[$category]) ? count($menu_selections[$category]) : 0;
                if ($selected < $required) {
                    $_SESSION['error'] = "Please select {$required} items from {$category}";
                    header('Location: ' . BASE_URL . '/modules/catering/step2.php');
                    exit;
                }
            }
        }
    }

    try {
        mysqli_begin_transaction($dbc);

        // Check if custom_catering_orders table exists, create it if not
        $menu_package = $_POST['menu_bundle'];
        $is_custom_package = ($menu_package === 'Custom Package');
        $table_check_query = "SHOW TABLES LIKE 'custom_catering_orders'";
        $table_check_result = mysqli_query($dbc, $table_check_query);
        
        // Add any missing columns to the custom_catering_orders table
        if (mysqli_num_rows($table_check_result) > 0) {
            // Check if staff_notes column exists
            $column_check_query = "SHOW COLUMNS FROM custom_catering_orders LIKE 'staff_notes'";
            $column_check_result = mysqli_query($dbc, $column_check_query);
            if (mysqli_num_rows($column_check_result) == 0) {
                // Add staff_notes column
                $add_column_query = "ALTER TABLE custom_catering_orders ADD COLUMN staff_notes TEXT";
                mysqli_query($dbc, $add_column_query);
            }
        }

        // Check if it's a custom package order or small group order (less than 50 people)
        $is_small_group = ($num_persons < 50);
        $is_special_request = $is_custom_package || $is_small_group;
        
        // Process options flags
        $options = isset($_POST['options']) ? $_POST['options'] : array();
        $has_tablesandchairs = in_array('tables', $options) ? 1 : 0;
        $has_setup = in_array('setup', $options) ? 1 : 0;
        $has_decoration = in_array('decoration', $options) ? 1 : 0;
        $other_requests = isset($_POST['other_requests']) ? $_POST['other_requests'] : '';
        
        // Calculate additional services cost 
        $services_cost = 0;
        if (!empty($options)) {
            foreach($options as $option) {
                switch($option) {
                    case 'setup':
                        $services_cost += 2000;
                        break;
                    case 'tables':
                        $services_cost += 3500;
                        break;
                    case 'decoration':
                        $services_cost += 5000;
                        break;
                }
            }
        }
        
        // Use different tables for standard vs. custom orders
        if ($is_special_request) {
            // For custom_catering_orders table
            $query = "INSERT INTO custom_catering_orders (
                user_id, full_name, phone, email, event_date, num_persons, 
                venue, occasion, menu_preferences, needs_tablesandchairs, needs_setup,
                needs_decoration, special_requests, payment_method, status, estimated_budget
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)";
            
            $stmt = mysqli_prepare($dbc, $query);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . mysqli_error($dbc));
            }
            
            // Set estimated budget to the service costs (since package cost will be determined later)
            $estimated_budget = $services_cost;
            
            mysqli_stmt_bind_param($stmt, "issssisssiiissd", 
                $_SESSION['user_id'],
                $_POST['client_name'],
                $_POST['contact_info'],
                $_POST['email'],
                $event_datetime,
                $num_persons,
                $venue,
                $_POST['occasion'],
                $menu_package,
                $has_tablesandchairs,
                $has_setup,
                $has_decoration,
                $other_requests,
                $_POST['payment_method'],
                $estimated_budget
            );
        } else {
            // Standard package - use regular catering_orders table            // Get package price from the packages table
            $package_query = "SELECT base_price FROM packages WHERE name = ? LIMIT 1";
            $stmt = mysqli_prepare($dbc, $package_query);
            mysqli_stmt_bind_param($stmt, "s", $menu_package);
            mysqli_stmt_execute($stmt);
            $package_result = mysqli_stmt_get_result($stmt);
            $package_data = mysqli_fetch_assoc($package_result);
            
            if (!$package_data) {
                throw new Exception("Invalid package selected");
            }
            
            // Calculate total amount
            $package_price = $package_data['base_price'];
            $total_amount = ($num_persons * $package_price) + $services_cost;

            // Convert needs_setup from tinyint to varchar as per the database structure
            $needs_setup_str = $has_setup ? '1' : '0';

            $query = "INSERT INTO catering_orders (
                user_id, full_name, phone, email, event_date, num_persons, 
                venue, occasion, menu_package, needs_tablesandchairs, needs_setup,
                needs_decoration, special_requests, total_amount, payment_method, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

            $stmt = mysqli_prepare($dbc, $query);
            mysqli_stmt_bind_param($stmt, "issssisssiissds", 
                $_SESSION['user_id'],
                $_POST['client_name'],
                $_POST['contact_info'],
                $_POST['email'],
                $event_datetime,
                $num_persons,
                $venue,
                $_POST['occasion'],
                $menu_package,
                $has_tablesandchairs,
                $needs_setup_str,
                $has_decoration,
                $other_requests,
                $total_amount,
                $_POST['payment_method']
            );
        }

        if (!mysqli_stmt_execute($stmt)) {
            $error_message = mysqli_stmt_error($stmt);
            error_log("Catering Order SQL Error: " . $error_message);
            throw new Exception("Error creating catering order: " . $error_message);
        }
        
        // Get the newly created catering order ID
        $catering_order_id = mysqli_insert_id($dbc);
        
        // Insert menu selections if not a custom package
        if ((!$is_custom_package && !$is_small_group) && isset($_POST['menu_selections'])) {
            foreach ($_POST['menu_selections'] as $category => $selections) {
                foreach ($selections as $product_id) {
                    // Validate product exists and belongs to the category
                    $validate_product = "SELECT p.product_id, c.category_id 
                                       FROM products p 
                                       JOIN categories c ON p.prod_cat_id = c.category_id 
                                       WHERE p.product_id = ? AND c.category_name = ?";
                    $validate_stmt = mysqli_prepare($dbc, $validate_product);
                    mysqli_stmt_bind_param($validate_stmt, "is", $product_id, $category);
                    mysqli_stmt_execute($validate_stmt);
                    $validate_result = mysqli_stmt_get_result($validate_stmt);
                    
                    if (!$validate_result || mysqli_num_rows($validate_result) === 0) {
                        throw new Exception("Invalid product selection");
                    }
                    
                    $product_data = mysqli_fetch_assoc($validate_result);

                    // Insert menu selection
                    $menu_query = "INSERT INTO catering_order_menu_items 
                                 (catering_order_id, product_id, category_id) 
                                 VALUES (?, ?, ?)";
                    $menu_stmt = mysqli_prepare($dbc, $menu_query);
                    mysqli_stmt_bind_param($menu_stmt, "iii", 
                        $catering_order_id, 
                        $product_id,
                        $product_data['category_id']
                    );

                    if (!mysqli_stmt_execute($menu_stmt)) {
                        throw new Exception("Error storing menu selection");
                    }
                }
            }
        }

        // Insert menu selections for custom catering orders and small group orders
        if (($is_custom_package || $is_small_group) && isset($_POST['menu_selections'])) {
            foreach ($_POST['menu_selections'] as $category => $selections) {
                foreach ($selections as $product_id) {
                    // Validate product exists and belongs to the category
                    $validate_product = "SELECT p.product_id, c.category_id 
                                       FROM products p 
                                       JOIN categories c ON p.prod_cat_id = c.category_id 
                                       WHERE p.product_id = ? AND c.category_name = ?";
                    $validate_stmt = mysqli_prepare($dbc, $validate_product);
                    mysqli_stmt_bind_param($validate_stmt, "is", $product_id, $category);
                    mysqli_stmt_execute($validate_stmt);
                    $validate_result = mysqli_stmt_get_result($validate_stmt);
                    
                    if (!$validate_result || mysqli_num_rows($validate_result) === 0) {
                        throw new Exception("Invalid product selection");
                    }
                    
                    $product_data = mysqli_fetch_assoc($validate_result);

                    // Insert menu selection into cust_catering_order_items
                    $menu_query = "INSERT INTO cust_catering_order_items 
                                 (custom_order_id, product_id, category_id) 
                                 VALUES (?, ?, ?)";
                    $menu_stmt = mysqli_prepare($dbc, $menu_query);
                    mysqli_stmt_bind_param($menu_stmt, "iii", 
                        $catering_order_id, 
                        $product_id,
                        $product_data['category_id']
                    );

                    if (!mysqli_stmt_execute($menu_stmt)) {
                        throw new Exception("Error storing custom menu selection");
                    }
                }
            }
        }

        mysqli_commit($dbc);
        
        // Different success message for custom/small group orders
        if ($is_special_request) {
            $_SESSION['success'] = 'Your special catering request has been submitted! Our staff will contact you shortly to discuss details and pricing. <a href="' . BASE_URL . '/modules/orders.php" class="alert-link">View your orders</a>';
        } else {
            $_SESSION['success'] = 'Catering request submitted successfully! <a href="' . BASE_URL . '/modules/orders.php" class="alert-link">View your order</a>';
        }

        // Clear catering-related session data after successful submission
        unset($_SESSION['catering_form']);
        unset($_SESSION['catering_step1']);
        
        header('Location: ' . BASE_URL . '/modules/catering.php');
        exit;

    } catch (Exception $e) {
        mysqli_rollback($dbc);
        error_log("Catering Order Error: " . $e->getMessage());
        $_SESSION['error'] = 'Error submitting request. Please try again. Error: ' . $e->getMessage();
        header('Location: ' . BASE_URL . '/modules/catering.php');
        exit;
    } finally {
        mysqli_close($dbc);
    }
}
?>