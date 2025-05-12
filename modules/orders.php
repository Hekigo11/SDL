<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['loginok'])) {
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
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/vendor/style1.css">
        <title>My Orders - MARJ Food Services</title>
        <style>
            .badge {
                font-size: 0.875rem;
                padding: 0.375rem 0.75rem;
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
            .cancel-reason {
                font-size: 0.813rem;
                color: #6c757d;
                margin-left: 20px;
                font-style: italic;
            }
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.875rem;
            }
            .modal-content {
                border: none;
                border-radius: 0.5rem;
            }
            .modal-header {
                border-bottom: 1px solid #dee2e6;
                border-top-left-radius: 0.5rem;
                border-top-right-radius: 0.5rem;
            }
            .empty-state {
                text-align: center;
                padding: 2rem 0;
            }
            .empty-state i {
                font-size: 3rem;
                color: #6c757d;
                margin-bottom: 1rem;
            }
        </style>
    </head>
    <body>
        <?php include("navigation.php"); ?>
        
        <main class="container py-5">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>            <ul class="nav nav-tabs mb-4">
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
                    <h2 class="h4 mb-4">Delivery Orders</h2>
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                include("dbconi.php");
                                
                                $query = "SELECT o.*, GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.prod_name) SEPARATOR ', ') as items
                                         FROM orders o
                                         JOIN order_items oi ON o.order_id = oi.order_id
                                         JOIN products p ON oi.product_id = p.product_id
                                         WHERE o.user_id = ?
                                         GROUP BY o.order_id
                                         ORDER BY o.created_at DESC";
                                         
                                $stmt = $dbc->prepare($query);
                                $stmt->bind_param('i', $_SESSION['user_id']);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $statusClass = '';
                                        switch($row['status']) {
                                            case 'pending':
                                                $statusClass = 'warning';
                                                break;
                                            case 'processing':
                                            case 'in_kitchen':
                                                $statusClass = 'info';
                                                break;
                                            case 'ready_for_delivery':
                                                $statusClass = 'primary';
                                                break;
                                            case 'delivering':
                                                $statusClass = 'info';
                                                break;
                                            case 'completed':
                                                $statusClass = 'success';
                                                break;
                                            case 'cancelled':
                                                $statusClass = 'danger';
                                                break;
                                            default:
                                                $statusClass = 'secondary';
                                        }
                                        
                                        $statusDisplay = str_replace('_', ' ', ucfirst($row['status']));
                                        ?>
                                        <tr class="<?php echo $row['status'] === 'cancelled' ? 'cancelled' : ($row['status'] === 'completed' ? 'completed' : ''); ?>">
                                            <td>#<?php echo $row['order_id']; ?></td>
                                            <td><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
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
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($row['status'] !== 'delivering' && $row['status'] !== 'completed' && $row['status'] !== 'cancelled'): ?>
                                                    <button class="btn btn-sm btn-danger" onclick="showCancelModal(<?php echo $row['order_id']; ?>)">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="6" class="empty-state">
                                            <i class="fas fa-box-open"></i>
                                            <p class="text-muted">You haven't placed any delivery orders yet.</p>
                                            <a href="<?php echo BASE_URL; ?>/modules/products.php" class="btn btn-primary">Browse Products</a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Catering Orders Tab -->
                <div class="tab-pane fade" id="catering">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h4 mb-0">Catering Orders</h2>
                        <a href="<?php echo BASE_URL; ?>/modules/catering.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Catering Request
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Event Date</th>
                                    <th>Venue</th>
                                    <th>Package</th>
                                    <th>Persons</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Get standard catering orders with menu selections
                                $standard_query = "SELECT co.*, 'standard' AS order_type,
                                    GROUP_CONCAT(DISTINCT CONCAT(c.category_name, ': ', p.prod_name)) as selected_items
                                    FROM catering_orders co
                                    LEFT JOIN catering_order_menu_items comi ON co.catering_id = comi.catering_order_id
                                    LEFT JOIN products p ON comi.product_id = p.product_id
                                    LEFT JOIN categories c ON p.prod_cat_id = c.category_id
                                    WHERE co.user_id = ?
                                    GROUP BY co.catering_id
                                    ORDER BY co.created_at DESC";
                                $stmt = $dbc->prepare($standard_query);
                                $stmt->bind_param('i', $_SESSION['user_id']);
                                $stmt->execute();
                                $standard_result = $stmt->get_result();
                                
                                // Only count standard catering orders for this tab
                                $total_catering_orders = $standard_result->num_rows;

                                if ($total_catering_orders > 0) {
                                    // Display standard catering orders
                                    while ($row = $standard_result->fetch_assoc()) {
                                        $statusClass = '';
                                        switch($row['status']) {
                                            case 'pending':
                                                $statusClass = 'warning';
                                                break;
                                            case 'confirmed':
                                                $statusClass = 'info';
                                                break;
                                            case 'completed':
                                                $statusClass = 'success';
                                                break;
                                            case 'cancelled':
                                                $statusClass = 'danger';
                                                break;
                                            default:
                                                $statusClass = 'secondary';
                                        }
                                        ?>
                                        <tr>
                                            <td>CTR-<?php echo $row['catering_id']; ?></td>
                                            <td><?php echo date('M j, Y g:i A', strtotime($row['event_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($row['venue']); ?></td>
                                            <td><?php echo htmlspecialchars($row['menu_package'] ?: 'Not specified'); ?></td>
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
                                            <td><span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="showCateringDetails('standard', <?php echo $row['catering_id']; ?>, <?php echo htmlspecialchars(json_encode($row), ENT_QUOTES); ?>)">
                                                    <i class="fas fa-eye"></i> Details
                                                </button>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="8" class="empty-state">
                                            <i class="fas fa-calendar-alt"></i>
                                            <p class="text-muted">You haven't placed any catering orders yet.</p>
                                            <a href="<?php echo BASE_URL; ?>/modules/catering.php" class="btn btn-primary">Request Catering Service</a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Custom Catering Orders Tab -->
                <div class="tab-pane fade" id="custom-catering">
                    <h2 class="h4 mb-4">Custom Catering Orders</h2>
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Event Date</th>
                                    <th>Package</th>
                                    <th>Persons</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $custom_query = "SELECT co.*, 
                                    GROUP_CONCAT(DISTINCT CONCAT(c.category_name, ': ', p.prod_name)) as items
                                    FROM custom_catering_orders co
                                    LEFT JOIN cust_catering_order_items coi ON co.custom_order_id = coi.custom_order_id
                                    LEFT JOIN products p ON coi.product_id = p.product_id
                                    LEFT JOIN categories c ON coi.category_id = c.category_id
                                    WHERE co.user_id = " . $_SESSION['user_id'] . "
                                    GROUP BY co.custom_order_id
                                    ORDER BY co.created_at DESC";
                                
                                $custom_result = mysqli_query($dbc, $custom_query);

                                if (mysqli_num_rows($custom_result) > 0) {
                                    while ($row = mysqli_fetch_assoc($custom_result)) {
                                        // Determine the status class for the badge
                                        $statusClass = '';
                                        switch($row['status']) {
                                            case 'pending': $statusClass = 'warning'; break;
                                            case 'confirmed': $statusClass = 'info'; break;
                                            case 'completed': $statusClass = 'success'; break;
                                            case 'cancelled': $statusClass = 'danger'; break;
                                            default: $statusClass = 'secondary';
                                        }

                                        // Determine if it's a small group
                                        $isSmallGroup = ($row['num_persons'] < 50);
                                        
                                        ?>
                                        <tr class="<?php echo $row['status'] === 'cancelled' ? 'cancelled' : ($row['status'] === 'completed' ? 'completed' : ''); ?>">
                                            <td>CSP-<?php echo $row['custom_order_id']; ?></td>
                                            <td><?php echo date('M j, Y', strtotime($row['event_date'])); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($row['menu_preferences'] ?: 'Custom Package'); ?>
                                                <?php if ($isSmallGroup): ?>
                                                    <span class="badge badge-info">Small Group</span>
                                                <?php endif; ?>
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
                                                        <div class="text-muted small">
                                                            <i class="fas fa-comment"></i> <?php echo htmlspecialchars($row['staff_notes']); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info view-custom-order" 
                                                        data-id="<?php echo $row['custom_order_id']; ?>"
                                                        data-items="<?php echo htmlspecialchars($row['items'] ?? ''); ?>"
                                                        data-event-date="<?php echo date('M j, Y', strtotime($row['event_date'])); ?>"
                                                        data-venue="<?php echo htmlspecialchars($row['venue']); ?>"
                                                        data-persons="<?php echo $row['num_persons']; ?>"
                                                        data-package="<?php echo htmlspecialchars($row['menu_preferences'] ?: 'Custom Package'); ?>"
                                                        data-amount="<?php echo $row['quote_amount'] ?? ''; ?>"
                                                        data-notes="<?php echo htmlspecialchars($row['special_requests'] ?? ''); ?>"
                                                        data-services="<?php 
                                                            $services = [];
                                                            if ($row['needs_setup']) $services[] = 'setup';
                                                            if ($row['needs_tablesandchairs']) $services[] = 'tables';
                                                            if ($row['needs_decoration']) $services[] = 'decoration';
                                                            echo implode(',', $services); 
                                                        ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($row['status'] === 'pending'): ?>
                                                    <button class="btn btn-sm btn-danger cancel-custom-order" 
                                                            data-id="<?php echo $row['custom_order_id']; ?>">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php }
                                } else { ?>
                                    <tr>
                                        <td colspan="7">
                                            <div class="empty-state">
                                                <i class="fas fa-calendar-alt"></i>
                                                <p class="lead">No custom catering orders yet</p>
                                                <p>Book a custom catering package for your special events!</p>
                                                <a href="catering.php" class="btn btn-primary">Book Custom Catering</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        <!-- Cancel Order Modal -->
        <div class="modal fade" id="cancelOrderModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cancel Order</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="cancelOrderForm" method="POST" action="cancel_order.php">
                        <div class="modal-body">
                            <input type="hidden" name="order_id" id="cancel_order_id">
                            <div class="form-group">
                                <label>Reason for Cancellation:</label>
                                <select class="form-control" name="reason" required>
                                    <option value="">Select a reason</option>
                                    <option value="Changed my mind">Changed my mind</option>
                                    <option value="Ordered by mistake">Ordered by mistake</option>
                                    <option value="Delivery time too long">Delivery time too long</option>
                                    <option value="Payment issues">Payment issues</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="form-group" id="otherReasonDiv" style="display: none;">
                                <label>Please specify:</label>
                                <textarea class="form-control" name="other_reason"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Catering Details Modal -->
        <div class="modal fade" id="cateringDetailsModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background-color:var(--accent);">
                        <h5 class="modal-title text-white">Catering Order Details</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Event Details</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th>Order ID:</th>
                                        <td id="details-order-id"></td>
                                    </tr>
                                    <tr>
                                        <th>Event Date:</th>
                                        <td id="details-event-date"></td>
                                    </tr>
                                    <tr>
                                        <th>Venue:</th>
                                        <td id="details-venue"></td>
                                    </tr>
                                    <tr>
                                        <th>Number of Persons:</th>
                                        <td id="details-persons"></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Package Details</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th>Package Type:</th>
                                        <td id="details-package"></td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td id="details-status"></td>
                                    </tr>
                                    <tr>
                                        <th>Amount:</th>
                                        <td id="details-amount"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Selected Items</h6>
                                <div id="details-menu-items" class="table-responsive" style="max-height:220px; overflow-y:auto; border:1px solid #eee; border-radius:6px;">
                                    <!-- Menu items will be loaded here -->
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Additional Services</h6>
                                <div id="details-services"></div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Special Requests</h6>
                                <p id="details-special-requests" class="text-muted"></p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Catering Order Details Modal -->
        <div class="modal fade" id="customCateringModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Custom Catering Order Details</h5>
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
                                        <th>Event Date:</th>
                                        <td id="custom-event-date"></td>
                                    </tr>
                                    <tr>
                                        <th>Venue:</th>
                                        <td id="custom-venue"></td>
                                    </tr>
                                    <tr>
                                        <th>Number of Persons:</th>
                                        <td id="custom-persons"></td>
                                    </tr>
                                    <tr>
                                        <th>Package Type:</th>
                                        <td id="custom-package"></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Amount Details</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th>Base Amount:</th>
                                        <td id="custom-amount"></td>
                                    </tr>
                                    <tr>
                                        <th>Additional Services:</th>
                                        <td id="custom-services"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Menu Preferences</h6>
                                <div id="custom-menu-items"></div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Special Requests</h6>
                                <p id="custom-notes" class="text-muted"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        function showCancelModal(orderId) {
            document.getElementById('cancel_order_id').value = orderId;
            $('#cancelOrderModal').modal('show');
        }

        document.querySelector('select[name="reason"]').addEventListener('change', function() {
            const otherReasonDiv = document.getElementById('otherReasonDiv');
            otherReasonDiv.style.display = this.value === 'Other' ? 'block' : 'none';
        });

        function showCateringDetails(type, orderId, orderData) {
            const modal = $('#cateringDetailsModal');
            
            // Set basic details
            $('#details-order-id').text(type === 'standard' ? `CTR-${orderId}` : `CSP-${orderId}`);
            $('#details-event-date').text(new Date(orderData.event_date).toLocaleString());
            $('#details-venue').text(orderData.venue);
            $('#details-persons').text(orderData.num_persons);
            $('#details-package').text(orderData.menu_package || orderData.menu_preferences || 'Custom Package');
            
            // Set status with badge
            const statusBadgeClass = {
                'pending': 'warning',
                'confirmed': 'info',
                'completed': 'success',
                'cancelled': 'danger'
            }[orderData.status] || 'secondary';
            
            $('#details-status').html(`<span class="badge badge-${statusBadgeClass}">${orderData.status.charAt(0).toUpperCase() + orderData.status.slice(1)}</span>`);
            
            // Set amount
            const amount = type === 'standard' ? orderData.total_amount : (orderData.quote_amount || 'To be quoted');
            $('#details-amount').text(typeof amount === 'number' ? `₱${amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : amount);

            // Display selected menu items
            if (orderData.selected_items) {
                const items = orderData.selected_items.split(',');
                $('#details-menu-items').html(`
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${items.map(item => `
                                <tr>
                                    <td><i class="fas fa-utensils text-accent mr-2"></i>${item.trim()}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `);
            } else {
                $('#details-menu-items').html('<p class="text-muted">No menu items selected yet</p>');
            }

            // Set additional services
            const services = [];
            const selectedServices = orderData.selected_services ? orderData.selected_services.split(',') : [];
            
            if (orderData.needs_setup === '1' || selectedServices.includes('setup')) {
                services.push('Buffet Setup (₱2,000)');
            }
            if (orderData.needs_tablesandchairs === '1' || selectedServices.includes('tables')) {
                services.push('Tables and Chairs (₱3,500)');
            }
            if (orderData.needs_decoration === '1' || selectedServices.includes('decoration')) {
                services.push('Venue Decoration (₱5,000)');
            }
            
            $('#details-services').html(services.length ? 
                services.map(service => `<div><i class="fas fa-check text-success"></i> ${service}</div>`).join('') :
                '<p class="text-muted">No additional services selected</p>'
            );

            // Set special requests
            $('#details-special-requests').text(orderData.special_requests || 'No special requests');

            modal.modal('show');
        }

        $(document).ready(function() {
            // View Custom Catering Order Details
            $('.view-custom-order').click(function() {
                const data = $(this).data();
                
                // Set basic details
                $('#custom-event-date').text(data.eventDate);
                $('#custom-venue').text(data.venue);
                $('#custom-persons').text(data.persons + ' persons');
                $('#custom-package').text(data.package);
                
                // Set amount and format it with PHP currency format
                $('#custom-amount').html(
                    data.amount ? 
                    '₱' + parseFloat(data.amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) :
                    '<em>To be quoted</em>'
                );
                
                // Set selected menu items
                if (data.items) {
                    const menuItems = data.items.split(',');
                    $('#custom-menu-items').html(`
                        <div class="table-responsive" style="max-height:200px; overflow-y:auto;">
                            <table class="table table-sm">
                                <tbody>
                                    ${menuItems.map(item => `
                                        <tr>
                                            <td><i class="fas fa-utensils text-accent mr-2"></i>${item.trim()}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `);
                } else {
                    $('#custom-menu-items').html('<p class="text-muted">Menu items to be finalized</p>');
                }

                // Set additional services
                const services = data.services ? data.services.split(',') : [];
                const servicesList = [];
                if (services.includes('setup')) servicesList.push('Buffet Setup (₱2,000)');
                if (services.includes('tables')) servicesList.push('Tables and Chairs (₱3,500)');
                if (services.includes('decoration')) servicesList.push('Venue Decoration (₱5,000)');
                
                $('#custom-services').html(servicesList.length ? 
                    servicesList.map(service => `<div><i class="fas fa-check text-success"></i> ${service}</div>`).join('') :
                    '<p class="text-muted mb-0">No additional services selected</p>'
                );

                // Set special requests
                $('#custom-notes').text(data.notes || 'No special requests');

                $('#customCateringModal').modal('show');
            });

            // Cancel Custom Catering Order
            $('.cancel-custom-order').click(function() {
                if (confirm('Are you sure you want to cancel this custom catering order?')) {
                    const orderId = $(this).data('id');
                    $.post('cancel_order.php', {
                        order_id: orderId,
                        order_type: 'custom'
                    })
                    .done(function(response) {
                        if (response === 'success') {
                            location.reload();
                        } else {
                            alert('Failed to cancel order: ' + response);
                        }
                    })
                    .fail(function() {
                        alert('Failed to cancel order. Please try again.');
                    });
                }
            });
        });
        </script>

        <?php include('authenticate.php'); ?>
        
        <!-- Logout Modal -->
        <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
            <div class="modal-dialog" style="margin: 25vh auto;" role="document">
                <div class="modal-content" style="border-radius: 30px;">
                    <div class="modal-header text-center position-center" style="background-color:var(--accent); border-radius: 30px 30px 0 0;">
                        <h5 class="modal-title text-light" id="logoutModalLabel">Logout</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <p>Are you sure you want to log out?</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <a href="<?php echo BASE_URL; ?>/modules/logout.php" class="btn btn-danger">Yes, Logout</a>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>