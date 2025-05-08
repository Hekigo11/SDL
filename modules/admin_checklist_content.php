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
                                 o.notes as special_notes,
                                 (SELECT COUNT(*) FROM order_checklist oc WHERE oc.order_id = o.order_id) as total_items,
                                 (SELECT COUNT(*) FROM order_checklist oc WHERE oc.order_id = o.order_id AND oc.is_ready = 1) as completed_items
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
                            
                            $progress = $row['total_items'] > 0 ? 
                                      round(($row['completed_items'] / $row['total_items']) * 100) : 0;
                            ?>
                            <tr data-order-id="<?php echo $row['order_id']; ?>">
                                <td>ORD-<?php echo $row['order_id']; ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $statusClass; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                    <?php if ($row['total_items'] > 0): ?>
                                        <div class="progress mt-1" style="height: 5px;">
                                            <div class="progress-bar bg-<?php echo $statusClass; ?>" 
                                                 role="progressbar" 
                                                 style="width: <?php echo $progress; ?>%" 
                                                 aria-valuenow="<?php echo $progress; ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="text-muted" data-progress="<?php echo $row['order_id']; ?>">
                                            <?php echo $row['completed_items']; ?>/<?php echo $row['total_items']; ?> items ready
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['items']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info view-checklist" 
                                            data-id="<?php echo $row['order_id']; ?>"
                                            data-items="<?php echo htmlspecialchars($row['items']); ?>"
                                            data-notes="<?php echo htmlspecialchars($row['special_notes']); ?>"
                                            <?php echo ($row['status'] === 'cancelled' ? 'disabled' : ''); ?>>
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
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-shopping-cart"></i> Order Items</h6>
                                <div id="orderItems" class="mb-4"></div>
                                
                                <h6 class="card-title mt-3"><i class="fas fa-sticky-note"></i> Special Notes</h6>
                                <p id="orderNotes" class="text-muted"></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-clipboard-check"></i> Ingredients Checklist</h6>
                                <div id="orderChecklist"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>