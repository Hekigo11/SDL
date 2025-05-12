<?php
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

include("dbconi.php");
?>

<style>
    .order-actions {
        min-width: 150px;
    }
    .order-status {
        min-width: 200px;
    }
    .status-info {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .status-time {
        font-size: 0.813rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    .status-time i {
        width: 16px;
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    tr.cancelled {
        background-color: rgba(220, 53, 69, 0.05);
    }
    tr.completed {
        background-color: rgba(40, 167, 69, 0.05);
    }
    .text-danger .fa-times-circle {
        color: #dc3545;
    }
    .text-success .fa-check-circle {
        color: #28a745;
    }
    .text-info .fa-truck {
        color: #17a2b8;
    }
    .checklist-container {
        font-size: 0.9rem;
    }
    .station-group {
        background: #fff;
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 15px;
    }
    .station-group:last-child {
        margin-bottom: 0;
    }
    .checklist-item {
        padding: 8px;
        border-radius: 4px;
        background: #fff;
        transition: all 0.2s ease;
    }
    .checklist-item:hover {
        background: #f8f9fa;
    }
    .font-weight-medium {
        font-weight: 500;
    }
    /* Custom scrollbar for webkit browsers */
    #ingredientsChecklist::-webkit-scrollbar {
        width: 6px;
    }
    #ingredientsChecklist::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    #ingredientsChecklist::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 3px;
    }
    #ingredientsChecklist::-webkit-scrollbar-thumb:hover {
        background: #999;
    }
    /* For Firefox */
    #ingredientsChecklist {
        scrollbar-width: thin;
        scrollbar-color: #ccc #f1f1f1;
    }
    .nav-tabs {
        border-bottom: 2px solid #eee;
    }
    .nav-tabs .nav-link {
        border: none;
        color: #666;
        font-weight: 500;
        padding: 1rem 1.5rem;
        position: relative;
        transition: color 0.2s;
    }
    .nav-tabs .nav-link:hover {
        color: var(--accent);
        border: none;
    }
    .nav-tabs .nav-link.active {
        color: var(--accent);
        border: none;
        background: none;
    }
    .nav-tabs .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background-color: var(--accent);
    }
    .tab-content {
        padding-top: 1.5rem;
    }
    .card {
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        border-radius: 15px;
        border: none;
    }
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #eee;
    }
    .table thead th {
        border-top: none;
        border-bottom-width: 1px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.875rem;
        color: #666;
    }
    .table td {
        vertical-align: middle;
    }
    .badge {
        padding: 0.5em 0.75em;
        font-weight: 500;
    }
    .btn-group > .btn {
        border-radius: 4px;
        margin: 0 2px;
    }
    .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 0 30px rgba(0,0,0,0.2);
    }
    .modal-header {
        border-radius: 15px 15px 0 0;
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark, #007bff) 100%);
        color: white;
    }
    .modal-header .close {
        color: white;
        text-shadow: none;
        opacity: 0.8;
    }
    .modal-header .close:hover {
        opacity: 1;
    }
</style>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Manage Orders</h2>
        </div>
    </div>    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" id="delivery-tab" data-toggle="tab" href="#delivery">Delivery Orders</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="catering-tab" data-toggle="tab" href="#catering">Standard Catering</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="custom-catering-tab" data-toggle="tab" href="#custom-catering">Custom Catering</a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Delivery Orders Tab -->
        <div class="tab-pane fade show active" id="delivery">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total Amount</th>
                                    <th style="min-width: 200px;">Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $delivery_query = "SELECT o.*, 
                                         GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.prod_name) SEPARATOR ', ') as items,
                                         u.email_add as customer_email, u.fname, u.lname, u.mobile_num as phone,
                                         CASE 
                                             WHEN o.scheduled_delivery IS NOT NULL 
                                             THEN DATE_FORMAT(o.scheduled_delivery, '%M %e, %l:%i %p') 
                                             ELSE NULL 
                                         END as formatted_delivery_time
                                         FROM orders o
                                         JOIN order_items oi ON o.order_id = oi.order_id
                                         JOIN products p ON oi.product_id = p.product_id
                                         JOIN users u ON o.user_id = u.user_id
                                         GROUP BY o.order_id
                                         ORDER BY o.created_at DESC";
                                $delivery_result = mysqli_query($dbc, $delivery_query);
                                while ($row = mysqli_fetch_assoc($delivery_result)) {
                                    $statusClass = getStatusClass($row['status']);
                                    $statusDisplay = getStatusDisplay($row['status']);
                                ?>
                                <tr class="<?php echo $row['status'] === 'cancelled' ? 'cancelled' : ($row['status'] === 'completed' ? 'completed' : ''); ?>">
                                    <td>#<?php echo $row['order_id']; ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?><br>
                                        <small class="text-muted"><?php echo $row['phone']; ?></small>
                                    </td>
                                    <td><?php echo $row['items']; ?></td>
                                    <td>₱<?php echo number_format($row['total_amount'], 2); ?></td>
                                    <td class="order-status">
                                        <div class="status-info">
                                            <div class="status-badge">
                                                <span class="badge badge-<?php echo $statusClass; ?>"><?php echo $statusDisplay; ?></span>
                                            </div>
                                            <?php if ($row['status'] === 'delivering' && !empty($row['delivery_started_at'])): ?>
                                                <div class="status-time text-info">
                                                    <i class="fas fa-truck"></i> Out for delivery since <?php echo date('g:i A', strtotime($row['delivery_started_at'])); ?>
                                                </div>
                                            <?php elseif ($row['status'] === 'completed' && !empty($row['delivered_at'])): ?>
                                                <div class="status-time text-success">
                                                    <i class="fas fa-check-circle"></i> Delivered on <?php echo date('M j, g:i A', strtotime($row['delivered_at'])); ?>
                                                </div>
                                            <?php elseif ($row['status'] === 'cancelled' && !empty($row['cancelled_at'])): ?>
                                                <div class="status-time text-danger">
                                                    <i class="fas fa-times-circle"></i> Cancelled on <?php echo date('M j, g:i A', strtotime($row['cancelled_at'])); ?>
                                                    <?php if (!empty($row['cancellation_reason'])): ?>
                                                        <div class="text-muted small mt-1">
                                                            <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($row['cancellation_reason']); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($row['scheduled_delivery'])): ?>
                                                <div class="status-time text-info">
                                                    <i class="fas fa-clock"></i> Scheduled for: <?php echo $row['formatted_delivery_time']; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="order-actions">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-info view-order" 
                                                    data-id="<?php echo $row['order_id']; ?>"
                                                    data-type="delivery"
                                                    data-items="<?php echo htmlspecialchars($row['items'] ?? ''); ?>"
                                                    data-notes="<?php echo htmlspecialchars($row['notes'] ?? ''); ?>"
                                                    data-scheduled-delivery="<?php echo $row['scheduled_delivery'] ?? ''; ?>"
                                                    data-payment-method="<?php echo htmlspecialchars($row['payment_method'] ?? ''); ?>"
                                                    data-delivery-address="<?php echo htmlspecialchars($row['address'] ?? ''); ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($row['status'] !== 'completed' && $row['status'] !== 'cancelled'): ?>
                                            <button class="btn btn-sm btn-primary update-status"
                                                    data-id="<?php echo $row['order_id']; ?>"
                                                    data-type="delivery"
                                                    data-current-status="<?php echo $row['status']; ?>"
                                                    data-current-notes="<?php echo htmlspecialchars($row['notes'] ?? ''); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catering Orders Tab -->
        <div class="tab-pane fade" id="catering">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Event Date</th>
                                    <th>Customer</th>
                                    <th>Package</th>
                                    <th>Persons</th>
                                    <th>Amount</th>
                                    <th style="min-width: 200px;">Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Query both standard and custom catering orders
                                $standard_query = "SELECT co.*, 'standard' AS order_type,
                                    GROUP_CONCAT(DISTINCT CONCAT(c.category_name, ': ', p.prod_name)) as selected_items,
                                    u.fname, u.lname, u.mobile_num as phone, u.email_add
                                    FROM catering_orders co
                                    LEFT JOIN catering_order_menu_items comi ON co.catering_id = comi.catering_order_id
                                    LEFT JOIN products p ON comi.product_id = p.product_id
                                    LEFT JOIN categories c ON p.prod_cat_id = c.category_id
                                    JOIN users u ON co.user_id = u.user_id
                                    GROUP BY co.catering_id
                                    ORDER BY co.created_at DESC";
                                
                                $custom_query = "SELECT cco.*, 'custom' AS order_type,
                                    GROUP_CONCAT(DISTINCT CONCAT(c.category_name, ': ', p.prod_name)) as selected_items,
                                    u.fname, u.lname, u.mobile_num as phone, u.email_add
                                    FROM custom_catering_orders cco
                                    LEFT JOIN cust_catering_order_items ccoi ON cco.custom_order_id = ccoi.custom_order_id
                                    LEFT JOIN products p ON ccoi.product_id = p.product_id
                                    LEFT JOIN categories c ON ccoi.category_id = c.category_id
                                    JOIN users u ON cco.user_id = u.user_id
                                    GROUP BY cco.custom_order_id
                                    ORDER BY cco.created_at DESC";

                                $standard_result = mysqli_query($dbc, $standard_query);
                                $custom_result = mysqli_query($dbc, $custom_query);

                                // Function to render catering order row
                                function renderCateringRow($row, $order_type) {
                                    $statusClass = getStatusClass($row['status']);
                                    $orderId = $order_type === 'standard' ? $row['catering_id'] : $row['custom_order_id'];
                                    $orderPrefix = $order_type === 'standard' ? 'CTR-' : 'CSP-';
                                    ?>
                                    <tr>
                                        <td><?php echo $orderPrefix . $orderId; ?></td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($row['event_date'])); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?><br>
                                            <small class="text-muted"><?php echo $row['phone']; ?></small>
                                        </td>
                                        <td>
                                            <?php 
                                            if ($order_type === 'standard') {
                                                echo htmlspecialchars($row['menu_package']);
                                            } else {
                                                echo htmlspecialchars($row['menu_preferences'] ?: 'Custom Package');
                                                if (isset($row['num_persons']) && $row['num_persons'] < 50) {
                                                    echo ' <span class="badge badge-info">Small Group</span>';
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $row['num_persons']; ?></td>
                                        <td>
                                            <?php if (!empty($row['quote_amount'])): ?>
                                                ₱<?php echo number_format($row['quote_amount'], 2); ?>
                                            <?php elseif (!empty($row['total_amount'])): ?>
                                                ₱<?php echo number_format($row['total_amount'], 2); ?>
                                            <?php else: ?>
                                                <em>To be quoted</em>
                                            <?php endif; ?>
                                        </td>
                                        <td class="order-status">
                                            <div class="status-info">
                                                <div class="status-badge">
                                                    <span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($row['status']); ?></span>
                                                </div>
                                                <?php if (!empty($row['staff_notes'])): ?>
                                                    <div class="text-muted small mt-1">
                                                        <i class="fas fa-comment"></i> <?php echo htmlspecialchars($row['staff_notes']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="order-actions">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-info view-order" 
                                                        data-id="<?php echo $orderId; ?>"
                                                        data-type="<?php echo $order_type; ?>"
                                                        data-items="<?php echo htmlspecialchars($row['selected_items'] ?? ''); ?>"
                                                        data-notes="<?php echo htmlspecialchars($row['special_requests'] ?? ''); ?>"
                                                        data-event-date="<?php echo $row['event_date']; ?>"
                                                        data-venue="<?php echo htmlspecialchars($row['venue']); ?>"
                                                        data-persons="<?php echo $row['num_persons']; ?>"
                                                        data-package="<?php echo htmlspecialchars($order_type === 'standard' ? $row['menu_package'] : ($row['menu_preferences'] ?: 'Custom Package')); ?>"
                                                        data-amount="<?php echo !empty($row['quote_amount']) ? $row['quote_amount'] : ($row['total_amount'] ?? ''); ?>"
                                                        data-services="<?php 
                                                            $services = [];
                                                            if ($row['needs_setup']) $services[] = 'setup';
                                                            if ($row['needs_tablesandchairs']) $services[] = 'tables';
                                                            if ($row['needs_decoration']) $services[] = 'decoration';
                                                            echo implode(',', $services); 
                                                        ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($row['status'] !== 'completed' && $row['status'] !== 'cancelled'): ?>
                                                <button class="btn btn-sm btn-primary update-status"
                                                        data-id="<?php echo $orderId; ?>"
                                                        data-type="<?php echo $order_type; ?>"
                                                        data-current-status="<?php echo $row['status']; ?>"
                                                        data-current-notes="<?php echo htmlspecialchars($row['staff_notes'] ?? ''); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }

                                // Helper function for status class
                                function getStatusClass($status) {
                                    switch($status) {
                                        case 'pending': return 'warning';
                                        case 'confirmed': return 'info';
                                        case 'processing':
                                        case 'in_kitchen': return 'info';
                                        case 'ready_for_delivery': return 'primary';
                                        case 'delivering': return 'info';
                                        case 'completed': return 'success';
                                        case 'cancelled': return 'danger';
                                        default: return 'secondary';
                                    }
                                }

                                // Helper function for status display
                                function getStatusDisplay($status) {
                                    return ucwords(str_replace('_', ' ', $status));
                                }

                                // Display standard catering orders
                                while ($row = mysqli_fetch_assoc($standard_result)) {
                                    renderCateringRow($row, 'standard');
                                }

                                // Display custom catering orders
                                while ($row = mysqli_fetch_assoc($custom_result)) {
                                    renderCateringRow($row, 'custom');
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>        </div>

        <!-- Custom Catering Orders Tab -->
        <div class="tab-pane fade" id="custom-catering">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Event Date</th>
                                    <th>Customer</th>
                                    <th>Package</th>
                                    <th>Persons</th>
                                    <th>Amount</th>
                                    <th style="min-width: 200px;">Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Query custom catering orders
                                $custom_query = "SELECT cco.*, 
                                    u.email_add as customer_email, u.fname, u.lname, u.mobile_num as phone,
                                    GROUP_CONCAT(DISTINCT CONCAT(c.category_name, ': ', p.prod_name)) as selected_items
                                    FROM custom_catering_orders cco
                                    LEFT JOIN cust_catering_order_items ccoi ON cco.custom_order_id = ccoi.custom_order_id
                                    LEFT JOIN products p ON ccoi.product_id = p.product_id
                                    LEFT JOIN categories c ON ccoi.category_id = c.category_id
                                    JOIN users u ON cco.user_id = u.user_id
                                    GROUP BY cco.custom_order_id
                                    ORDER BY cco.created_at DESC";

                                $custom_result = mysqli_query($dbc, $custom_query);
                                while ($row = mysqli_fetch_assoc($custom_result)) {
                                    $statusClass = getStatusClass($row['status']);
                                    $isCustomPackage = ($row['menu_preferences'] == 'Custom Package');
                                    $isSmallGroup = ($row['num_persons'] < 50);
                                    
                                    // Determine request type label
                                    if ($isCustomPackage && $isSmallGroup) {
                                        $requestTypeLabel = 'Custom Menu (Small Group)';
                                    } elseif ($isCustomPackage) {
                                        $requestTypeLabel = 'Custom Menu';
                                    } elseif ($isSmallGroup) {
                                        $requestTypeLabel = 'Small Group';
                                    } else {
                                        $requestTypeLabel = 'Special Request';
                                    }
                                ?>
                                <tr>
                                    <td>CSP-<?php echo $row['custom_order_id']; ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($row['event_date'])); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?><br>
                                        <small class="text-muted"><?php echo $row['phone']; ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($row['menu_preferences'] ?: 'Not specified'); ?>
                                        <span class="badge badge-info"><?php echo $requestTypeLabel; ?></span>
                                    </td>
                                    <td><?php echo $row['num_persons']; ?></td>
                                    <td>
                                        <?php if (!empty($row['quote_amount'])): ?>
                                            ₱<?php echo number_format($row['quote_amount'], 2); ?>
                                        <?php else: ?>
                                            <em>To be quoted</em>
                                        <?php endif; ?>
                                    </td>
                                    <td class="order-status">
                                        <div class="status-info">
                                            <div class="status-badge">
                                                <span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($row['status']); ?></span>
                                            </div>
                                            <?php if (!empty($row['staff_notes'])): ?>
                                                <div class="text-muted small mt-1">
                                                    <i class="fas fa-comment"></i> <?php echo htmlspecialchars($row['staff_notes']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="order-actions">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-info view-order" 
                                                    data-id="<?php echo $row['custom_order_id']; ?>"
                                                    data-type="custom"
                                                    data-items="<?php echo htmlspecialchars($row['selected_items'] ?? ''); ?>"
                                                    data-notes="<?php echo htmlspecialchars($row['special_requests'] ?? ''); ?>"
                                                    data-event-date="<?php echo $row['event_date']; ?>"
                                                    data-venue="<?php echo htmlspecialchars($row['venue']); ?>"
                                                    data-persons="<?php echo $row['num_persons']; ?>"
                                                    data-package="<?php echo htmlspecialchars($row['menu_preferences'] ?: 'Custom Package'); ?>"
                                                    data-amount="<?php echo $row['quote_amount'] ?? ''; ?>"
                                                    data-services="<?php 
                                                        $services = [];
                                                        if ($row['needs_setup']) $services[] = 'setup';
                                                        if ($row['needs_tablesandchairs']) $services[] = 'tables';
                                                        if ($row['needs_decoration']) $services[] = 'decoration';
                                                        echo implode(',', $services); 
                                                    ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($row['status'] !== 'completed' && $row['status'] !== 'cancelled'): ?>
                                            <button class="btn btn-sm btn-primary update-status"
                                                    data-id="<?php echo $row['custom_order_id']; ?>"
                                                    data-type="custom"
                                                    data-current-status="<?php echo $row['status']; ?>"
                                                    data-current-notes="<?php echo htmlspecialchars($row['staff_notes'] ?? ''); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Order Details Modal -->
    <div class="modal fade" id="deliveryOrderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delivery Order Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="row no-gutters">
                        <!-- Left side: Order details -->
                        <div class="col-md-7 p-3 border-right">
                            <h6>Order Items</h6>
                            <div id="orderItems" class="mb-4"></div>
                            
                            <h6>Delivery Details</h6>
                            <div id="deliveryTime" class="mb-3"></div>
                            
                            <h6>Delivery Address</h6>
                            <p id="deliveryAddress" class="text-muted"></p>
                            
                            <h6>Payment Method</h6>
                            <p id="paymentMethod" class="text-muted mb-3"></p>
                            
                            <h6>Special Notes</h6>
                            <p id="orderNotes" class="text-muted"></p>
                        </div>
                        
                        <!-- Right side: Ingredients checklist -->
                        <div class="col-md-5 bg-light">
                            <div class="sticky-top">
                                <div class="p-3 border-bottom bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-clipboard-check"></i>
                                        Ingredients Checklist
                                    </h6>
                                </div>
                                <div id="ingredientsChecklist" class="p-3" style="max-height: 60vh; overflow-y: auto;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Catering Order Details Modal -->
    <div class="modal fade" id="cateringOrderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Catering Order Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Event Details</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Order ID:</th>
                                    <td id="catering-order-id"></td>
                                </tr>
                                <tr>
                                    <th>Event Date:</th>
                                    <td id="catering-event-date"></td>
                                </tr>
                                <tr>
                                    <th>Venue:</th>
                                    <td id="catering-venue"></td>
                                </tr>
                                <tr>
                                    <th>Number of Persons:</th>
                                    <td id="catering-persons"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Package Details</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Package Type:</th>
                                    <td id="catering-package"></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td id="catering-status"></td>
                                </tr>
                                <tr>
                                    <th>Amount:</th>
                                    <td id="catering-amount"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Selected Items</h6>
                            <div id="catering-menu-items" class="table-responsive" style="max-height:220px; overflow-y:auto; border:1px solid #eee; border-radius:6px;"></div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Additional Services</h6>
                            <div id="catering-services"></div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Special Requests</h6>
                            <p id="catering-special-requests" class="text-muted"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Order Status</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="updateStatusForm">
                        <input type="hidden" name="order_id" id="updateOrderId">
                        <input type="hidden" name="order_type" id="updateOrderType">
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="status" id="orderStatus" required>
                                <!-- Options will be populated dynamically -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control" name="status_notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveStatus">Update Status</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // View Order Details
        $('.view-order').click(function() {
            const orderType = $(this).data('type');
            
            if (orderType === 'delivery') {
                // Handle delivery order details
                const orderId = $(this).data('id');
                const items = $(this).data('items');
                const notes = $(this).data('notes');
                const scheduledDelivery = $(this).data('scheduled-delivery');
                const paymentMethod = $(this).data('payment-method');
                const deliveryAddress = $(this).data('delivery-address');
                
                // Display order items
                $('#orderItems').html(items.split(', ').map(item => `<div class="mb-2">${item}</div>`).join(''));
                
                // Load and display ingredients checklist
                $.get('admin_get_checklist.php', { order_id: orderId })
                    .done(function(response) {
                        $('#ingredientsChecklist').html(response);
                        // Disable all checkboxes after loading
                        $('#ingredientsChecklist input[type="checkbox"]').prop('disabled', true);
                    })
                    .fail(function() {
                        $('#ingredientsChecklist').html('<div class="alert alert-danger">Failed to load ingredients checklist</div>');
                    });
                
                // Display notes
                $('#orderNotes').text(notes || 'No special notes');
                
                // Display scheduled delivery time if exists
                if (scheduledDelivery) {
                    const deliveryDate = new Date(scheduledDelivery);
                    const formattedDate = deliveryDate.toLocaleString('en-US', { 
                        month: 'long',
                        day: 'numeric',
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });
                    $('#deliveryTime').html(`
                        <div class="alert alert-info">
                            <i class="fas fa-clock"></i> 
                            Scheduled for: ${formattedDate}
                        </div>
                    `);
                } else {
                    $('#deliveryTime').html('<p class="text-muted">No scheduled delivery time</p>');
                }
                
                // Display delivery address and payment method
                $('#deliveryAddress').text(deliveryAddress || 'No delivery address provided');
                $('#paymentMethod').text(paymentMethod || 'No payment method specified');
                
                $('#deliveryOrderModal').modal('show');
            } else {
                // Handle catering order details
                const orderId = $(this).data('id');
                const orderPrefix = orderType === 'standard' ? 'CTR-' : 'CSP-';
                const items = $(this).data('items');
                const notes = $(this).data('notes');
                const eventDate = new Date($(this).data('event-date'));
                const venue = $(this).data('venue');
                const persons = $(this).data('persons');
                const package = $(this).data('package');
                const amount = $(this).data('amount');
                const services = $(this).data('services') ? $(this).data('services').split(',') : [];
                
                // Set basic details
                $('#catering-order-id').text(`${orderPrefix}${orderId}`);
                $('#catering-event-date').text(eventDate.toLocaleString());
                $('#catering-venue').text(venue);
                $('#catering-persons').text(persons);
                $('#catering-package').text(package);
                
                // Set amount
                $('#catering-amount').text(amount ? `₱${parseFloat(amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : 'To be quoted');
                
                // Display selected menu items
                if (items) {
                    const menuItems = items.split(',');
                    $('#catering-menu-items').html(`
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${menuItems.map(item => `
                                    <tr>
                                        <td><i class="fas fa-utensils text-accent mr-2"></i>${item.trim()}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `);
                } else {
                    $('#catering-menu-items').html('<p class="text-muted">No menu items selected yet</p>');
                }

                // Set additional services
                const servicesList = [];
                if (services.includes('setup')) servicesList.push('Buffet Setup (₱2,000)');
                if (services.includes('tables')) servicesList.push('Tables and Chairs (₱3,500)');
                if (services.includes('decoration')) servicesList.push('Venue Decoration (₱5,000)');
                
                $('#catering-services').html(servicesList.length ? 
                    servicesList.map(service => `<div><i class="fas fa-check text-success"></i> ${service}</div>`).join('') :
                    '<p class="text-muted">No additional services selected</p>'
                );

                // Set special requests
                $('#catering-special-requests').text(notes || 'No special requests');
                
                $('#cateringOrderModal').modal('show');
            }
        });

        // Update Order Status
        $('.update-status').click(function() {
            const orderId = $(this).data('id');
            const orderType = $(this).data('type');
            const currentStatus = $(this).data('current-status');
            const currentNotes = $(this).data('current-notes');
            
            $('#updateOrderId').val(orderId);
            $('#updateOrderType').val(orderType);
            
            // Clear and populate status options based on order type
            const $statusSelect = $('#orderStatus').empty();
            
            if (orderType === 'delivery') {
                const deliveryStatuses = ['pending', 'processing', 'in_kitchen', 'ready_for_delivery', 'delivering', 'completed', 'cancelled'];
                deliveryStatuses.forEach(status => {
                    const displayStatus = status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    $statusSelect.append(`<option value="${status}"${currentStatus === status ? ' selected' : ''}>${displayStatus}</option>`);
                });
            } else {
                const cateringStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
                cateringStatuses.forEach(status => {
                    const displayStatus = status.charAt(0).toUpperCase() + status.slice(1);
                    $statusSelect.append(`<option value="${status}"${currentStatus === status ? ' selected' : ''}>${displayStatus}</option>`);
                });
            }
            
            $('textarea[name="status_notes"]').val(currentNotes);
            $('#updateStatusModal').modal('show');
        });

        // Save Status Update
        $('#saveStatus').click(function() {
            const btn = $(this);
            const form = $('#updateStatusForm');
            const orderId = $('#updateOrderId').val();
            const orderType = $('#updateOrderType').val();
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            
            // Determine the update endpoint based on order type
            const updateEndpoint = orderType === 'delivery' ? 'admin_update_order.php' : 'admin_update_catering_order.php';
            
            $.post(updateEndpoint, form.serialize())
                .done(function(response) {
                    if (response === 'success') {
                        location.reload();
                    } else if (response === 'No changes to update') {
                        alert('No changes were made');
                        $('#updateStatusModal').modal('hide');
                    } else {
                        alert('Error: ' + response);
                    }
                })
                .fail(function() {
                    alert('Update failed. Please try again.');
                })
                .always(function() {
                    btn.prop('disabled', false).text('Update Status');
                });
        });
    });
    </script>
</div>
