<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {    
    http_response_code(403);
    echo 'Unauthorized access';
    exit;
}

include("dbconi.php");

if (!isset($_POST['order_id']) || !isset($_POST['status']) || !isset($_POST['order_type'])) {
    http_response_code(400);
    echo 'Missing required fields';
    exit;
}

try {
    mysqli_begin_transaction($dbc);

    $order_id = intval($_POST['order_id']);
    $new_status = mysqli_real_escape_string($dbc, $_POST['status']);
    $status_notes = isset($_POST['status_notes']) ? mysqli_real_escape_string($dbc, $_POST['status_notes']) : '';
    $is_standard = $_POST['order_type'] === 'standard';

    // Get current order status and customer email
    $table = $is_standard ? 'catering_orders' : 'custom_catering_orders';
    $id_field = $is_standard ? 'catering_id' : 'custom_order_id';
    
    $current_status_query = "SELECT o.status, o.email, o.full_name, o.event_date 
                            FROM $table o
                            WHERE o.$id_field = ?";
    
    $stmt = mysqli_prepare($dbc, $current_status_query);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        throw new Exception("Order not found");
    }

    $order = mysqli_fetch_assoc($result);
    
    if ($order['status'] === $new_status && empty($status_notes)) {
        echo 'No changes to update';
        exit;
    }

    // Get status message based on new status
    $status_message = '';
    switch($new_status) {
        case 'confirmed':
            $status_message = 'Your catering request has been confirmed. Our team will start preparing for your event.';
            break;
        case 'completed':
            $status_message = 'Your catering service has been completed. We hope everything was to your satisfaction!';
            break;
        case 'cancelled':
            $status_message = 'Your catering request has been cancelled. If you have any questions, please contact us.';
            break;
        default:
            $status_message = 'Your catering request status has been updated.';
    }

    // Update the order status
    $update_query = "UPDATE $table 
                    SET status = ?, 
                        staff_notes = CONCAT(COALESCE(staff_notes, ''), 
                            IF(LENGTH(COALESCE(staff_notes, '')) > 0, '\n', ''),
                            '[', NOW(), '] ', ?)
                    WHERE $id_field = ?";
                    
    $stmt = mysqli_prepare($dbc, $update_query);
    mysqli_stmt_bind_param($stmt, "ssi", $new_status, $status_notes, $order_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to update order status");
    }

    // Send email notification
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'deliverysystem72@gmail.com';
        $mail->Password = 'ckoz cvrw vyuj hine';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('deliverysystem72@gmail.com', 'MARJ Food Services');
        $mail->addAddress($order['email']);
        
        $mail->isHTML(true);
        $mail->Subject = "Catering Order Status Update - " . ($is_standard ? "CTR-" : "CSP-") . $order_id;
        
        $event_date = new DateTime($order['event_date']);
        $formatted_event_date = $event_date->format('F j, Y g:i A');

        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                }
                h1 {
                    color: #176ca1;
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
                    <p>Dear ' . htmlspecialchars($order['full_name']) . ',</p>
                    <h2>Catering Order Status Update</h2>
                    <p>Your catering order #' . ($is_standard ? "CTR-" : "CSP-") . $order_id . ' for ' . $formatted_event_date . ' has been updated to: <span class="status">' . ucfirst($new_status) . '</span></p>
                    <p>' . $status_message . '</p>';
        
        if ($status_notes) {
            $message .= '
                    <div class="notes">
                        <strong>Notes from our team:</strong><br>
                        ' . nl2br(htmlspecialchars($status_notes)) . '
                    </div>';
        }
        
        $message .= '
                    <p>If you have any questions about your catering order, please don\'t hesitate to contact us.</p>
                </div>
                <div class="footer">
                    <p>Thank you for choosing MARJ Food Services!</p>
                    <small>From Our Kitchen to Your Event – With Excellence.</small>
                </div>
            </div>
        </body>
        </html>';
        
        $mail->Body = $message;
        $mail->send();
    } catch (Exception $e) {
        // Log email error but don't stop the status update
        error_log("Email sending failed: " . $e->getMessage());
    }

    mysqli_commit($dbc);
    echo "success";

} catch (Exception $e) {
    mysqli_rollback($dbc);
    error_log("Error updating catering order: " . $e->getMessage());
    http_response_code(500);
    echo $e->getMessage();
}
