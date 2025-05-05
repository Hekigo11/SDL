<?php
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}
?>
<div class="sidebar">
    <div class="logo">
        <i class="fas fa-utensils"></i>
        <span>MARJ Foods</span>
    </div>
    
    <button class="toggle-btn">
        <i class="fas fa-chevron-left"></i>
    </button>

    <nav>
        <a href="#" class="nav-link" data-page="dashboard">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="#" class="nav-link" data-page="products">
            <i class="fas fa-box"></i>
            <span>Products</span>
        </a>
        <a href="#" class="nav-link" data-page="orders">
            <i class="fas fa-shopping-cart"></i>
            <span>Orders</span>
        </a>
        <a href="#" class="nav-link" data-page="sales">
            <i class="fas fa-chart-line"></i>
            <span>Sales Report</span>
        </a>
        <a href="#" class="nav-link" data-page="customers">
            <i class="fas fa-users"></i>
            <span>Customers</span>
        </a>
        <a href="#" class="nav-link" data-toggle="modal" data-target="#logoutModal">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>
</div>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="margin: 25vh auto;" role="document">
        <div class="modal-content" style="border-radius: 30px;">
            <div class="modal-header text-center position-center" style="background-color:var(--accent); border-radius: 30px 30px 0 0;">
                <h5 class="modal-title text-light" id="logoutModalLabel">Logout</h5>
                <button type="button" class="close text-light" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-sign-out-alt fa-3x text-muted mb-3"></i>
                <p class="mb-0">Are you sure you want to log out?</p>
            </div>
            <div class="modal-footer justify-content-center border-0">
                <a href="<?php echo BASE_URL; ?>/modules/logout.php" class="btn btn-danger px-4">Yes, Logout</a>
                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>