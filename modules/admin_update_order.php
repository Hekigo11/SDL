<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {    
    http_response_code(403);
    echo 'Unauthorized access';
    exit;
}

include("dbconi.php");

if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
    http_response_code(400);
    echo 'Missing required fields';
    exit;
}

try {
    mysqli_begin_transaction($dbc);

    $order_id = intval($_POST['order_id']);
    $new_status = mysqli_real_escape_string($dbc, $_POST['status']);
    $status_notes = isset($_POST['status_notes']) ? mysqli_real_escape_string($dbc, $_POST['status_notes']) : '';
    // Add support for delivery_tracking_link (Lalamove link)
    $delivery_tracking_link = isset($_POST['delivery_tracking_link']) ? mysqli_real_escape_string($dbc, $_POST['delivery_tracking_link']) : '';

    // Get current order status and customer email
    $query = "SELECT o.status, o.user_id, u.email_add, o.payment_status, o.payment_method 
              FROM orders o
              JOIN users u ON o.user_id = u.user_id
              WHERE o.order_id = ?";
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);

    if (!$order) {
        throw new Exception("Order not found");
    }

    // Prevent going back to pending if order is already processing or beyond
    if ($new_status === 'pending' && $order['status'] !== 'pending') {
        throw new Exception("Cannot revert order back to pending status once processing has started");
    }

    // If marking as completed and it's a cash order that is unpaid, set payment_status to paid
    $update_payment_status = false;
    if ($new_status === 'completed' && $order['payment_method'] === 'cash' && $order['payment_status'] !== 'paid') {
        $update_payment_status = true;
    }

    // Update order status and timestamps
    $update_query = "UPDATE orders SET 
                    status = ?,
                    payment_status = IF(" . (int)$update_payment_status . ", 'paid', payment_status),
                    delivery_started_at = CASE 
                        WHEN ? = 'delivering' AND status != 'delivering' THEN NOW()
                        WHEN ? != 'delivering' THEN NULL
                        ELSE delivery_started_at
                    END,
                    delivered_at = CASE 
                        WHEN ? = 'completed' AND status != 'completed' THEN NOW()
                        WHEN ? != 'completed' THEN NULL
                        ELSE delivered_at
                    END,
                    cancelled_at = CASE 
                        WHEN ? = 'cancelled' AND status != 'cancelled' THEN NOW()
                        WHEN ? != 'cancelled' THEN NULL
                        ELSE cancelled_at
                    END,
                    status_notes = ?,
                    status_updates = CONCAT(COALESCE(status_updates, ''), IF(LENGTH(COALESCE(status_updates, '')) > 0, '\n', ''), '[', DATE_FORMAT(NOW(), '%M %e, %Y %l:%i %p'), '] ', ?, ' (', ? ,')'),
                    delivery_tracking_link = ?
                    WHERE order_id = ?";
    
    $update_stmt = mysqli_prepare($dbc, $update_query);
    mysqli_stmt_bind_param($update_stmt, "sssssssssssi", 
        $new_status,
        $new_status,
        $new_status,
        $new_status,
        $new_status,
        $new_status,
        $new_status,
        $status_notes,
        $status_notes,
        $new_status,
        $delivery_tracking_link,
        $order_id
    );

    if (!mysqli_stmt_execute($update_stmt)) {
        throw new Exception("Failed to update order status");
    }

    // Send email notification if status changed
    if ($order['status'] !== $new_status) {
        // Only send emails for specific statuses
        $notify_statuses = ['delivering', 'completed', 'delivered'];
        
        if (in_array($new_status, $notify_statuses)) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp-relay.brevo.com';
                $mail->SMTPAuth = true;
                $mail->Username = '89af28001@smtp-brevo.com';
                $mail->Password = '2SgtjUQrEsRHwA0M';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->Timeout = 30;
                $mail->CharSet = 'UTF-8';
    
                $mail->setFrom('cateringservices69420@gmail.com', 'MARJ Food Services');
                $mail->addAddress($order['email_add']);
    
                $mail->isHTML(true);
                $mail->Subject = 'Order #' . $order_id . ' Status Update';
                
                // Customized message based on status
                $status_message = '';
                switch($new_status) {
                    case 'delivering':
                        $status_message = 'Your order is now on the way to your location!';
                        break;
                    case 'completed':
                        $status_message = 'Your order has been completed. We hope you enjoyed your meal!';
                        break;
                    case 'delivered':
                        $status_message = 'Your order has been delivered. Enjoy your meal!';
                        break;
                }
                
                $message = '
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body {
                            font-family: "Montserrat", Arial, sans-serif;
                            margin: 0;
                            padding: 0;
                            background-color: #f5f5f5;
                        }
                        .container {
                            max-width: 600px;
                            margin: 20px auto;
                            background-color: #ffffff;
                            border-radius: 15px;
                            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                            padding: 30px;
                        }
                        .header {
                            background-color: #21233f;
                            color: #ffffff;
                            padding: 20px;
                            text-align: center;
                            border-radius: 10px 10px 0 0;
                            margin: -30px -30px 20px -30px;
                        }
                        .content {
                            padding: 20px 0;
                        }
                        h1 {
                            color: #ffffff;
                            font-size: 24px;
                            margin: 0;
                        }
                        h2 {
                            color: #21233f;
                            font-size: 20px;
                            margin-bottom: 15px;
                        }
                        p {
                            color: #666666;
                            line-height: 1.6;
                            margin-bottom: 15px;
                        }
                        .status {
                            background-color: #176ca1;
                            color: #ffffff;
                            padding: 8px 16px;
                            border-radius: 25px;
                            display: inline-block;
                            font-weight: bold;
                        }
                        .notes {
                            background-color: #feebd2;
                            padding: 15px;
                            border-radius: 10px;
                            margin: 15px 0;
                        }
                        .footer {
                            text-align: center;
                            padding-top: 20px;
                            border-top: 1px solid #eeeeee;
                            margin-top: 20px;
                            color: #888888;
                            font-size: 14px;
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="header">
                            <h1>MARJ Food Services</h1>
                        </div>
                        <div class="content">
                            <h2>Order Status Update</h2>
                            <p>Your order #' . $order_id . ' has been updated to: <span class="status">' . ucfirst($new_status) . '</span></p>
                            <p>' . $status_message . '</p>';
                
                if ($status_notes) {
                    $message .= '
                            <div class="notes">
                                <strong>Notes from our team:</strong><br>
                                ' . nl2br(htmlspecialchars($status_notes)) . '
                            </div>';
                }
                
                $message .= '
                            <p>If you have any questions about your order, please don\'t hesitate to contact us.</p>
                        </div>
                        <div class="footer">
                            <p>Thank you for choosing MARJ Food Services!</p>
                            <small>From Our Kitchen to Your Table – With Love.</small>
                        </div>
                    </div>
                </body>
                </html>';
                
                $mail->Body = $message;
                $mail->send();
            } catch (Exception $e) {
                error_log("Email sending failed: " . $e->getMessage());
                // Continue even if email fails
            }
        }
    }

    mysqli_commit($dbc);
    echo 'success';

} catch (Exception $e) {
    mysqli_rollback($dbc);
    http_response_code(500);
    echo $e->getMessage();
} finally {
    mysqli_close($dbc);
}
?>