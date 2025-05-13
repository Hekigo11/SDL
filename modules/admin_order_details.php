<?php
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

include("dbconi.php");

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
        font-weight: bold;
        color: var(--accent);
        transition: color 0.2s, background 0.2s, box-shadow 0.2s;
    }
    .nav-tabs .nav-link:hover {
        color: var(--accent);
        background: #e9ecef;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        border-bottom: 2.5px var(--accent);
        text-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }
    .nav-tabs .nav-link.active {
        font-weight: bold;
        color:var(--accent);
        background: #e9ecef;
        border-bottom: 3px solid var(--accent);
        box-shadow: 0 4px 12px rgba(0,123,255,0.08);
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
    .menu-items-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
        padding: 1rem;
    }
    .menu-item {
        transition: all 0.3s ease;
    }
    .menu-item.selected {
        border-color: var(--primary);
        background-color: rgba(0,123,255,0.05);
    }
    .selected-items-summary {
        max-height: 200px;
        overflow-y: auto;
    }
    .remove-item {
        padding: 0.25rem 0.5rem;
    }
    .remove-item i {
        font-size: 0.875rem;
    }
    /* Menu Items Tab Styling */
    #menuItemsContainer .nav-pills .nav-link {
        color: #333;
        background-color: #f0f0f0;
        margin-right: 5px;
        border-radius: 4px;
        transition: all 0.3s ease;
        font-weight: 500;
    }
    #menuItemsContainer .nav-pills .nav-link:hover,
    #menuItemsContainer .nav-pills .nav-link.active {
        color: #fff;
        background-color: var(--accent, #0275d8);
    }
    #menuItemsContainer .menu-item {
        transition: all 0.2s ease;
    }
    #menuItemsContainer .menu-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    #menuItemsContainer .menu-item.selected {
        box-shadow: 0 2px 8px rgba(0,123,255,0.25);
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
                                // Query standard catering orders only for this tab
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

                                $standard_result = mysqli_query($dbc, $standard_query);

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
                                            <?php echo htmlspecialchars($row['menu_package']); ?>
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
                                                        data-package="<?php echo htmlspecialchars($row['menu_package']); ?>"
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
                                                <?php if ($order_type === 'standard'): ?>
                                                <button class="btn btn-sm btn-primary edit-standard-order"
                                                        data-id="<?php echo $orderId; ?>"
                                                        data-status="<?php echo $row['status']; ?>"
                                                        data-current-notes="<?php echo htmlspecialchars($row['staff_notes'] ?? ''); ?>"
                                                        data-package="<?php echo htmlspecialchars($row['menu_package'] ?? ''); ?>"
                                                        data-persons="<?php echo htmlspecialchars($row['num_persons'] ?? 0); ?>"
                                                        data-event-date="<?php echo htmlspecialchars($row['event_date'] ?? ''); ?>"
                                                        data-venue="<?php echo htmlspecialchars($row['venue'] ?? ''); ?>"
                                                        data-notes="<?php echo htmlspecialchars($row['special_requests'] ?? ''); ?>"
                                                        data-services="<?php
                                                            $services = [];
                                                            if ($row['needs_setup'] ?? 0) $services[] = 'setup';
                                                            if ($row['needs_tablesandchairs'] ?? 0) $services[] = 'tables';
                                                            if ($row['needs_decoration'] ?? 0) $services[] = 'decoration';
                                                            echo implode(',', $services);
                                                        ?>">
                                                    <i class="fas fa-edit"></i> Edit Order
                                                </button>
                                                <?php else: ?>
                                                <button class="btn btn-sm btn-primary edit-custom-order"
                                                        data-id="<?php echo $row['custom_order_id']; ?>"
                                                        data-preferences="<?php echo htmlspecialchars($row['menu_preferences'] ?? ''); ?>"
                                                        data-persons="<?php echo htmlspecialchars($row['num_persons'] ?? 0); ?>"
                                                        data-amount="<?php echo htmlspecialchars($row['quote_amount'] ?? 0); ?>"
                                                        data-status="<?php echo $row['status']; ?>"
                                                        data-current-notes="<?php echo htmlspecialchars($row['staff_notes'] ?? ''); ?>"
                                                        data-event-date="<?php echo htmlspecialchars($row['event_date']); ?>"
                                                        data-venue="<?php echo htmlspecialchars($row['venue']); ?>"
                                                        data-items="<?php echo htmlspecialchars($row['selected_items'] ?? ''); ?>"
                                                        data-special-requests="<?php echo htmlspecialchars($row['special_requests'] ?? ''); ?>"
                                                        data-services="<?php 
                                                            $services = [];
                                                            if ($row['needs_setup']) $services[] = 'setup';
                                                            if ($row['needs_tablesandchairs']) $services[] = 'tables';
                                                            if ($row['needs_decoration']) $services[] = 'decoration';
                                                            echo implode(',', $services); 
                                                        ?>">
                                                    <i class="fas fa-edit"></i> Edit Order
                                                </button>
                                                <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }

                                // Display standard catering orders only
                                while ($row = mysqli_fetch_assoc($standard_result)) {
                                    renderCateringRow($row, 'standard');
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
                                        <?php
                                        if (!empty($row['quote_amount'])) {
                                            $total = $row['quote_amount'];
                                            // Add costs for additional services
                                            if ($row['needs_setup'] == 1) $total += 2000;
                                            if ($row['needs_tablesandchairs'] == 1) $total += 3500;
                                            if ($row['needs_decoration'] == 1) $total += 5000;
                                            echo '₱' . number_format($total, 2);
                                        } else {
                                            echo '<em>To be quoted</em>';
                                        }
                                        ?>
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
                                                <div class="btn-group">
                                                <button class="btn btn-sm btn-primary edit-custom-order"
                                                    data-id="<?php echo $row['custom_order_id']; ?>"
                                                    data-preferences="<?php echo htmlspecialchars($row['menu_preferences'] ?? ''); ?>"
                                                    data-persons="<?php echo htmlspecialchars($row['num_persons'] ?? 0); ?>"
                                                    data-amount="<?php echo htmlspecialchars($row['quote_amount'] ?? 0); ?>"
                                                    data-status="<?php echo $row['status']; ?>"
                                                    data-current-notes="<?php echo htmlspecialchars($row['staff_notes'] ?? ''); ?>"
                                                    data-event-date="<?php echo htmlspecialchars($row['event_date']); ?>"
                                                    data-venue="<?php echo htmlspecialchars($row['venue']); ?>"
                                                    data-items="<?php echo htmlspecialchars($row['selected_items'] ?? ''); ?>"
                                                    data-special-requests="<?php echo htmlspecialchars($row['special_requests'] ?? ''); ?>"
                                                    data-services="<?php 
                                                    $services = [];
                                                    if ($row['needs_setup']) $services[] = 'setup';
                                                    if ($row['needs_tablesandchairs']) $services[] = 'tables';
                                                    if ($row['needs_decoration']) $services[] = 'decoration';
                                                    echo implode(',', $services); 
                                                    ?>">
                                                    <i class="fas fa-edit"></i> Edit Order
                                                </button>
                                                <button class="btn btn-sm btn-info update-status"
                                                        data-id="<?php echo $row['custom_order_id']; ?>"
                                                        data-type="custom"
                                                            data-current-status="<?php echo $row['status']; ?>"
                                                            data-current-notes="<?php echo htmlspecialchars($row['staff_notes'] ?? ''); ?>">
                                                        <i class="fas fa-sync-alt"></i> Update Status
                                                    </button>
                                                </div>
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

    <!-- Edit Custom Catering Order Modal -->
    <div class="modal fade" id="editCustomOrderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Custom Catering Order</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="details-tab" data-toggle="tab" href="#detailsTab" role="tab">Order Details</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="menu-tab" data-toggle="tab" href="#menuTab" role="tab">Menu Items</a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Details Tab -->
                        <div class="tab-pane fade show active" id="detailsTab" role="tabpanel">
                            <form id="editCustomOrderForm">
                                <input type="hidden" name="custom_order_id" id="editOrderId">
                                
                                <div class="form-group">
                                    <label>Menu Preferences</label>
                                    <input type="text" class="form-control" name="menu_preferences" id="editMenuPreferences">
                                </div>

                                <div class="form-group">
                                    <label>Number of Persons</label>
                                    <input type="number" class="form-control" name="num_persons" id="editNumPersons" min="1">
                                </div>

                                <div class="form-group">
                                    <label>Quote Amount</label>
                                    <input type="number" step="0.01" class="form-control" name="quote_amount" id="editQuoteAmount">
                                </div>

                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" name="status" id="editOrderStatus">
                                        <option value="pending">Pending</option>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Staff Notes</label>
                                    <textarea class="form-control" name="staff_notes" id="editStaffNotes" rows="3"></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Additional Services</label>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="setup" name="needs_setup">
                                        <label class="custom-control-label" for="setup">Buffet Setup (₱2,000)</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="tables" name="needs_tablesandchairs">
                                        <label class="custom-control-label" for="tables">Tables and Chairs (₱3,500)</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="decoration" name="needs_decoration">
                                        <label class="custom-control-label" for="decoration">Venue Decoration (₱5,000)</label>
                                    </div>
                                </div>

                                <div class="total-cost mt-3 p-3 bg-light rounded">
                                    <h6>Total Cost Breakdown:</h6>
                                    <div id="costBreakdown"></div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <strong>Total Amount:</strong>
                                        <strong id="totalAmount">₱0.00</strong>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Menu Items Tab -->
                        <div class="tab-pane fade" id="menuTab" role="tabpanel">
                            <div id="menuItemsContainer">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="menuHalalOnly">
                                        <label class="custom-control-label" for="menuHalalOnly">Show Halal Items Only</label>
                                    </div>
                                    <div class="form-group mb-0">
                                        <input type="text" class="form-control" id="menuSearchBox" placeholder="Search menu items...">
                                    </div>
                                </div>
                                
                                <div id="categoryLoadingStatus" class="alert alert-info mb-3">Loading menu categories...</div>
                                
                                <nav>
                                    <div class="nav nav-pills mb-3" id="menuCategories" role="tablist">
                                        <!-- Categories will be loaded dynamically -->
                                    </div>
                                </nav>

                                <div class="tab-content mt-3" id="menuItemContent">
                                    <!-- Menu content will be loaded dynamically -->
                                </div>

                                <hr class="my-4">

                                <h5 class="mb-3">Selected Items:</h5>
                                <div id="selectedItemsList" class="list-group mb-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveCustomOrder">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Standard Catering Order Modal -->
    <div class="modal fade" id="editStandardOrderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Standard Catering Order</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editStandardOrderForm">
                        <input type="hidden" name="order_id" id="editStandardOrderId">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Package Type</label>
                                    <select class="form-control" name="menu_package" id="editStandardPackage" required>
                                        <?php
                                        // Query packages from the database
                                        $package_query = "SELECT name, base_price FROM packages WHERE is_active = 1 ORDER BY base_price ASC";
                                        $package_result = mysqli_query($dbc, $package_query);
                                        
                                        if ($package_result) {
                                            while ($package = mysqli_fetch_assoc($package_result)) {
                                                echo '<option value="' . htmlspecialchars($package['name']) . '">' 
                                                    . htmlspecialchars($package['name']) . ' (₱' . number_format($package['base_price'], 2) . ' per person)</option>';
                                            }
                                        } else {
                                            // Fallback options if query fails
                                            echo '<option value="Basic Filipino Package">Basic Filipino Package (₱250.00 per person)</option>';
                                            echo '<option value="Premium Filipino Package">Premium Filipino Package (₱450.00 per person)</option>';
                                            echo '<option value="Executive Package">Executive Package (₱650.00 per person)</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Number of Persons</label>
                                    <input type="number" class="form-control" name="num_persons" id="editStandardPersons" min="20" required>
                                    <small class="text-muted">Minimum 20 persons required for standard packages</small>
                                </div>
                                
                                <div class="form-group">
                                    <label>Event Date and Time</label>
                                    <input type="datetime-local" class="form-control" name="event_date" id="editStandardEventDate" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Venue</label>
                                    <input type="text" class="form-control" name="venue" id="editStandardVenue" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" name="status" id="editStandardStatus">
                                        <option value="pending">Pending</option>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Staff Notes</label>
                                    <textarea class="form-control" name="staff_notes" id="editStandardNotes" rows="4"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label>Additional Services</label>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="standardSetup" name="needs_setup">
                                        <label class="custom-control-label" for="standardSetup">Buffet Setup (₱2,000)</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="standardTables" name="needs_tablesandchairs">
                                        <label class="custom-control-label" for="standardTables">Tables and Chairs (₱3,500)</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="standardDecoration" name="needs_decoration">
                                        <label class="custom-control-label" for="standardDecoration">Venue Decoration (₱5,000)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-3">
                            <label>Special Requests</label>
                            <textarea class="form-control" name="special_requests" id="editStandardRequests" rows="2"></textarea>
                        </div>
                        
                        <div class="total-cost mt-4 p-3 bg-light rounded">
                            <h6>Price Calculation:</h6>
                            <div id="standardCostBreakdown" class="mb-3"></div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Total Amount:</strong>
                                <strong id="standardTotalAmount">₱0.00</strong>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveStandardOrder">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // View Order Details
    $(document).on('click', '.view-order', function() {
        const orderType = $(this).data('type');
        const orderId = $(this).data('id');
        const items = $(this).data('items');
        const notes = $(this).data('notes');
        const eventDate = $(this).data('event-date');
        const venue = $(this).data('venue');
        const persons = $(this).data('persons');
        const packageType = $(this).data('package');
        const amount = $(this).data('amount');
        const services = $(this).data('services');
        
        if (orderType === 'delivery') {
            // Handle delivery order
            const scheduledDelivery = $(this).data('scheduled-delivery');
            const paymentMethod = $(this).data('payment-method');
            const deliveryAddress = $(this).data('delivery-address');
            
            // Display order items
            $('#orderItems').html(items.split(', ').map(item => `<div class="mb-2">${item}</div>`).join(''));
            
            // Load and display ingredients checklist
            $.get('admin_get_checklist.php', { order_id: orderId })
                .done(function(response) {
                    $('#ingredientsChecklist').html(response);
                    $('#ingredientsChecklist input[type="checkbox"]').prop('disabled', true);
                })
                .fail(function() {
                    $('#ingredientsChecklist').html('<div class="alert alert-danger">Failed to load checklist</div>');
                });
            
            $('#orderNotes').text(notes || 'No special notes');
            $('#deliveryAddress').text(deliveryAddress || 'No delivery address provided');
            $('#paymentMethod').text(paymentMethod || 'No payment method specified');
            
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
                        <i class="fas fa-clock"></i> Scheduled for: ${formattedDate}
                    </div>
                `);
            } else {
                $('#deliveryTime').html('<p class="text-muted">No scheduled delivery time</p>');
            }
            
            $('#deliveryOrderModal').modal('show');
        } else {
            // Handle catering order
            const orderPrefix = orderType === 'standard' ? 'CTR-' : 'CSP-';
            $('#catering-order-id').text(`${orderPrefix}${orderId}`);
            $('#catering-event-date').text(new Date(eventDate).toLocaleString());
            $('#catering-venue').text(venue);
            $('#catering-persons').text(persons);
            $('#catering-package').text(packageType);
            $('#catering-amount').text(amount ? `₱${parseFloat(amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : 'To be quoted');
            
            // Display menu items
            if (items) {
                const menuItems = items.split(',');
                $('#catering-menu-items').html(`
                    <table class="table table-sm">
                        <tbody>
                            ${menuItems.map(item => `
                                <tr><td><i class="fas fa-utensils text-accent mr-2"></i>${item.trim()}</td></tr>
                            `).join('')}
                        </tbody>
                    </table>
                `);
            } else {
                $('#catering-menu-items').html('<p class="text-muted">No menu items selected yet</p>');
            }

            // Display services
            const servicesList = [];
            if (services) {
                if (services.includes('setup')) servicesList.push('Buffet Setup (₱2,000)');
                if (services.includes('tables')) servicesList.push('Tables and Chairs (₱3,500)');
                if (services.includes('decoration')) servicesList.push('Venue Decoration (₱5,000)');
            }
            
            $('#catering-services').html(servicesList.length ? 
                servicesList.map(service => `<div><i class="fas fa-check text-success"></i> ${service}</div>`).join('') :
                '<p class="text-muted">No additional services selected</p>'
            );

            $('#catering-special-requests').text(notes || 'No special requests');
            $('#cateringOrderModal').modal('show');
        }
    });

    // Update Status
    $(document).on('click', '.update-status', function() {
        const orderId = $(this).data('id');
        const orderType = $(this).data('type');
        const currentStatus = $(this).data('current-status');
        const currentNotes = $(this).data('current-notes');
        
        $('#updateOrderId').val(orderId);
        $('#updateOrderType').val(orderType);
        
        const $statusSelect = $('#orderStatus').empty();
        
        if (orderType === 'delivery') {
            const statuses = ['pending', 'processing', 'in_kitchen', 'ready_for_delivery', 'delivering', 'completed', 'cancelled'];
            statuses.forEach(status => {
                const display = status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                $statusSelect.append(`<option value="${status}"${currentStatus === status ? ' selected' : ''}>${display}</option>`);
            });
        } else {
            const statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
            statuses.forEach(status => {
                const display = status.charAt(0).toUpperCase() + status.slice(1);
                $statusSelect.append(`<option value="${status}"${currentStatus === status ? ' selected' : ''}>${display}</option>`);
            });
        }
        
        $('textarea[name="status_notes"]').val(currentNotes);
        $('#updateStatusModal').modal('show');
    });

    // Save Status Update
    $(document).on('click', '#saveStatus', function() {
        const btn = $(this);
        const form = $('#updateStatusForm');
        const orderType = $('#updateOrderType').val();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        let endpoint;
        switch(orderType) {
            case 'delivery':
                endpoint = 'admin_update_order.php';
                break;
            case 'standard':
            case 'custom':
                endpoint = 'admin_update_catering_order.php';
                break;
            default:
                alert('Invalid order type');
                btn.prop('disabled', false).text('Update Status');
                return;
        }
        
        $.post(endpoint, form.serialize())
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

    // Initialize menu item management
    let selectedMenuItems = new Map(); // Map to store selected items

    // Edit Custom Order
    $(document).on('click', '.edit-custom-order', function() {
        const orderId = $(this).data('id');
        const preferences = $(this).data('preferences');
        const persons = $(this).data('persons');
        const amount = $(this).data('amount');
        const services = $(this).data('services') ? $(this).data('services').split(',') : [];
        const items = $(this).data('items') ? $(this).data('items').split(',') : [];
        const specialRequests = $(this).data('special-requests');
        const eventDate = $(this).data('event-date');
        const venue = $(this).data('venue');
        const status = $(this).data('status');
        const staffNotes = $(this).data('current-notes');

        // Reset selected items
        selectedMenuItems.clear();
        
        // Process selected items
        items.forEach(item => {
            const parts = item.split(': ');
            if (parts.length === 2) { // Only add if valid format (category: name)
                const category = parts[0].trim();
                const name = parts[1].trim();
                // Store temporarily with category information
                selectedMenuItems.set(name, {
                    id: name, // Will be replaced with actual ID when items are loaded
                    name: name,
                    category: category
                });
            }
        });

        $('#editOrderId').val(orderId);
        $('#editMenuPreferences').val(preferences);
        $('#editNumPersons').val(persons);
        $('#editQuoteAmount').val(amount);
        $('#editOrderStatus').val(status);
        $('#editStaffNotes').val(staffNotes);

        $('#setup').prop('checked', services.includes('setup'));
        $('#tables').prop('checked', services.includes('tables'));
        $('#decoration').prop('checked', services.includes('decoration'));

        // Load menu items and handle tab show
        loadMenuItems();
        
        // Ensure menu items are reloaded if tab is clicked after initial load
        $('#menu-tab').off('show.bs.tab').on('show.bs.tab', function() {
            loadMenuItems();
        });

        updateCostBreakdown();
        $('#editCustomOrderModal').modal('show');
    });

    // Load menu items function
    function loadMenuItems() {
        // Show loading status
        $('#categoryLoadingStatus').removeClass('alert-warning alert-danger').addClass('alert-info')
            .show().text('Loading menu categories...');
        
        // Clear existing items and categories
        $('#menuCategories').empty();
        $('#menuItemContent').empty();
        
        // Create a copy of the selected items to preserve original selections
        const tempSelectedItems = new Map(selectedMenuItems);
        
        // Load items from database via AJAX
        $.ajax({
            url: 'admin_get_menu_items.php',
            method: 'GET',
            dataType: 'json',
            cache: false,
            success: function(response) {
                if (response && response.success) {
                    console.log('Menu items loaded:', response.items.length);
                    
                    // Check if we have items
                    if (response.items.length === 0) {
                        $('#categoryLoadingStatus').removeClass('alert-info').addClass('alert-warning')
                            .text('No menu items found. Please add products to your menu first.');
                        return;
                    }
                    
                    // Group items by category
                    const items = response.items;
                    const categories = [...new Set(items.map(item => item.category_name))];
                    
                    console.log('Categories found:', categories);
                    
                    // First pass: match product IDs to selected items by name
                    items.forEach(item => {
                        // If this item's name is in our selected list but has a temp ID, update it
                        if (tempSelectedItems.has(item.prod_name)) {
                            const selectedItem = tempSelectedItems.get(item.prod_name);
                            // Replace temporary name ID with actual product ID
                            if (typeof selectedItem.id === 'string') {
                                selectedItem.id = item.product_id;
                                tempSelectedItems.set(item.prod_name, selectedItem);
                            }
                        }
                    });
                    
                    // Update the actual selectedMenuItems map
                    selectedMenuItems.clear();
                    tempSelectedItems.forEach((item, key) => {
                        selectedMenuItems.set(key, item);
                    });
                    
                    // Hide loading message if we have categories
                    if (categories.length > 0) {
                        $('#categoryLoadingStatus').hide();
                    } else {
                        $('#categoryLoadingStatus').removeClass('alert-info').addClass('alert-warning')
                            .text('No categories found. Please configure product categories first.');
                        return;
                    }
                    
                    // Generate category navigation tabs
                    categories.forEach((category, index) => {
                        const categoryId = 'category-' + category.toLowerCase().replace(/\s+/g, '-');
                        const isActive = index === 0 ? 'active' : '';
                        
                        $('#menuCategories').append(`
                            <a class="nav-link ${isActive}" 
                               id="${categoryId}-tab" 
                               data-toggle="pill" 
                               href="#${categoryId}" 
                               role="tab">
                                ${category}
                            </a>
                        `);
                        
                        // Create tab pane for this category
                        const showActive = index === 0 ? 'show active' : '';
                        $('#menuItemContent').append(`
                            <div class="tab-pane fade ${showActive}" 
                                 id="${categoryId}" 
                                 role="tabpanel">
                                <div class="menu-items-grid" data-category="${category}"></div>
                            </div>
                        `);
                    });
                    
                    // Apply explicit styling to nav links to ensure visibility
                    $('#menuCategories .nav-link').css({
                        'color': '#333',
                        'background-color': '#f0f0f0',
                        'margin-right': '5px',
                        'border-radius': '4px',
                        'font-weight': '500',
                        'padding': '8px 15px'
                    });
                    
                    $('#menuCategories .nav-link.active').css({
                        'color': '#fff',
                        'background-color': 'var(--accent, #0275d8)'
                    });
                    
                    // Add hover event handlers
                    $('#menuCategories .nav-link').hover(
                        function() {
                            // Mouse enter
                            if (!$(this).hasClass('active')) {
                                $(this).css({
                                    'background-color': '#e0e0e0',
                                    'color': '#222'
                                });
                            }
                        },
                        function() {
                            // Mouse leave
                            if (!$(this).hasClass('active')) {
                                $(this).css({
                                    'background-color': '#f0f0f0',
                                    'color': '#333'
                                });
                            }
                        }
                    );
                    
                    // Add click handler to properly style active tab
                    $('#menuCategories .nav-link').on('click', function() {
                        // Remove active styling from all tabs
                        $('#menuCategories .nav-link').css({
                            'color': '#333',
                            'background-color': '#f0f0f0'
                        });
                        
                        // Add active styling to clicked tab
                        $(this).css({
                            'color': '#fff',
                            'background-color': 'var(--accent, #0275d8)'
                        });
                    });
                    
                    // Add menu items to their respective category grids
                    items.forEach(item => {
                        const categoryId = 'category-' + item.category_name.toLowerCase().replace(/\s+/g, '-');
                        const grid = $(`#${categoryId} .menu-items-grid`);
                        const isSelected = selectedMenuItems.has(item.prod_name);
                        
                        const itemHtml = `
                            <div class="card menu-item mb-2 ${isSelected ? 'selected border-primary' : ''}" 
                                 data-id="${item.product_id}" 
                                 data-name="${item.prod_name}" 
                                 data-category="${item.category_name}"
                                 data-halal="${item.is_halal ? '1' : '0'}">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="card-title mb-1">${item.prod_name}</h6>
                                        <span class="badge ${item.is_halal ? 'badge-success' : 'badge-secondary'} ml-2">
                                            ${item.is_halal ? 'Halal' : 'Non-Halal'}
                                        </span>
                                    </div>
                                    <p class="card-text small mb-2">${item.prod_desc || 'No description available'}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">₱${parseFloat(item.prod_price).toFixed(2)}</span>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input menu-item-select" 
                                                   id="item_${item.product_id}" 
                                                   ${isSelected ? 'checked' : ''}>
                                            <label class="custom-control-label" for="item_${item.product_id}">Select</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        grid.append(itemHtml);
                    });

                    // Update the selected items list
                    updateSelectedItemsList();
                    
                    // Setup search and filter functionality
                    setupMenuFilters();
                } else {
                    // Show error for invalid response
                    console.error('Invalid response format:', response);
                    $('#categoryLoadingStatus').removeClass('alert-info').addClass('alert-danger')
                        .text('Error loading menu items: Invalid server response format');
                }
            },
            error: function(xhr, status, error) {
                // Handle AJAX error
                console.error('AJAX error:', xhr.status, status, error);
                console.log('Response text:', xhr.responseText);
                
                let errorMessage = 'Failed to load menu items. ';
                
                try {
                    // Try to parse the error response as JSON
                    const response = JSON.parse(xhr.responseText);
                    if (response && response.message) {
                        errorMessage += response.message;
                    } else {
                        errorMessage += 'Server error: ' + error;
                    }
                } catch (e) {
                    // If JSON parsing fails, show the raw response or error
                    errorMessage += 'Server error: ' + (xhr.responseText || error);
                }
                
                $('#categoryLoadingStatus').removeClass('alert-info').addClass('alert-danger')
                    .text(errorMessage);
            }
        });
    }
    
    // Setup menu search and filter functionality
    function setupMenuFilters() {
        const filterMenuItems = function() {
            const searchText = $('#menuSearchBox').val().toLowerCase();
            const showHalalOnly = $('#menuHalalOnly').is(':checked');
            
            $('.menu-item').each(function() {
                const $item = $(this);
                const productName = $item.find('.card-title').text().toLowerCase();
                const isHalal = $item.data('halal') === 1;
                
                const matchesSearch = productName.includes(searchText);
                const matchesHalal = !showHalalOnly || isHalal;
                
                $item.toggle(matchesSearch && matchesHalal);
            });
        };
        
        // Attach event listeners
        $('#menuSearchBox').off('keyup').on('keyup', filterMenuItems);
        $('#menuHalalOnly').off('change').on('change', filterMenuItems);
    }

    // Handle menu item selection
    $(document).on('change', '.menu-item-select', function() {
        const checkbox = $(this);
        const card = checkbox.closest('.card');
        const itemId = card.data('id');
        const itemName = card.data('name');
        const category = card.data('category');
        
        if (checkbox.is(':checked')) {
            card.addClass('selected border-primary');
            selectedMenuItems.set(itemName, {
                id: itemId,
                name: itemName,
                category: category
            });
        } else {
            card.removeClass('selected border-primary');
            selectedMenuItems.delete(itemName);
        }
        
        updateSelectedItemsList();
    });

    function updateSelectedItemsList() {
        const list = $('#selectedItemsList');
        list.empty();
        
        if (selectedMenuItems.size === 0) {
            list.append('<div class="text-muted p-3 text-center">No items selected</div>');
            return;
        }
        
        const groupedItems = new Map();
        
        // Group items by category
        selectedMenuItems.forEach((item, name) => {
            if (!groupedItems.has(item.category)) {
                groupedItems.set(item.category, []);
            }
            groupedItems.get(item.category).push({name: name, id: item.id});
        });
        
        // Create a section for each category
        groupedItems.forEach((items, category) => {
            list.append(`<div class="list-group-item list-group-item-secondary font-weight-bold">${category}</div>`);
            
            items.forEach(item => {
                list.append(`
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span>${item.name}</span>
                        <button type="button" class="btn btn-sm btn-danger remove-item" data-name="${item.name}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `);
            });
        });
    }

    // Handle menu item removal from selected items list
    $(document).on('click', '.remove-item', function() {
        const itemName = $(this).data('name');
        const item = selectedMenuItems.get(itemName);
        if (item) {
            $(`#item_${item.id}`).prop('checked', false).change();
        }
    });

    // Save Custom Order Changes
    $(document).on('click', '#saveCustomOrder', function() {
        const btn = $(this);
        const form = $('#editCustomOrderForm');
        
        // Ensure quote_amount is a valid number
        const quoteAmount = $('#editQuoteAmount').val();
        if (quoteAmount !== '') {
            $('#editQuoteAmount').val(parseFloat(quoteAmount));
        }
        
        const selectedServices = [];
        if ($('#setup').prop('checked')) selectedServices.push('setup');
        if ($('#tables').prop('checked')) selectedServices.push('tables');
        if ($('#decoration').prop('checked')) selectedServices.push('decoration');
        
        const formData = new FormData(form[0]);
        formData.append('services', selectedServices.join(','));
        
        // Convert the selectedMenuItems Map to an array of IDs
        const selectedItemIds = [];
        selectedMenuItems.forEach(item => {
            selectedItemIds.push(item.id);
        });
        formData.append('selectedItems', JSON.stringify(selectedItemIds));
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            url: 'admin_update_custom_order.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response === 'success') {
                    location.reload();
                } else {
                    alert('Error: ' + response);
                }
            },
            error: function() {
                alert('Failed to save changes. Please try again.');
            },
            complete: function() {
                btn.prop('disabled', false).text('Save Changes');
            }
        });
    });

    // Cost Breakdown Updates
    $(document).on('change', '#editQuoteAmount, #setup, #tables, #decoration', updateCostBreakdown);

    function updateCostBreakdown() {
        const baseAmount = parseFloat($('#editQuoteAmount').val()) || 0;
        const setupCost = $('#setup').is(':checked') ? 2000 : 0;
        const tablesCost = $('#tables').is(':checked') ? 3500 : 0;
        const decorationCost = $('#decoration').is(':checked') ? 5000 : 0;
        const totalAmount = baseAmount + setupCost + tablesCost + decorationCost;

        let breakdown = `<div class="mb-2">Base Amount: ₱${baseAmount.toFixed(2)}</div>`;
        if (setupCost) breakdown += `<div class="mb-2">Buffet Setup: ₱${setupCost.toFixed(2)}</div>`;
        if (tablesCost) breakdown += `<div class="mb-2">Tables and Chairs: ₱${tablesCost.toFixed(2)}</div>`;
        if (decorationCost) breakdown += `<div class="mb-2">Venue Decoration: ₱${decorationCost.toFixed(2)}</div>`;

        $('#costBreakdown').html(breakdown);
        $('#totalAmount').text(`₱${totalAmount.toFixed(2)}`);
    }

    // Handle Standard Catering Order Edit
    $(document).on('click', '.edit-standard-order', function() {
        console.log('Edit standard order button clicked');
        const orderId = $(this).data('id');
        const status = $(this).data('status');
        const staffNotes = $(this).data('current-notes') || '';
        const packageType = $(this).data('package') || '';
        const persons = $(this).data('persons') || '';
        const eventDate = $(this).data('event-date') || '';
        const venue = $(this).data('venue') || '';
        const services = $(this).data('services') ? $(this).data('services').split(',') : [];
        const specialRequests = $(this).data('notes') || '';

        console.log('Order data:', {
            orderId, status, packageType, persons, eventDate, venue, services, specialRequests, staffNotes
        });

        // Clear and reset the form
        $('#editStandardOrderForm')[0].reset();
        
        // Set form values
        $('#editStandardOrderId').val(orderId);
        $('#editStandardStatus').val(status);
        $('#editStandardNotes').val(staffNotes);
        $('#editStandardPackage').val(packageType);
        $('#editStandardPersons').val(persons);
        $('#editStandardEventDate').val(eventDate);
        $('#editStandardVenue').val(venue);
        $('#editStandardRequests').val(specialRequests);
        
        // Set service checkboxes
        $('#standardSetup').prop('checked', services.includes('setup'));
        $('#standardTables').prop('checked', services.includes('tables'));
        $('#standardDecoration').prop('checked', services.includes('decoration'));
        
        // Update cost breakdown
        setTimeout(updateStandardCostBreakdown, 100);
        
        // Show the modal
        $('#editStandardOrderModal').modal('show');
    });

    // Update cost breakdown when inputs change
    $(document).on('change', '#editStandardPackage, #editStandardPersons, #standardSetup, #standardTables, #standardDecoration', updateStandardCostBreakdown);
    $(document).on('input', '#editStandardPersons', updateStandardCostBreakdown);

    // Calculate standard package costs (client-side estimate)
    function updateStandardCostBreakdown() {
        const packageType = $('#editStandardPackage').val();
        const packageText = $('#editStandardPackage option:selected').text();
        const numPersons = parseInt($('#editStandardPersons').val()) || 0;
        const setupCost = $('#standardSetup').is(':checked') ? 2000 : 0;
        const tablesCost = $('#standardTables').is(':checked') ? 3500 : 0;
        const decorationCost = $('#standardDecoration').is(':checked') ? 5000 : 0;
        
        // Extract rate from the option text (Format: "Package Name (₱XXX.XX per person)")
        let ratePerPerson = 0;
        const priceMatch = packageText.match(/₱([\d,]+\.\d+)/);
        if (priceMatch && priceMatch[1]) {
            ratePerPerson = parseFloat(priceMatch[1].replace(/,/g, ''));
        }
        
        console.log('Rate extracted from dropdown:', ratePerPerson);
        
        // Calculate totals
        const packageTotal = ratePerPerson * numPersons;
        const totalAmount = packageTotal + setupCost + tablesCost + decorationCost;
        
        console.log('Client-side calculation:', {packageType, ratePerPerson, numPersons, packageTotal, setupCost, tablesCost, decorationCost, totalAmount});
        
        // Build breakdown HTML
        let breakdown = `
            <div class="mb-2">${packageType}: ₱${ratePerPerson.toFixed(2)} × ${numPersons} persons = ₱${packageTotal.toFixed(2)}</div>
        `;
        
        if (setupCost) breakdown += `<div class="mb-2">Buffet Setup: ₱${setupCost.toFixed(2)}</div>`;
        if (tablesCost) breakdown += `<div class="mb-2">Tables and Chairs: ₱${tablesCost.toFixed(2)}</div>`;
        if (decorationCost) breakdown += `<div class="mb-2">Venue Decoration: ₱${decorationCost.toFixed(2)}</div>`;
        
        $('#standardCostBreakdown').html(breakdown);
        $('#standardTotalAmount').text(`₱${totalAmount.toFixed(2)}`);
        
        // Note: We don't set standardTotalAmountInput anymore since calculation is done server-side
    }

    // Save Standard Catering Order Changes
    $(document).on('click', '#saveStandardOrder', function() {
        console.log('Save standard order button clicked');
        const btn = $(this);
        const form = $('#editStandardOrderForm');
        
        // Validate required fields
        if (!form[0].checkValidity()) {
            console.log('Form validation failed');
            form[0].reportValidity();
            return;
        }
        
        // Collect services
        const selectedServices = [];
        if ($('#standardSetup').prop('checked')) selectedServices.push('setup');
        if ($('#standardTables').prop('checked')) selectedServices.push('tables');
        if ($('#standardDecoration').prop('checked')) selectedServices.push('decoration');
        
        // Create FormData object and append data
        const formData = new FormData(form[0]);
        formData.append('services', selectedServices.join(','));
        
        // Log the form data before sending
        console.log('Order ID:', $('#editStandardOrderId').val());
        console.log('Package:', $('#editStandardPackage').val());
        console.log('Persons:', $('#editStandardPersons').val());
        console.log('Event Date:', $('#editStandardEventDate').val());
        console.log('Venue:', $('#editStandardVenue').val());
        console.log('Status:', $('#editStandardStatus').val());
        console.log('Services:', selectedServices);
        
        // Set button to loading state
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        // Send AJAX request
        $.ajax({
            url: 'admin_update_standard_order.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Server response:', response);
                if (response === 'success') {
                    location.reload();
                } else {
                    alert('Error: ' + response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                alert('Failed to save changes. Please try again. Error: ' + (xhr.responseText || error));
            },
            complete: function() {
                btn.prop('disabled', false).text('Save Changes');
            }
        });
    });

    // Trigger calculation updates when inputs change
    $('#editStandardPackage, #editStandardPersons').on('change input', function() {
        updateStandardCostBreakdown();
    });
    
    $('#standardSetup, #standardTables, #standardDecoration').on('change', function() {
        updateStandardCostBreakdown();
    });
});
</script>
