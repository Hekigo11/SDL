<?php
session_start();
if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    exit('Unauthorized');
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; //dedefault to pa dashboard

switch($page) {
    case 'dashboard':
        include('admin_dashboard_content.php');
        break;
    case 'ingredients':
        include('admin_ingredients_content.php');
        break;
    case 'products':
        include('admin_products.php');
        break;
    case 'packages':
        include('admin_packages.php');
        break;
    case 'orders':
        include('admin_order_details.php');
        break;
    case 'checklist':
        include('admin_checklist_content.php');
        break;
    case 'sales':
        if (isset($_GET['is_ajax']) && $_GET['is_ajax']) {
            // For AJAX requests, update session dates
            if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
                $parsed_date = date_create_from_format('Y-m-d', $_GET['start_date']);
                if ($parsed_date !== false) {
                    $_SESSION['sales_start_date'] = $parsed_date->format('Y-m-d');
                }
            }
            if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
                $parsed_date = date_create_from_format('Y-m-d', $_GET['end_date']);
                if ($parsed_date !== false) {
                    $_SESSION['sales_end_date'] = $parsed_date->format('Y-m-d');
                }
            }
        }
        include('admin_sales_data.php');
        break;
    default:
        include('admin_dashboard_content.php');
        break;
}
?>