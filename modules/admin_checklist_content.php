<?php
// Only run these for direct page load, not AJAX calls
if (!isset($_GET['ajax'])) {
    require_once '../config.php';
    if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
    include("dbconi.php");
}

// Get the date filter (default to 'today' if not set)
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'today';

// Build the SQL date condition
$date_condition = '';
switch($date_filter) {
    case 'today':
        $date_condition = "AND DATE(o.scheduled_delivery) = CURRENT_DATE()";
        break;
    case 'tomorrow':
        $date_condition = "AND DATE(o.scheduled_delivery) = DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY)";
        break;
    case '3days':
        $date_condition = "AND DATE(o.scheduled_delivery) BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL 2 DAY)";
        break;
    case '5days':
        $date_condition = "AND DATE(o.scheduled_delivery) BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL 4 DAY)";
        break;
    case 'week':
        $date_condition = "AND DATE(o.scheduled_delivery) BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL 6 DAY)";
        break;
    case 'all':
        $date_condition = "";
        break;
    default:
        $date_condition = "AND DATE(o.scheduled_delivery) = CURRENT_DATE()";
}

// Function to render the checklist based on date filter
function renderChecklist($dbc, $date_condition) {
    // Get consolidated ingredients from pending orders with the date condition
    $query = "SELECT 
        i.ingredient_id,
        i.name as ingredient_name,
        i.unit,
        it.type_name as station_name,
        GROUP_CONCAT(DISTINCT oc.order_id) as order_ids,
        SUM(oc.quantity_needed) as total_quantity,
        COUNT(DISTINCT oc.order_id) as order_count
    FROM order_checklist oc
    JOIN ingredients i ON oc.ingredient_id = i.ingredient_id
    JOIN ingredient_types it ON i.type_id = it.type_id
    JOIN orders o ON oc.order_id = o.order_id
    WHERE o.status = 'pending'
    AND oc.is_ready = 0
    $date_condition
    GROUP BY i.ingredient_id, i.name, i.unit, it.type_name
    ORDER BY it.type_name, i.name";
    
    $result = mysqli_query($dbc, $query);
    
    if (!$result || mysqli_num_rows($result) == 0) {
        echo '<div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No pending ingredients to prepare for the selected time period.
        </div>';
        return;
    }
    
    $current_station = '';
    while ($row = mysqli_fetch_assoc($result)) {
        if ($current_station != $row['station_name']) {
            if ($current_station != '') {
                echo '</div>';
            }
            $current_station = $row['station_name'];
            echo '<div class="station-group mb-4">
                <h5 class="station-title">
                    <i class="fas fa-utensils"></i> ' . 
                    htmlspecialchars($row['station_name']) . ' Station
                </h5>';
        }
        echo '<div class="ingredient-item card mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="ingredient-name mb-2">
                            ' . htmlspecialchars($row['ingredient_name']) . '
                        </h6>
                        <p class="mb-2">
                            <strong>Total needed:</strong> 
                            ' . number_format($row['total_quantity'], 2) . ' ' . 
                              htmlspecialchars($row['unit']) . '
                        </p>
                        <div class="small text-muted">
                            For orders: ';
                            $order_ids = explode(',', $row['order_ids']);
                            echo implode(', ', array_map(function($id) {
                                return '#' . $id;
                            }, $order_ids));
                        echo '</div>
                    </div>
                    <div class="col-md-4 text-right">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" 
                                class="custom-control-input ingredient-checkbox" 
                                id="ing_' . $row['ingredient_id'] . '" 
                                data-ingredient-id="' . $row['ingredient_id'] . '" 
                                data-order-ids="' . htmlspecialchars($row['order_ids']) . '">
                            <label class="custom-control-label" 
                                for="ing_' . $row['ingredient_id'] . '">
                                Mark as Ready
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }
    if ($current_station != '') {
        echo '</div>';
    }
}

// Function to render orders summary based on date filter
function renderOrdersSummary($dbc, $date_condition) {
    $orders_query = "SELECT 
        o.order_id,
        o.created_at,
        o.scheduled_delivery,
        GROUP_CONCAT(
            CONCAT(p.prod_name, ' (', oi.quantity, ')')
            SEPARATOR ', '
        ) as items
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN products p ON oi.product_id = p.product_id
    WHERE o.status = 'pending'
    $date_condition
    GROUP BY o.order_id
    ORDER BY o.scheduled_delivery ASC";
    
    $orders_result = mysqli_query($dbc, $orders_query);
    
    if (!$orders_result || mysqli_num_rows($orders_result) == 0) {
        echo '<div class="alert alert-info mb-0">
            <i class="fas fa-info-circle"></i> No pending orders for the selected time period.
        </div>';
        return;
    }
    
    while ($order = mysqli_fetch_assoc($orders_result)) {
        $created = new DateTime($order['created_at']);
        $scheduled = new DateTime($order['scheduled_delivery']);
        echo '<div class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-1">Order #' . $order['order_id'] . '</h6>
                <small class="text-muted">' . 
                    $scheduled->format('M j, g:i A') . 
                '</small>
            </div>
            <p class="mb-1 small">
                <strong>Items:</strong> ' . 
                htmlspecialchars($order['items']) . '
            </p>
            <small class="text-muted">
                Ordered: ' . $created->format('M j, g:i A') . '
            </small>
        </div>';
    }
}

// Handle AJAX request
if (isset($_GET['ajax'])) {
    include("dbconi.php");
    
    if (isset($_GET['content']) && $_GET['content'] === 'orders') {
        // Return only orders content
        renderOrdersSummary($dbc, $date_condition);
    } else {
        // Return ingredient checklist by default
        renderChecklist($dbc, $date_condition);
    }
    exit;
}

// Regular page load below (non-AJAX)
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Kitchen Preparation Dashboard</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list-check"></i> Pending Ingredients
                        </h5>
                        <div class="form-inline">
                            <select class="form-control form-control-sm" id="dateFilter">
                                <option value="today" <?php echo ($date_filter === 'today') ? 'selected' : ''; ?>>Today</option>
                                <option value="tomorrow" <?php echo ($date_filter === 'tomorrow') ? 'selected' : ''; ?>>Tomorrow</option>
                                <option value="3days" <?php echo ($date_filter === '3days') ? 'selected' : ''; ?>>Within 3 Days</option>
                                <option value="5days" <?php echo ($date_filter === '5days') ? 'selected' : ''; ?>>Within 5 Days</option>
                                <option value="week" <?php echo ($date_filter === 'week') ? 'selected' : ''; ?>>Within a Week</option>
                                <option value="all" <?php echo ($date_filter === 'all') ? 'selected' : ''; ?>>All Pending Orders</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body" id="checklistContent">
                    <?php renderChecklist($dbc, $date_condition); ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-clock"></i> Pending Orders
                    </h5>
                </div>
                <div class="card-body" id="ordersSummary">
                    <div class="list-group">
                        <?php renderOrdersSummary($dbc, $date_condition); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Set initial filter value
    $('#dateFilter').val('<?php echo $date_filter; ?>');
    
    // Handle date filter change
    $('#dateFilter').change(function() {
        const selectedDate = $(this).val();
        
        // Show loading indicators
        $('#checklistContent').html('<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading ingredients...</p></div>');
        $('#ordersSummary').html('<div class="text-center p-5"><div class="spinner-border text-warning" role="status"></div><p class="mt-2">Loading orders...</p></div>');
        
        // Update URL without page refresh
        const newUrl = new URL(window.location.href);
        newUrl.searchParams.set('date_filter', selectedDate);
        history.pushState({}, '', newUrl);
        
        // Load checklist content
        $.ajax({
            url: 'admin_checklist_content.php',
            method: 'GET',
            data: {
                date_filter: selectedDate,
                ajax: true
            },
            success: function(response) {
                $('#checklistContent').html(response);
                
                // Re-attach event handlers for checkboxes
                attachCheckboxHandlers();
            },
            error: function() {
                $('#checklistContent').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Failed to load ingredients. Please try again.</div>');
            }
        });
        
        // Load orders summary
        $.ajax({
            url: 'admin_checklist_content.php',
            method: 'GET',
            data: {
                date_filter: selectedDate,
                ajax: true,
                content: 'orders'
            },
            success: function(response) {
                $('#ordersSummary').html('<div class="list-group">' + response + '</div>');
            },
            error: function() {
                $('#ordersSummary').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Failed to load orders. Please try again.</div>');
            }
        });
    });
    
    // Function to attach checkbox handlers
    function attachCheckboxHandlers() {
        $('.ingredient-checkbox').change(function() {
            const checkbox = $(this);
            const ingredientId = checkbox.data('ingredient-id');
            const orderIds = checkbox.data('order-ids');
            const isChecked = checkbox.prop('checked');
            
            // Disable checkbox while processing
            checkbox.prop('disabled', true);
            
            $.ajax({
                url: 'update_consolidated_checklist.php',
                method: 'POST',
                data: {
                    ingredient_id: ingredientId,
                    order_ids: orderIds,
                    is_ready: isChecked ? 1 : 0
                },
                success: function(response) {
                    try {
                        const data = typeof response === 'string' ? JSON.parse(response) : response;
                        if (data.success) {
                            // Fade out the ingredient card
                            checkbox.closest('.ingredient-item').fadeOut(300, function() {
                                $(this).remove();
                                // Check if station is empty
                                if ($('.station-group:has(.ingredient-item)').length === 0) {
                                    // Reload the checklist if no more ingredients
                                    $('#dateFilter').trigger('change');
                                }
                            });
                        } else {
                            alert('Error: ' + data.error);
                            checkbox.prop('checked', !isChecked);
                        }
                    } catch (e) {
                        console.error('Error:', e);
                        alert('Error processing response');
                        checkbox.prop('checked', !isChecked);
                    }
                    checkbox.prop('disabled', false);
                },
                error: function() {
                    alert('Failed to update ingredient status');
                    checkbox.prop('checked', !isChecked);
                    checkbox.prop('disabled', false);
                }
            });
        });
    }
    
    // Initial attachment of checkbox handlers
    attachCheckboxHandlers();
});
</script>

<style>
.station-group {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.station-title {
    color: #2c3e50;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 10px;
    margin-bottom: 15px;
}
.ingredient-item {
    transition: all 0.3s ease;
}
.ingredient-item:hover {
    transform: translateX(5px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.ingredient-name {
    color: #2c3e50;
    font-weight: 600;
}
.list-group-item {
    transition: all 0.3s ease;
}
.list-group-item:hover {
    background-color: #f8f9fa;
}
</style>