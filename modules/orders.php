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
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <!-- Popper JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
        <!-- Latest compiled JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/vendor/style1.css">
        <title>My Orders - MARJ Food Delivery</title>
    </head>
    <body>
        <?php include("navigation.php"); ?>
        
        <main class="container py-5">
            <h1 class="mb-4">My Orders</h1>
            
            <div class="table-responsive">
                <table class="table">
                    <thead class="thead-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Status</th>
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
                                        $statusClass = 'info';
                                        break;
                                    case 'completed':
                                        $statusClass = 'success';
                                        break;
                                    default:
                                        $statusClass = 'secondary';
                                }
                                ?>
                                <tr>
                                    <td>#<?php echo $row['order_id']; ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
                                    <td><?php echo $row['items']; ?></td>
                                    <td>₱<?php echo number_format($row['total_amount'], 2); ?></td>
                                    <td><span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">You haven't placed any orders yet.</p>
                                    <a href="<?php echo BASE_URL; ?>/modules/products.php" class="btn btn-primary">Browse Products</a>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
        <!-- LOGIN MODAL -->
    <div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="margin: 25vh auto;" role="document">
            <div class="modal-content" style="border-radius: 30px;">
                <div class="modal-header text-center position-center" style="background-color:var(--accent); border-radius: 30px 30px 0 0;">
                    <h5 class="modal-title text-light" id="loginModalLabel">Login</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php include("login.php"); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- LOGOUT MODAL -->
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