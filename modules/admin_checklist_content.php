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
        <div class="col-md-6">
            <h2>Order Checklist Management</h2>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="checklistTable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT o.order_id, o.created_at, o.status, 
                                 GROUP_CONCAT(CONCAT(p.prod_name, ' (', oi.quantity, ')') SEPARATOR ', ') as items,
                                 o.notes as special_notes
                                 FROM orders o
                                 JOIN order_items oi ON o.order_id = oi.order_id
                                 JOIN products p ON oi.product_id = p.product_id
                                 GROUP BY o.order_id
                                 ORDER BY o.created_at DESC";
                        
                        $result = mysqli_query($dbc, $query);
                        
                        while ($row = mysqli_fetch_assoc($result)) {
                            $statusClass = '';
                            switch($row['status']) {
                                case 'pending': $statusClass = 'warning'; break;
                                case 'confirmed': $statusClass = 'info'; break;
                                case 'completed': $statusClass = 'success'; break;
                                case 'cancelled': $statusClass = 'danger'; break;
                                default: $statusClass = 'secondary';
                            }
                            ?>
                            <tr>
                                <td>ORD-<?php echo $row['order_id']; ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
                                <td><span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($row['items']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info view-checklist" 
                                            data-id="<?php echo $row['order_id']; ?>"
                                            data-items="<?php echo htmlspecialchars($row['items']); ?>"
                                            data-notes="<?php echo htmlspecialchars($row['special_notes']); ?>">
                                        <i class="fas fa-tasks"></i> View Checklist
                                    </button>
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
</div>

<!-- Checklist Modal -->
<div class="modal fade" id="checklistModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Checklist</h5>
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
                        <h6>Ingredients Checklist</h6>
                        <div id="orderChecklist"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#checklistTable').DataTable({
        order: [[1, 'desc']]
    });

    // View Checklist
    $('.view-checklist').click(function() {
        const orderId = $(this).data('id');
        const items = $(this).data('items');
        const notes = $(this).data('notes');
        
        $('#orderItems').html(items.split(', ').map(item => `<div class="mb-2">${item}</div>`).join(''));
        $('#orderNotes').text(notes || 'No special notes');
        
        $('#orderChecklist').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading checklist...</div>');
        
        $.get('admin_get_checklist.php', { order_id: orderId })
            .done(function(response) {
                $('#orderChecklist').html(response);
            })
            .fail(function() {
                $('#orderChecklist').html('<p class="text-danger">Failed to load checklist</p>');
            });

        $('#checklistModal').modal('show');
    });

    // Update checklist item status
    $(document).on('change', '.checklist-checkbox', function() {
        const checkbox = $(this);
        const itemId = checkbox.data('id');
        const isChecked = checkbox.prop('checked');
        
        $.post('admin_update_checklist.php', {
            item_id: itemId,
            is_ready: isChecked ? 1 : 0
        })
        .done(function(response) {
            if (response !== 'success') {
                checkbox.prop('checked', !isChecked);
                alert('Failed to update: ' + response);
            }
        })
        .fail(function() {
            checkbox.prop('checked', !isChecked);
            alert('Update failed. Please try again.');
        });
    });
});
</script>