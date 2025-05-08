<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loginok'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

include("dbconi.php");

// Fetch user data
$user_id = $_SESSION['user_id'];
$query = "SELECT fname, mname, lname, email_add, mobile_num FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($dbc, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - MARJ Food Services</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/vendor/style1.css">
</head>
<body>
    <?php include("navigation.php"); ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card" style="border-radius: 15px;">
                    <div class="card-header text-center" style="background-color: var(--accent); color: white; border-radius: 15px 15px 0 0;">
                        <h3>My Profile</h3>
                    </div>
                    <div class="card-body">
                        <div id="alert-container"></div>
                        
                        <!-- User Information -->
                        <div class="user-info mb-4">
                            <h4>Personal Information</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>First Name:</strong></p>
                                    <p class="text-muted"><?php echo htmlspecialchars($user_data['fname']); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Middle Name:</strong></p>
                                    <p class="text-muted"><?php echo htmlspecialchars($user_data['mname']); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Last Name:</strong></p>
                                    <p class="text-muted"><?php echo htmlspecialchars($user_data['lname']); ?></p>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <p><strong>Email:</strong></p>
                                    <p class="text-muted"><?php echo htmlspecialchars($user_data['email_add']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Mobile Number:</strong></p>
                                    <p class="text-muted"><?php echo htmlspecialchars($user_data['mobile_num']); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Change Password Section -->
                        <div class="change-password mt-4">
                            <h4>Change Password</h4>
                            <form id="changePasswordForm">
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Change Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('authenticate.php'); ?>

    <script>
    function showAlert(message, type) {
        const alertContainer = $("#alert-container");
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>`;
        alertContainer.html(alertHtml);
    }

    $(document).ready(function() {
        $("#changePasswordForm").on("submit", function(e) {
            e.preventDefault();
            
            const currentPassword = $("#current_password").val();
            const newPassword = $("#new_password").val();
            const confirmPassword = $("#confirm_password").val();

            if (!currentPassword || !newPassword || !confirmPassword) {
                showAlert("Please fill in all password fields", "warning");
                return;
            }

            if (newPassword !== confirmPassword) {
                showAlert("New passwords do not match", "warning");
                return;
            }

            $.post("<?php echo BASE_URL; ?>/modules/change_password.php", {
                current_password: currentPassword,
                new_password: newPassword
            })
            .done(function(response) {
                if (response.success) {
                    showAlert("Password changed successfully", "success");
                    $("#changePasswordForm")[0].reset();
                } else {
                    showAlert(response.message || "Failed to change password", "danger");
                }
            })
            .fail(function() {
                showAlert("An error occurred. Please try again.", "danger");
            });
        });
    });
    </script>
</body>
</html>