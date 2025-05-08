<?php
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}
include("dbconi.php");
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Kitchen Preparation Dashboard</h2>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="row">
        <!-- Consolidated Ingredients Card -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list-check"></i> Pending Ingredients
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    // Get consolidated ingredients from pending orders
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
                    GROUP BY i.ingredient_id, i.name, i.unit, it.type_name
                    ORDER BY it.type_name, i.name";

                    $result = mysqli_query($dbc, $query);

                    if (!$result || mysqli_num_rows($result) == 0) {
                        echo '<div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No pending ingredients to prepare.
                        </div>';
                    } else {
                        $current_station = '';
                        
                        while ($row = mysqli_fetch_assoc($result)) {
                            if ($current_station != $row['station_name']) {
                                if ($current_station != '') {
                                    echo '</div>'; // Close previous station
                                }
                                $current_station = $row['station_name'];
                                echo '<div class="station-group mb-4">
                                    <h5 class="station-title">
                                        <i class="fas fa-utensils"></i> ' . 
                                        htmlspecialchars($row['station_name']) . ' Station
                                    </h5>';
                            }
                            ?>
                            <div class="ingredient-item card mb-3">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h6 class="ingredient-name mb-2">
                                                <?php echo htmlspecialchars($row['ingredient_name']); ?>
                                            </h6>
                                            <p class="mb-2">
                                                <strong>Total needed:</strong> 
                                                <?php echo number_format($row['total_quantity'], 2) . ' ' . 
                                                      htmlspecialchars($row['unit']); ?>
                                            </p>
                                            <div class="small text-muted">
                                                For orders: <?php 
                                                    $order_ids = explode(',', $row['order_ids']);
                                                    echo implode(', ', array_map(function($id) {
                                                        return '#' . $id;
                                                    }, $order_ids));
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" 
                                                    class="custom-control-input ingredient-checkbox" 
                                                    id="ing_<?php echo $row['ingredient_id']; ?>" 
                                                    data-ingredient-id="<?php echo $row['ingredient_id']; ?>"
                                                    data-order-ids="<?php echo htmlspecialchars($row['order_ids']); ?>">
                                                <label class="custom-control-label" 
                                                    for="ing_<?php echo $row['ingredient_id']; ?>">
                                                    Mark as Ready
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        if ($current_station != '') {
                            echo '</div>'; // Close last station
                        }
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Pending Orders Summary -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-clock"></i> Pending Orders
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php
                        $orders_query = "SELECT 
                            o.order_id,
                            o.created_at,
                            GROUP_CONCAT(
                                CONCAT(p.prod_name, ' (', oi.quantity, ')')
                                SEPARATOR ', '
                            ) as items
                        FROM orders o
                        JOIN order_items oi ON o.order_id = oi.order_id
                        JOIN products p ON oi.product_id = p.product_id
                        WHERE o.status = 'pending'
                        GROUP BY o.order_id
                        ORDER BY o.created_at ASC";
                        
                        $orders_result = mysqli_query($dbc, $orders_query);
                        
                        if (!$orders_result || mysqli_num_rows($orders_result) == 0) {
                            echo '<div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle"></i> No pending orders.
                            </div>';
                        } else {
                            while ($order = mysqli_fetch_assoc($orders_result)) {
                                $created = new DateTime($order['created_at']);
                                echo '<div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-1">Order #' . $order['order_id'] . '</h6>
                                        <small class="text-muted">' . 
                                            $created->format('M j, g:i A') . 
                                        '</small>
                                    </div>
                                    <p class="mb-1 small">
                                        <strong>Items:</strong> ' . 
                                        htmlspecialchars($order['items']) . 
                                    '</p>
                                </div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- First include jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<!-- Then include Bootstrap JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    // Handle ingredient checkbox changes
    $('.ingredient-checkbox').change(function() {
        const checkbox = $(this);
        const ingredientId = checkbox.data('ingredient-id');
        const orderIds = checkbox.data('order-ids');
        const isChecked = checkbox.prop('checked');
        
        // Disable checkbox while processing
        checkbox.prop('disabled', true);
        
        $.ajax({
            url: 'update_consolidated_checklist.php',  // Remove 'modules/' from the path
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
                            // Reload page if no more ingredients in station
                            if ($('.ingredient-item').length === 0) {
                                location.reload();
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
            error: function(xhr, status, error) {
                console.error('Ajax error:', error);
                alert('Failed to update ingredient status');
                checkbox.prop('checked', !isChecked);
                checkbox.prop('disabled', false);
            }
        });
    });
    
    // Auto refresh every 2 minutes
    setInterval(function() {
        location.reload();
    }, 120000);
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