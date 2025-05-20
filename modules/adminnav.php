<?php
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {    
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}
?>
<div class="sidebar">
    <div class="logo" style="width: 100%; text-align: center; padding: 20px 0;">
        <i class="fas fa-utensils"></i>
        <span>MARJ Foods</span>
    </div>
    <button class="toggle-btn">
        <i class="fas fa-chevron-left"></i>
    </button>
    <nav class="sidebar-nav">
        <a href="#" class="nav-link" data-page="dashboard">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="#" class="nav-link" data-page="products">
            <i class="fas fa-box"></i>
            <span>Products</span>
        </a>
        <a href="#" class="nav-link" data-page="packages">
            <i class="fas fa-utensils"></i>
            <span>Catering Packages</span>
        </a>
        <a href="#" class="nav-link" data-page="ingredients">
            <i class="fas fa-mortar-pestle"></i>
            <span>Ingredients</span>
        </a>
        <a href="#" class="nav-link" data-page="orders">
            <i class="fas fa-shopping-cart"></i>
            <span>Orders</span>
        </a>
        <a href="#" class="nav-link" data-page="checklist">
            <i class="fas fa-tasks"></i>
            <span>Kitchen Checklist</span>
        </a>
        <a href="#" class="nav-link" data-page="sales">
            <i class="fas fa-chart-line"></i>
            <span>Sales Report</span>
        </a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 1) : ?>
        <a href="#" class="nav-link" data-page="users">
            <i class="fas fa-users-cog"></i>
            <span>Manage Users</span>
        </a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-logout">
        <a href="#" class="nav-link" data-toggle="modal" data-target="#logoutModal">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
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

<style>
.sidebar {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: #223046;
 
  transition: width 0.3s;
}
.sidebar-nav {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}
.sidebar .nav-link {
  width: 100%;
  text-align: center;
  margin: 0.5rem 0;
  padding: 0.5rem 0;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 16px;
  color: #fff;
  font-size: 1.1rem;
  transition: background 0.2s;
}
.sidebar .nav-link i {
  font-size: 1.4rem;
  min-width: 32px;
  text-align: center;
}
.sidebar-logout {
  width: 100%;
  padding-bottom: 1rem;
  display: flex;
  justify-content: center;
}
.sidebar .logo {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 12px;
  color: #fff;
  font-size: 1.3rem;
  padding-left: 18px;
}
@media (max-width: 768px) {
  .sidebar {
    width: 60px !important;
    min-width: 60px !important;
    max-width: 60px !important;
  }
  .sidebar .nav-link span,
  .sidebar .logo span {
    display: none !important;
  }
  .sidebar .logo {
    justify-content: center;
    padding-left: 0;
  }
  .sidebar .nav-link {
    justify-content: center;
    gap: 0;
  }
  .sidebar-logout {
    justify-content: center;
  }
}
</style>

