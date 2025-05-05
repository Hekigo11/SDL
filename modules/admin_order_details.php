<?php
require_once __DIR__ . '/../config.php';
// session_start();

// Check if user is admin
if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

include("dbconi.php");
?>


    <style>
        .checklist-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .checklist-item input[type="checkbox"] {
            margin-right: 0.5rem;
        }
        .order-actions {
            min-width: 150px;
        }
        .custom-checkbox .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #28a745;
            border-color: #28a745;
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
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT o.*, GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.prod_name) SEPARATOR ', ') as items,
                                     u.email_add as customer_email
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
                                <td>#<?php echo $row['order_id']; ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($row['full_name']); ?><br>
                                    <small class="text-muted"><?php echo $row['phone']; ?></small>
                                </td>
                                <td><?php echo $row['items']; ?></td>
                                <td>₱<?php echo number_format($row['total_amount'], 2); ?></td>
                                <td><span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                <td class="order-actions">
                                    <button class="btn btn-sm btn-info view-order" 
                                            data-id="<?php echo $row['order_id']; ?>"
                                            data-items="<?php echo htmlspecialchars($row['items']); ?>"
                                            data-notes="<?php echo htmlspecialchars($row['notes']); ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary update-status"
                                            data-id="<?php echo $row['order_id']; ?>"
                                            data-current-status="<?php echo $row['status']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
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
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Order Items</h6>
                            <div id="orderItems" class="mb-4"></div>
                            
                            <h6>Special Notes</h6>
                            <p id="orderNotes" class="text-muted"></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Order Checklist</h6>
                            <div id="orderChecklist"></div>
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
                                <option value="processing">Processing</option>
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
            
            $('#orderItems').html(items.split(', ').map(item => `<div class="mb-2">${item}</div>`).join(''));
            $('#orderNotes').text(notes || 'No special notes');
            
            // Load order checklist
            $('#orderChecklist').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading checklist...</div>');
            
            $.get('admin_get_checklist.php', { order_id: orderId })
                .done(function(response) {
                    $('#orderChecklist').html(response);
                })
                .fail(function(xhr) {
                    $('#orderChecklist').html('<p class="text-danger">Failed to load checklist. Please try again.</p>');
                });
            
            $('#orderDetailsModal').modal('show');
        });

        // Update checklist item status
        $(document).on('change', '.checklist-checkbox', function() {
            const checkbox = $(this);
            const itemId = checkbox.data('id');
            const isChecked = checkbox.prop('checked');
            const originalState = !isChecked;
            
            $.post('admin_update_checklist.php', {
                item_id: itemId,
                is_ready: isChecked ? 1 : 0
            })
            .done(function(response) {
                if (response !== 'success') {
                    checkbox.prop('checked', originalState);
                    alert('Failed to update checklist item: ' + response);
                }
            })
            .fail(function() {
                checkbox.prop('checked', originalState);
                alert('Failed to update checklist item. Please try again.');
            });
        });

        // Update Order Status
        $('.update-status').click(function() {
            const orderId = $(this).data('id');
            const currentStatus = $(this).data('current-status');
            
            $('#updateOrderId').val(orderId);
            $('#orderStatus').val(currentStatus);
            $('#updateStatusModal').modal('show');
        });

        // Save Status Update
        $('#saveStatus').click(function() {
            const btn = $(this);
            const originalText = btn.text();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
            
            const formData = $('#updateStatusForm').serialize();
            
            $.post('admin_update_order.php', formData)
                .done(function(response) {
                    if (response === 'success') {
                        location.reload();
                    } else {
                        alert('Error updating order status: ' + response);
                        btn.prop('disabled', false).text(originalText);
                    }
                })
                .fail(function() {
                    alert('Failed to update order status. Please try again.');
                    btn.prop('disabled', false).text(originalText);
                });
        });
    });
    </script>
