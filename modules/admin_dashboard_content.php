<?php
if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {    
    exit('Unauthorized');
}
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2>Welcome, Admin</h2>
            <p class="text-muted">Manage your restaurant operations here</p>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Orders</h5>
                    <p class="card-text">View and manage customer orders</p>
                    <a href="#" class="btn btn-outline-dark" data-page="orders">Manage Orders</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Sales Report</h5>
                    <p class="card-text">View sales statistics and reports</p>
                    <a href="#" class="btn btn-outline-dark" data-page="sales">View Reports</a>
                </div>
            </div>
        </div>
    </div>
</div>