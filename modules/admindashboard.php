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
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <link rel="stylesheet" href="../vendor/style1.css">
        <link rel="stylesheet" href="../vendor/admin.css">
        <title>Admin Dashboard - MARJ</title>
    </head>

    <body>
        <?php include("adminnav.php"); ?>

        <div class="content expanded" id="main-content">
            <!-- Content will be loaded here via AJAX -->
        </div>

        <script>
            $(document).ready(function() {
                // Simple function to load content
                function loadContent(page) {
                    $.ajax({
                        url: 'admin_content_loader.php',
                        type: 'GET',
                        data: { page: page || 'dashboard' },
                        success: function(data) {
                            $('#main-content').html(data);
                            // Save current page to localStorage
                            localStorage.setItem('currentAdminPage', page);
                        },
                        error: function() {
                            $('#main-content').html('<div class="alert alert-danger">Error loading content</div>');
                        }
                    });
                }

                // Restore sidebar state
                const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                if (isCollapsed) {
                    $('.sidebar').addClass('collapsed');
                    $('.content').addClass('expanded');
                } else {
                    $('.sidebar').removeClass('collapsed');
                    $('.content').removeClass('expanded');
                }

                // Load last active page or dashboard by default
                const lastPage = localStorage.getItem('currentAdminPage') || 'dashboard';
                loadContent(lastPage);
                
                // Set active state on the correct nav link
                $('.nav-link[data-page="' + lastPage + '"]').addClass('active');

                // Handle menu clicks
                $('.nav-link').click(function(e) {
                    if ($(this).data('toggle') === 'modal') return; // Don't handle modal toggles
                    e.preventDefault();
                    var page = $(this).data('page');
                    loadContent(page);
                    
                    // Update active state
                    $('.nav-link').removeClass('active');
                    $(this).addClass('active');
                });

                // Sidebar toggle with state persistence
                $('.toggle-btn').click(function() {
                    $('.sidebar').toggleClass('collapsed');
                    $('.content').toggleClass('expanded');
                    // Save sidebar state
                    localStorage.setItem('sidebarCollapsed', $('.sidebar').hasClass('collapsed'));
                });
            });
        </script>
    </body>
</html>


