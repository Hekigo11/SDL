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
            <?php endif; ?>

            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link active" id="delivery-tab" data-toggle="tab" href="#delivery">Delivery Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="catering-tab" data-toggle="tab" href="#catering">Catering Orders</a>
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
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT * FROM catering_orders WHERE user_id = ? ORDER BY created_at DESC";
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
                                            <td>₱<?php echo number_format($row['total_amount'], 2); ?></td>
                                            <td><span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="6" class="empty-state">
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

        <script>
        function showCancelModal(orderId) {
            document.getElementById('cancel_order_id').value = orderId;
            $('#cancelOrderModal').modal('show');
        }

        document.querySelector('select[name="reason"]').addEventListener('change', function() {
            const otherReasonDiv = document.getElementById('otherReasonDiv');
            otherReasonDiv.style.display = this.value === 'Other' ? 'block' : 'none';
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