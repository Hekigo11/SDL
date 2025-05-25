<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../dbconi.php';
session_start();

if (!isset($_SESSION['loginok'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Store form data in session if coming from POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['catering_form'] = [
        'client_name' => $_POST['client_name'] ?? '',
        'contact_info' => $_POST['contact_info'] ?? '',
        'email' => $_POST['email'] ?? '',
        'event_date' => $_POST['event_date'] ?? '',
        'event_time' => $_POST['event_time'] ?? '',
        'venue_street_number' => $_POST['venue_street_number'] ?? '',
        'venue_street_name' => $_POST['venue_street_name'] ?? '',
        'venue_barangay' => $_POST['venue_barangay'] ?? '',
        'venue_city' => $_POST['venue_city'] ?? '',
        'venue_province' => $_POST['venue_province'] ?? '',
        'venue_zip' => $_POST['venue_zip'] ?? '',
        'venue_details' => $_POST['venue_details'] ?? '',
        'occasion' => $_POST['occasion'] ?? '',
        'num_persons' => $_POST['num_persons'] ?? '',
        'menu_bundle' => $_POST['menu_bundle'] ?? ''
    ];
}
if (!isset($_SESSION['catering_form']) && isset($_SESSION['catering_step1'])) {
    $_SESSION['catering_form'] = $_SESSION['catering_step1'];
}

// Get user details for autofill
$user_query = "SELECT fname, mname, lname, email_add, mobile_num FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($dbc, $user_query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($user_result);
$full_name = trim($user_data['fname'] . ' ' . $user_data['mname'] . ' ' . $user_data['lname']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/vendor/style1.css">

    <title>Catering Request - Step 1 - MARJ Food Services</title>
</head>
<body>
    <?php include("../navigation.php"); ?>
    
    <main class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <div class="card shadow">
                    <div class="card-header  text-white rounded-top" style="background-color: var(--accent);">
                        <h2 class="h4 mb-0">Catering Request - Step 1</h2>
                    </div>                    <div class="card-body">
                        <div class="progress mb-4">
                            <div class="progress-bar bg-accent" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">Step 1 of 2</div>
                        </div>

                        <form action="step2.php" method="POST" id="cateringForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Full Name</label>
                                        <input type="text" class="form-control" name="client_name" 
                                            value="<?php echo isset($_SESSION['catering_form']['client_name']) ? htmlspecialchars($_SESSION['catering_form']['client_name']) : htmlspecialchars($full_name); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Contact Number</label>
                                        <input type="tel" class="form-control" name="contact_info" 
                                            value="<?php echo isset($_SESSION['catering_form']['contact_info']) ? htmlspecialchars($_SESSION['catering_form']['contact_info']) : htmlspecialchars($user_data['mobile_num']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" class="form-control" name="email" 
                                    value="<?php echo isset($_SESSION['catering_form']['email']) ? htmlspecialchars($_SESSION['catering_form']['email']) : htmlspecialchars($user_data['email_add']); ?>" required>
                            </div>

                            <?php include('date_selection.php'); ?>

                            <div class="form-group">
                                <label><strong>Venue Address</strong></label>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="venue_street_number">Street Number</label>
                                                    <input type="text" class="form-control" id="venue_street_number" name="venue_street_number" 
                                                        value="<?php echo isset($_SESSION['catering_form']['venue_street_number']) ? htmlspecialchars($_SESSION['catering_form']['venue_street_number']) : ''; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="venue_street_name">Street Name</label>
                                                    <input type="text" class="form-control" id="venue_street_name" name="venue_street_name" 
                                                        value="<?php echo isset($_SESSION['catering_form']['venue_street_name']) ? htmlspecialchars($_SESSION['catering_form']['venue_street_name']) : ''; ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="venue_barangay">Barangay</label>
                                                    <input type="text" class="form-control" id="venue_barangay" name="venue_barangay" 
                                                        value="<?php echo isset($_SESSION['catering_form']['venue_barangay']) ? htmlspecialchars($_SESSION['catering_form']['venue_barangay']) : ''; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="venue_city">City/Municipality</label>
                                                    <input type="text" class="form-control" id="venue_city" name="venue_city" 
                                                        value="<?php echo isset($_SESSION['catering_form']['venue_city']) ? htmlspecialchars($_SESSION['catering_form']['venue_city']) : ''; ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="venue_province">Province</label>
                                                    <input type="text" class="form-control" id="venue_province" name="venue_province" 
                                                        value="<?php echo isset($_SESSION['catering_form']['venue_province']) ? htmlspecialchars($_SESSION['catering_form']['venue_province']) : ''; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="venue_zip">ZIP Code</label>
                                                    <input type="text" class="form-control" id="venue_zip" name="venue_zip" 
                                                        value="<?php echo isset($_SESSION['catering_form']['venue_zip']) ? htmlspecialchars($_SESSION['catering_form']['venue_zip']) : ''; ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="venue_details">Venue Details <small class="text-muted">(Room/Floor/Building/Landmark)</small></label>
                                            <textarea class="form-control" id="venue_details" name="venue_details" rows="2"><?php echo isset($_SESSION['catering_form']['venue_details']) ? htmlspecialchars($_SESSION['catering_form']['venue_details']) : ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Occasion</label>
                                <input type="text" class="form-control" name="occasion" 
                                    value="<?php echo isset($_SESSION['catering_form']['occasion']) ? htmlspecialchars($_SESSION['catering_form']['occasion']) : ''; ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Number of Persons</label>
                                <input type="number" class="form-control" name="num_persons" id="num_persons" min="1" 
                                    value="<?php echo isset($_SESSION['catering_form']['num_persons']) ? htmlspecialchars($_SESSION['catering_form']['num_persons']) : '50'; ?>" required>
                                <small class="form-text text-muted">Minimum 50 persons for standard packages. For smaller groups, select "Custom Package" or our staff will contact you.</small>
                                <small id="smallGroupWarning" class="form-text text-danger font-weight-bold" style="display: none;">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    You've selected fewer than 50 persons. This will require special handling by our staff.
                                </small>
                            </div>

                            <div class="form-group">
                                <label>Select Menu Package</label>
                                <div class="packages-container">
                                    <div class="row">
                                        <?php
                                        $query = "SELECT * FROM packages WHERE is_active = 1 ORDER BY base_price";
                                        $result = mysqli_query($dbc, $query);
                                        
                                        if (mysqli_num_rows($result) > 0) {
                                            while($row = mysqli_fetch_assoc($result)) { ?>
                                                <div class="col-md-12 mb-3">
                                                    <div class="package-card card">
                                                        <div class="card-body">
                                                            <div class="custom-control custom-radio">
                                                                <input type="radio" id="package_<?php echo $row['package_id']; ?>" 
                                                                       name="menu_bundle" 
                                                                       value="<?php echo htmlspecialchars($row['name']); ?>" 
                                                                       class="custom-control-input package-select"
                                                                       data-price="<?php echo $row['base_price']; ?>"
                                                                       <?php echo (isset($_SESSION['catering_form']['menu_bundle']) && $_SESSION['catering_form']['menu_bundle'] === $row['name']) ? 'checked' : ''; ?>
                                                                       required>
                                                                <label class="custom-control-label font-weight-bold" for="package_<?php echo $row['package_id']; ?>">
                                                                    <?php echo htmlspecialchars($row['name']); ?> - ₱<?php echo number_format($row['base_price'], 2); ?> per head
                                                                </label>
                                                            </div>
                                                            <p class="card-text ml-4 mt-2"><?php echo htmlspecialchars($row['description']); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php }
                                            ?>
                                            
                                        <?php } else { ?>
                                            <div class="col-12">
                                                <p class="text-muted">No pre-made packages available at the moment.</p>
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-12 mb-3">
                                            <div class="package-card card">
                                                <div class="card-body">
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="package_custom" 
                                                            name="menu_bundle" 
                                                            value="Custom Package" 
                                                            class="custom-control-input package-select"
                                                            data-custom="true"
                                                            data-price="0"
                                                            <?php echo (isset($_SESSION['catering_form']['menu_bundle']) && $_SESSION['catering_form']['menu_bundle'] === 'Custom Package') ? 'checked' : ''; ?>>
                                                            <label class="custom-control-label font-weight-bold" for="package_custom">
                                                            Custom Package - Contact us for pricing
                                                            </label>
                                                    </div>
                                                        <p class="card-text ml-4 mt-2">Request a custom menu tailored to your needs. Our staff will contact you to discuss options.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="mb-3">Estimated Total</h5>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <p class="mb-2">Package Cost: <span id="packageCost">₱0.00</span></p>
                                            <hr>
                                            <p class="font-weight-bold">Total Amount: <span id="totalAmount" class="text-primary">₱0.00</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Custom Package / Small Group Modal -->
                            <div class="modal fade" id="customRequestModal" tabindex="-1" role="dialog" aria-labelledby="customRequestModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header bg-accent text-white" style="background-color: var(--accent) !important;">
                                            <h5 class="modal-title" id="customRequestModalLabel">Special Request</h5>
                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p id="customRequestMessage">For custom menu packages or events with less than 50 persons, please contact our staff directly for personalized assistance.</p>
                                            
                                            <div class="mt-4">
                                                <p>Contact Information:</p>
                                                <p><i class="fas fa-phone mr-2"></i> (02) 8123-4567</p>
                                                <p><i class="fas fa-envelope mr-2"></i> catering@marjfoodservices.com</p>
                                            </div>
                                            
                                            <div class="form-check mt-3">
                                                <input class="form-check-input" type="checkbox" id="proceedAnywayCheck">
                                                <label class="form-check-label" for="proceedAnywayCheck">
                                                    I understand and would like to submit this request anyway
                                                </label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="button" id="proceedAnywayBtn" class="btn btn-accent" disabled>Proceed with Request</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-accent px-5" id="continueBtn">
                                    Continue to Additional Services <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle form submission
        document.getElementById('cateringForm').addEventListener('submit', function(e) {
            const selectedPackage = document.querySelector('input[name="menu_bundle"]:checked');
            if (!selectedPackage) {
                e.preventDefault();
                alert('Please select a package before continuing.');
                return;
            }
            // if (selectedPackage.value === 'Custom Package') {
            //     e.preventDefault();
            //     this.action = '../submit_catering.php';
            //     this.submit();
            // }
        });
    });
    </script>
    <script src="scripts.js"></script>
    <?php include('../authenticate.php'); ?>
</body>
</html>