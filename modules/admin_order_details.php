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
</style>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Manage Orders</h2>
        </div>
    </div>

    <!-- Orders Table -->
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
                        $query = "SELECT o.*, 
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
                        $result = mysqli_query($dbc, $query);
                        while ($row = mysqli_fetch_assoc($result)) {
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
                                    <?php if (!empty($row['status_notes'])): ?>
                                        <div class="text-muted small mt-1">
                                            <i class="fas fa-comment"></i> <?php echo htmlspecialchars($row['status_notes']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="order-actions">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-info view-order" 
                                            data-id="<?php echo $row['order_id']; ?>"
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

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
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
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="status" id="orderStatus" required>
                                <option value="pending">Pending</option>
                                <option value="processing">In Kitchen</option>
                                <option value="ready_for_delivery">Ready for Delivery</option>
                                <option value="delivering">Out for Delivery</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Notes (optional)</label>
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
            
            // Display delivery address
            $('#deliveryAddress').text(deliveryAddress || 'No delivery address provided');
            
            // Display payment method
            $('#paymentMethod').text(paymentMethod || 'No payment method specified');
            
            $('#orderDetailsModal').modal('show');
        });

        // Update Order Status
        $('.update-status').click(function() {
            const orderId = $(this).data('id');
            const currentStatus = $(this).data('current-status');
            const currentNotes = $(this).data('current-notes');
            
            $('#updateOrderId').val(orderId);
            $('#orderStatus').val(currentStatus);
            $('textarea[name="status_notes"]').val(currentNotes);
            $('#updateStatusModal').modal('show');
        });

        // Save Status Update
        $('#saveStatus').click(function() {
            const btn = $(this);
            const form = $('#updateStatusForm');
            const orderId = $('#updateOrderId').val();
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            
            $.post('admin_update_order.php', form.serialize())
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
