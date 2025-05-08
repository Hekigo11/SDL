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

                        <!-- Address Management Section -->
                        <div class="address-management mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4>My Addresses</h4>
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addAddressModal">
                                    <i class="fas fa-plus"></i> Add New Address
                                </button>
                            </div>
                            <div id="addressList" class="mt-3">
                                <!-- Addresses will be loaded here -->
                            </div>
                        </div>

                        <!-- Add/Edit Address Modal -->
                        <div class="modal fade" id="addAddressModal" tabindex="-1" role="dialog" aria-labelledby="addressModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addressModalLabel">Add New Address</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="addressForm">
                                            <input type="hidden" id="address_id" name="address_id">
                                            <div class="form-group">
                                                <label for="street_number">Street Number</label>
                                                <input type="text" class="form-control" id="street_number" name="street_number" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="street_name">Street Name</label>
                                                <input type="text" class="form-control" id="street_name" name="street_name" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="barangay">Barangay</label>
                                                <input type="text" class="form-control" id="barangay" name="barangay" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="city">City</label>
                                                <input type="text" class="form-control" id="city" name="city" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="province">Province</label>
                                                <input type="text" class="form-control" id="province" name="province" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="zip_code">ZIP Code</label>
                                                <input type="text" class="form-control" id="zip_code" name="zip_code" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="label">Label</label>
                                                <select class="form-control" id="label" name="label">
                                                    <option value="Home">Home</option>
                                                    <option value="Work">Work</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                            <div class="form-group" id="customLabelGroup" style="display: none;">
                                                <label for="customLabel">Custom Label</label>
                                                <input type="text" class="form-control" id="customLabel" name="customLabel" placeholder="Enter custom label">
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="is_default" name="is_default">
                                                <label class="form-check-label" for="is_default">Set as default address</label>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" id="saveAddress">Save Address</button>
                                    </div>
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

    function loadAddresses() {
        $.post("<?php echo BASE_URL; ?>/modules/manage_address.php", {
            action: "get"
        })
        .done(function(response) {
            if (response.success) {
                const addressList = $("#addressList");
                addressList.empty();
                
                if (response.addresses.length === 0) {
                    addressList.html('<p class="text-muted">No addresses added yet.</p>');
                    return;
                }

                response.addresses.forEach(function(address) {
                    const addressHtml = `
                        <div class="card mb-2">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            ${address.label}
                                            ${address.is_default == 1 ? '<span class="badge badge-primary">Default</span>' : ''}
                                        </h6>
                                        <p class="mb-1">${address.street_number} ${address.street_name}</p>
                                        <p class="mb-1">${address.barangay}</p>
                                        <p class="mb-1">${address.city}, ${address.province} ${address.zip_code}</p>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-info edit-address" data-address='${JSON.stringify(address)}'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-address" data-id="${address.address_id}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    addressList.append(addressHtml);
                });
            }
        });
    }

    $(document).ready(function() {
        // Load addresses when page loads
        loadAddresses();

        // Handle form submission
        $("#saveAddress").click(function() {
            const formData = {
                action: $("#address_id").val() ? "update" : "add",
                address_id: $("#address_id").val(),
                street_number: $("#street_number").val(),
                street_name: $("#street_name").val(),
                barangay: $("#barangay").val(),
                city: $("#city").val(),
                province: $("#province").val(),
                zip_code: $("#zip_code").val(),
                label: $("#label").val(),
                customLabel: $("#customLabel").val(),
                is_default: $("#is_default").prop("checked")
            };

            $.post("<?php echo BASE_URL; ?>/modules/manage_address.php", formData)
                .done(function(response) {
                    if (response.success) {
                        $("#addAddressModal").modal("hide");
                        $("#addressForm")[0].reset();
                        $("#address_id").val("");
                        showAlert(response.message, "success");
                        loadAddresses();
                    } else {
                        showAlert(response.message || "Error saving address", "danger");
                    }
                })
                .fail(function() {
                    showAlert("An error occurred. Please try again.", "danger");
                });
        });

        // Edit address
        $(document).on("click", ".edit-address", function() {
            const address = $(this).data("address");
            $("#address_id").val(address.address_id);
            $("#street_number").val(address.street_number);
            $("#street_name").val(address.street_name);
            $("#barangay").val(address.barangay);
            $("#city").val(address.city);
            $("#province").val(address.province);
            $("#zip_code").val(address.zip_code);
            $("#label").val(address.label);
            $("#customLabel").val(address.customLabel || "");
            $("#is_default").prop("checked", address.is_default == 1);
            $("#addressModalLabel").text("Edit Address");
            $("#addAddressModal").modal("show");
            toggleCustomLabelGroup();
        });

        // Delete address
        $(document).on("click", ".delete-address", function() {
            if (confirm("Are you sure you want to delete this address?")) {
                const addressId = $(this).data("id");
                $.post("<?php echo BASE_URL; ?>/modules/manage_address.php", {
                    action: "delete",
                    address_id: addressId
                })
                .done(function(response) {
                    if (response.success) {
                        showAlert(response.message, "success");
                        loadAddresses();
                    } else {
                        showAlert(response.message || "Error deleting address", "danger");
                    }
                })
                .fail(function() {
                    showAlert("An error occurred. Please try again.", "danger");
                });
            }
        });

        // Reset form when modal is closed
        $("#addAddressModal").on("hidden.bs.modal", function() {
            $("#addressForm")[0].reset();
            $("#address_id").val("");
            $("#addressModalLabel").text("Add New Address");
            toggleCustomLabelGroup();
        });

        // Toggle custom label input visibility
        $("#label").change(function() {
            toggleCustomLabelGroup();
        });

        function toggleCustomLabelGroup() {
            const labelValue = $("#label").val();
            if (labelValue === "Other") {
                $("#customLabelGroup").show();
            } else {
                $("#customLabelGroup").hide();
                $("#customLabel").val("");
            }
        }

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