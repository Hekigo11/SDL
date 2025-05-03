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
    case 'products':
        include('admin_products.php');
        break;
    case 'orders':
        include('admin_order_details.php');
        break;
    case 'sales':
        include('admin_sales_data.php');
        break;
    default:
        include('admin_dashboard_content.php');
        break;
}
?>