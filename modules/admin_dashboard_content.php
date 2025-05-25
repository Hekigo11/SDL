<?php
if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {    
    exit('Unauthorized');
}
include('dbconi.php');

// Pending Orders
$pending_orders = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT COUNT(*) as cnt FROM orders WHERE status = 'pending'"))['cnt'];
// Pending Ingredients (checklist items not ready)
$pending_ingredients = mysqli_fetch_assoc(mysqli_query($dbc, "
    SELECT COUNT(*) as cnt 
    FROM order_checklist oc
    INNER JOIN orders o ON oc.order_id = o.order_id
    WHERE oc.is_ready = 0 
    AND o.status = 'pending'
"))['cnt'];
// Orders Awaiting Payment
$awaiting_payment = mysqli_fetch_assoc(mysqli_query($dbc, "
    SELECT COUNT(*) as cnt 
    FROM orders 
    WHERE payment_status = 'unpaid' 
    AND payment_method != 'gcash'
    AND status != 'cancelled'
"))['cnt'];
// Weekly Revenue (sum of paid orders in the last 7 days)
$weekly_revenue = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT COALESCE(SUM(total_amount),0) as total FROM orders WHERE payment_status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"))['total'];
// Order Status Breakdown
$status_counts = [];
$status_query = mysqli_query($dbc, "SELECT status, COUNT(*) as cnt FROM orders GROUP BY status");
while ($row = mysqli_fetch_assoc($status_query)) {
    $status_counts[$row['status']] = $row['cnt'];
}
// Number of customers
$customer_count = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT COUNT(*) as cnt FROM users WHERE role_id = 2"))['cnt'];
// Number of pending catering orders (standard + custom)
$pending_catering = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT COUNT(*) as cnt FROM catering_orders WHERE status = 'pending'"))['cnt'];
$pending_custom_catering = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT COUNT(*) as cnt FROM custom_catering_orders WHERE status = 'pending'"))['cnt'];
$total_pending_catering = $pending_catering + $pending_custom_catering;
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12 mb-3">
            <h2>Admin Dashboard</h2>
            <p class="text-muted">Quick overview of your restaurant operations</p>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Delivery Orders Overview</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card text-center border-primary h-100">
                        <div class="card-body">
                            <h6 class="card-title">Pending Delivery Orders</h6>
                            <h2 class="text-primary"><?php echo $pending_orders; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center border-warning h-100">
                        <div class="card-body">
                            <h6 class="card-title">Pending Delivery Ingredients</h6>
                            <h2 class="text-warning"><?php echo $pending_ingredients; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center border-info h-100">
                        <div class="card-body">
                            <h6 class="card-title">Delivery Orders Awaiting Payment</h6>
                            <h2 class="text-info"><?php echo $awaiting_payment; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center border-success h-100">
                        <div class="card-body">
                            <h6 class="card-title">Weekly Delivery Revenue</h6>
                            <?php
                            $revenue_parts = explode('.', number_format($weekly_revenue, 2));
                            $main_amount = $revenue_parts[0];
                            $decimals = isset($revenue_parts[1]) ? $revenue_parts[1] : '00';
                            ?>
                            <h2 class="text-success" style="font-size:2.5rem; font-weight:700;">
                                <span style="font-size:1.5rem; vertical-align:super;">₱</span><?php echo $main_amount; ?><span style="font-size:1.2rem; vertical-align:super; font-weight:400;">.<?php echo $decimals; ?></span>
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-primary">
                        <div class="card-body">
                            <h5 class="card-title">Delivery Orders Status Breakdown</h5>
                            <div style="max-height: 260px; overflow-y: auto;">
                                <ul class="list-group">
                                    <?php foreach ($status_counts as $status => $cnt): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <?php echo ucfirst($status); ?> Orders
                                            <span class="badge badge-primary badge-pill"><?php echo $cnt; ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Customers</h5>
                    <h2 class="text-info" style="font-size:2.5rem; font-weight:700;"><?php echo $customer_count; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Pending Catering Orders</h5>
                    <h2 class="text-warning" style="font-size:2.5rem; font-weight:700;"><?php echo $total_pending_catering; ?></h2>
                </div>
            </div>
        </div>
    </div>
    <!-- Add more widgets as needed -->
</div>