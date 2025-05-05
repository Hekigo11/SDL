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
                            // Restore sidebar state after content load
                            applySidebarState();
                            // Update active state
                            $('.nav-link').removeClass('active');
                            $('.nav-link[data-page="' + page + '"]').addClass('active');
                        },
                        error: function() {
                            $('#main-content').html('<div class="alert alert-danger">Error loading content</div>');
                        }
                    });
                }

                // Apply sidebar state
                function applySidebarState() {
                    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                    $('.sidebar').toggleClass('collapsed', isCollapsed);
                    $('.content').toggleClass('expanded', isCollapsed);
                    $('.toggle-btn i').toggleClass('fa-chevron-right', isCollapsed)
                                    .toggleClass('fa-chevron-left', !isCollapsed);
                }

                // Initialize on page load
                applySidebarState();

                // Load last active page or dashboard by default
                const lastPage = localStorage.getItem('currentAdminPage') || 'dashboard';
                loadContent(lastPage);

                // Handle menu clicks
                $('.nav-link').click(function(e) {
                    if ($(this).data('toggle') === 'modal') return; // Don't handle modal toggles
                    e.preventDefault();
                    const page = $(this).data('page');
                    loadContent(page);
                });

                // Sidebar toggle with state persistence
                $('.toggle-btn').click(function() {
                    const willBeCollapsed = !$('.sidebar').hasClass('collapsed');
                    $('.sidebar').toggleClass('collapsed');
                    $('.content').toggleClass('expanded');
                    // Update toggle button icon
                    $(this).find('i').toggleClass('fa-chevron-right', willBeCollapsed)
                                   .toggleClass('fa-chevron-left', !willBeCollapsed);
                    // Save sidebar state
                    localStorage.setItem('sidebarCollapsed', willBeCollapsed);
                });

                // Handle page refresh and back/forward navigation
                $(window).on('pageshow', function() {
                    applySidebarState();
                });
            });
        </script>
    </body>
</html>


