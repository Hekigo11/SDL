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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
        <link rel="stylesheet" href="../vendor/style1.css">
        <link rel="stylesheet" href="../vendor/admin.css">
        <title>Admin Dashboard - MARJ</title>
    </head>

    <body>
        <?php include("adminnav.php"); ?>

        <div class="content expanded" id="main-content">
            <!-- Content will be loaded here via AJAX -->
        </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

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
                            
                            // Initialize DataTables and other components after content load
                            if (page === 'checklist' && $.fn.DataTable) {
                                if ($.fn.DataTable.isDataTable('#checklistTable')) {
                                    $('#checklistTable').DataTable().destroy();
                                }
                                $('#checklistTable').DataTable({
                                    ordering: false,
                                    language: {
                                        search: "Filter orders: "
                                    }
                                });
                            }
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
                    if ($(this).data('toggle') === 'modal') return;
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

                // Global event handlers for dynamically loaded content
                $(document).on('click', '.view-checklist', function() {
                    const orderId = $(this).data('id');
                    const items = $(this).data('items');
                    const notes = $(this).data('notes');
                    
                    // Format items as a list
                    $('#orderItems').html(
                        items.split(', ').map(item => 
                            `<div class="mb-2"><i class="fas fa-utensils text-muted mr-2"></i>${item}</div>`
                        ).join('')
                    );
                    
                    // Display notes
                    $('#orderNotes').html(
                        notes ? notes : '<em>No special notes provided</em>'
                    );
                    
                    // Show loading state
                    $('#orderChecklist').html(`
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <div class="mt-2">Loading checklist...</div>
                        </div>
                    `);
                    
                    // Load checklist
                    $.get('admin_get_checklist.php', { order_id: orderId })
                        .done(function(response) {
                            $('#orderChecklist').html(response);
                        })
                        .fail(function() {
                            $('#orderChecklist').html(`
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle"></i> Failed to load checklist. Please try again.
                                </div>
                            `);
                        });

                    $('#checklistModal').modal('show');
                });

                // Handle checklist updates
                $(document).on('change', '.checklist-checkbox', function() {
                    const checkbox = $(this);
                    const itemId = checkbox.data('id');
                    const isChecked = checkbox.prop('checked');
                    
                    checkbox.prop('disabled', true);
                    const label = checkbox.closest('.custom-control').find('.custom-control-label');
                    const originalText = label.html();
                    label.html('<em>Updating...</em>');
                    
                    $.post('admin_update_checklist.php', {
                        item_id: itemId,
                        is_ready: isChecked ? 1 : 0
                    })
                    .done(function(response) {
                        if (response === 'success') {
                            const orderId = itemId.split('-')[0];
                            $.get('admin_get_checklist.php', { order_id: orderId })
                                .done(function(response) {
                                    $('#orderChecklist').html(response);
                                    
                                    // Update progress in the main table
                                    $.get('get_checklist_progress.php', { order_id: orderId })
                                        .done(function(progressData) {
                                            try {
                                                const result = JSON.parse(progressData);
                                                if (result.success) {
                                                    const progressElement = $(`[data-progress="${orderId}"]`);
                                                    if (progressElement.length) {
                                                        progressElement.html(`${result.completed}/${result.total} items ready`);
                                                        
                                                        const progress = Math.round((result.completed / result.total) * 100);
                                                        const progressBar = progressElement.closest('td').find('.progress-bar');
                                                        progressBar.css('width', progress + '%').attr('aria-valuenow', progress);
                                                    }
                                                }
                                            } catch(e) {
                                                console.error('Error parsing progress data:', e);
                                            }
                                        });
                                });
                        } else {
                            checkbox.prop('checked', !isChecked);
                            alert(response);
                        }
                    })
                    .fail(function(jqXHR) {
                        checkbox.prop('checked', !isChecked);
                        alert('Update failed: ' + (jqXHR.responseText || 'Please try again'));
                    })
                    .always(function() {
                        checkbox.prop('disabled', false);
                        label.html(originalText);
                    });
                });
            });
        </script>
    </body>
</html>


