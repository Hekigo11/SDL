<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
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

    // Get current order status and customer email
    $query = "SELECT o.status, o.user_id, u.email_add 
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

    // Update order status
    $update_query = "UPDATE orders SET status = ? WHERE order_id = ?";
    $update_stmt = mysqli_prepare($dbc, $update_query);
    mysqli_stmt_bind_param($update_stmt, "si", $new_status, $order_id);

    if (!mysqli_stmt_execute($update_stmt)) {
        throw new Exception("Failed to update order status");
    }

    // Send email notification if status changed
    if ($order['status'] !== $new_status) {
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
                        <p>Your order #' . $order_id . ' has been updated to: <span class="status">' . ucfirst($new_status) . '</span></p>';
            
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