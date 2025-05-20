<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {    
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
        <style>
            /* Custom Select Styles */
            .custom-select {
                appearance: none;
                -webkit-appearance: none;
                -moz-appearance: none;
                background: #fff url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'%3E%3Cpath fill='%23343a40' d='M2 0L0 2h4zm0 5L0 3h4z'/%3E%3C/svg%3E") right .75rem center/8px 10px no-repeat;
                padding-right: 1.75rem;
            }

            .custom-select:focus {
                border-color: var(--accent);
                box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
            }

            /* Modal and Form Styles */
            .modal-content {
                border: none;
                box-shadow: 0 5px 30px rgba(0,0,0,0.15);
                border-radius: 15px;
            }
            .modal-header {
                padding: 1.5rem;
                border-bottom: none;
            }
            .modal-body {
                padding: 1.8rem;
            }
            .modal-footer {
                padding: 1.2rem 1.8rem;
                border-top: 1px solid rgba(0,0,0,0.05);
            }
            .form-control {
                border-radius: 8px;
                border: 1px solid rgba(0,0,0,0.1);
                padding: 0.6rem 1rem;
                transition: all 0.3s ease;
            }
            .form-control:focus {
                box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.15);
                border-color: var(--accent);
            }

            /* Button Styles */
            .btn {
                border-radius: 8px;
                padding: 0.5rem 1.2rem;
                font-weight: 500;
                transition: all 0.3s ease;
            }
            .btn-primary {
                background: var(--accent);
                border: none;
                box-shadow: 0 2px 6px rgba(var(--accent-rgb), 0.2);
            }
            .btn-primary:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(var(--accent-rgb), 0.3);
            }
            .btn-secondary {
                background: #f8f9fa;
                border: 1px solid rgba(0,0,0,0.1);
                color: #6c757d;
            }
            .btn-secondary:hover {
                background: #e9ecef;
            }
            .btn-sm {
                padding: 0.4rem 0.8rem;
                font-size: 0.875rem;
            }

            /* Card and Table Styles */
            .card {
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
                border-radius: 15px;
                border: none;
            }
            .card-header {
                background-color: #fff;
                border-bottom: 1px solid #eee;
            }
            .table th {
                border-top: none;
                font-weight: 600;
                color: #495057;
                background: #f8f9fa;
            }
            .table td {
                vertical-align: middle;
            }

            /* Image Styles */
            .product-image {
                max-width: 100px;
                max-height: 100px;
                object-fit: cover;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            #currentProductImage {
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }

            /* Tab Styles */
            #details-tab, #ingredients-tab {
                font-weight: 600;
                color: var(--primary1);
                border: solid 2px var(--primary1);
                margin: 0 10px;
            }

            /* Optgroup Styles */
            optgroup {
                font-weight: 600;
                color: #495057;
                background-color: #f8f9fa;
                padding: 6px 12px;
            }

            option {
                padding: 6px 12px;
                color: #495057;
                background-color: #fff;
            }

            /* Ingredient Search Styles */
            .ingredient-search-container {
                position: relative;
            }

            .ingredient-search-container .dropdown-menu {
                margin-top: 0;
                border: 1px solid rgba(0,0,0,0.1);
                box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                border-radius: 8px;
                padding: 8px 0;
            }

            .ingredient-search-container .dropdown-header {
                font-weight: 600;
                color: var(--accent);
                padding: 8px 16px;
                font-size: 0.875rem;
                background: rgba(var(--accent-rgb), 0.05);
            }

            .ingredient-search-container .dropdown-item {
                padding: 8px 16px;
                color: #495057;
                transition: all 0.2s ease;
            }

            .ingredient-search-container .dropdown-item:hover {
                background-color: rgba(var(--accent-rgb), 0.1);
            }

            .ingredient-search-container .dropdown-item small {
                margin-left: 8px;
                color: #6c757d;
            }

            .ingredient-search-container .dropdown-divider {
                margin: 4px 0;
            }

            .ingredient-search-container .dropdown-item-text {
                padding: 8px 16px;
                color: #6c757d;
                font-style: italic;
            }
        </style>
        <!-- Add Select2 CSS and JS -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
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
        <!-- Add Select2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            $(document).ready(function() {
                // Initialize Select2 for dynamically loaded content
                function initSelect2() {
                    $('.ingredient-select').each(function() {
                        var $select = $(this);
                        var $modal = $select.closest('.modal');
                        
                        // Destroy existing Select2 instance if any
                        if ($select.hasClass('select2-hidden-accessible')) {
                            $select.select2('destroy');
                        }
                        
                        // Configure Select2
                        var config = {
                            theme: 'bootstrap4',
                            width: '100%',
                            dropdownAutoWidth: true,
                            allowClear: true,
                            placeholder: 'Search for an ingredient...',
                            minimumInputLength: 1,
                            templateResult: formatIngredientOption,
                            templateSelection: formatIngredientSelection,
                            escapeMarkup: function(markup) { return markup; },
                            dropdownPosition: 'below',
                            dropdownParent: $modal.find('.modal-content'),
                            closeOnSelect: true
                        };

                        // Initialize Select2
                        $select.select2(config)
                            .on('select2:open', function() {
                                // Ensure proper positioning
                                setTimeout(function() {
                                    $('.select2-dropdown').css('width', $select.width());
                                    $('.select2-search__field').focus();
                                }, 0);
                            });
                    });
                }

                // Format dropdown options with group headers
                function formatIngredientOption(ingredient) {
                    if (!ingredient.id || !ingredient.element) {
                        return ingredient.text;
                    }

                    if ($(ingredient.element).is('optgroup')) {
                        return $('<div class="ingredient-group">' +
                                '<strong><i class="fas fa-layer-group mr-1"></i>' + ingredient.text + '</strong>' +
                                '</div>');
                    }

                    var $ingredient = $(
                        '<div class="ingredient-option">' +
                        '<span class="ingredient-name">' + ingredient.text + '</span>' +
                        '<small class="text-muted ml-2">(' + 
                        $(ingredient.element).data('unit') + ')</small>' +
                        '</div>'
                    );

                    return $ingredient;
                }

                // Format selected option
                function formatIngredientSelection(ingredient) {
                    if (!ingredient.id || !ingredient.element) {
                        return ingredient.text;
                    }

                    return ingredient.text + ' (' + $(ingredient.element).data('unit') + ')';
                }

                // Initialize Select2 when content is loaded
                $(document).on('shown.bs.modal', '.modal', function() {
                    initSelect2();
                });

                // Clean up Select2 when modal is hidden
                $(document).on('hidden.bs.modal', '.modal', function() {
                    $('.ingredient-select', this).select2('destroy');
                });                // Simple function to load content
                function loadContent(page) {
                    // Default to dashboard if no page specified
                    page = page || 'dashboard';
                    
                    // Extract the base page name and query string if any
                    let pageData = {};
                    if (page.includes('?')) {
                        const [pageName, queryString] = page.split('?');
                        pageData.page = pageName;
                        const urlParams = new URLSearchParams(queryString);
                        urlParams.forEach((value, key) => {
                            pageData[key] = value;
                        });
                    } else {
                        pageData.page = page;
                    }

                    $.ajax({
                        url: 'admin_content_loader.php',
                        type: 'GET',
                        data: pageData,
                        success: function(data) {
                            $('#main-content').html(data);
                            // Save current page to localStorage
                            localStorage.setItem('currentAdminPage', page);
                            // Restore sidebar state after content load
                            applySidebarState();                            // Update active state in sidebar
                            $('.nav-link').removeClass('active');
                            $('.nav-link[data-page="' + basePage + '"]').addClass('active');
                            
                            // Initialize DataTables and other components after content load
                            if (basePage === 'checklist' && $.fn.DataTable) {
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
                applySidebarState();                // Load last active page or dashboard by default
                const lastPage = localStorage.getItem('currentAdminPage') || 'dashboard';
                loadContent(lastPage);                

                // Handle menu clicks with proper event delegation
                $(document).on('click', '.nav-link', function(e) {
                    // Don't handle modal toggles
                    if ($(this).data('toggle') === 'modal') return;
                    
                    e.preventDefault();
                    const page = $(this).data('page');
                    if (page) {
                        loadContent(page);
                        // Update active state in sidebar
                        $('.nav-link').removeClass('active');
                        $(this).addClass('active');
                    }
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


