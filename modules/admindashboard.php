<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <!-- Popper JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
        <!-- Latest compiled JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <link rel="stylesheet" href="../vendor/style1.css">
        <link rel="stylesheet" href="../vendor/admin.css">
        <title>Admin Dashboard - MARJ</title>
    </head>

    <body>
        <?php include("adminnav.php"); ?>

        <div class="content">
            <div class="dashboard-header">
                <h1 class="mb-0">Dashboard</h1>
                <p class="text-muted mb-0">Welcome back, <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Admin'; ?></p>
            </div>

            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <div class="value">0</div>
                    <p class="mb-0 text-muted">Last 30 days</p>
                </div>
                
                <div class="stat-card">
                    <h3>Revenue</h3>
                    <div class="value">₱0</div>
                    <p class="mb-0 text-muted">Last 30 days</p>
                </div>
                
                <div class="stat-card">
                    <h3>Active Orders</h3>
                    <div class="value">0</div>
                    <p class="mb-0 text-muted">Pending delivery</p>
                </div>
                
                <div class="stat-card">
                    <h3>Customers</h3>
                    <div class="value">0</div>
                    <p class="mb-0 text-muted">Total registered</p>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sidebar = document.querySelector('.sidebar');
                const content = document.querySelector('.content');
                const toggleBtn = document.querySelector('.toggle-btn');
                const navLinks = document.querySelectorAll('.nav-link');

                // Set active nav link based on current page
                const currentPage = window.location.pathname.split('/').pop();
                navLinks.forEach(link => {
                    if (link.getAttribute('href') === currentPage) {
                        link.classList.add('active');
                    }
                });

                // Toggle sidebar
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    content.classList.toggle('expanded');
                });

                // Mobile menu toggle
                if (window.innerWidth <= 768) {
                    toggleBtn.addEventListener('click', function() {
                        sidebar.classList.toggle('active');
                    });

                    // Close sidebar when clicking outside
                    document.addEventListener('click', function(e) {
                        if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                            sidebar.classList.remove('active');
                        }
                    });
                }
            });
        </script>
    </body>
</html>


