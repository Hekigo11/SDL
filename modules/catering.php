<?php
require_once __DIR__ . '/../config.php';
require_once 'dbconi.php';  // Add database connection include
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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/vendor/style1.css">
    <title>Catering Request - MARJ Food Services</title>
    <style>
        .packages-container {
            max-height: 300px;
            overflow-y: auto;
            padding: 15px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .package-card {
            border-radius: 15px;
            margin-bottom: 15px;
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .package-card .card-header {
            background-color: var(--accent);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .btn-accent {
            background-color: var(--accent);
            color: white;
            border-radius: 50px;
        }
        .btn-accent:hover {
            background-color: var(--accent-dark);
            color: white;
        }
        @media (max-width: 768px) {
            .packages-container {
                max-height: 400px;
            }
        }
        .bg-accent {
            background-color: var(--accent) !important;
        }
    </style>
</head>
<body>
    <?php include("navigation.php"); ?>
    
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
                    <div class="card-header bg-accent text-white rounded-top">
                        <h2 class="h4 mb-0">Catering Request Form</h2>
                    </div>
                    <div class="card-body">
                        

                        <form action="submit_catering.php" method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Full Name</label>
                                        <?php 
                                        // Get user details for autofill
                                        $user_query = "SELECT fname, mname, lname, email_add, mobile_num FROM users WHERE user_id = ?";
                                        $stmt = mysqli_prepare($dbc, $user_query);
                                        mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
                                        mysqli_stmt_execute($stmt);
                                        $user_result = mysqli_stmt_get_result($stmt);
                                        $user_data = mysqli_fetch_assoc($user_result);
                                        
                                        $full_name = trim($user_data['fname'] . ' ' . $user_data['mname'] . ' ' . $user_data['lname']);
                                        ?>
                                        <input type="text" class="form-control" name="client_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Contact Number</label>
                                        <input type="tel" class="form-control" name="contact_info" value="<?php echo htmlspecialchars($user_data['mobile_num']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user_data['email_add']); ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Event Date</label>
                                        <input type="datetime-local" class="form-control" name="event_date" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Number of Persons</label>
                                        <input type="number" class="form-control" name="num_persons" min="10" value="10" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Venue Address</label>
                                <textarea class="form-control" name="venue" rows="2" required></textarea>
                            </div>

                            <div class="form-group">
                                <label>Occasion</label>
                                <input type="text" class="form-control" name="occasion" required>
                            </div>

                            <div class="form-group">
                                <label>Payment Method</label>
                                <select class="form-control" name="payment_method" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="cash">Cash</option>
                                    <option value="gcash">GCash</option>
                                </select>
                            </div>

                            <div class="packages-container mb-4">
                            <h5 class="mb-3">Available Packages</h5>
                            <div class="row">
                                <?php
                                include("dbconi.php");
                                $query = "SELECT * FROM products WHERE prod_cat_id = 5 ORDER BY prod_price";
                                $result = mysqli_query($dbc, $query);
                                
                                if (mysqli_num_rows($result) > 0) {
                                    while($row = mysqli_fetch_assoc($result)) { ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="package-card card h-100">
                                                <div class="card-header">
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($row['prod_name']); ?></h6>
                                                </div>
                                                <div class="card-body">
                                                    <p class="card-text"><?php echo htmlspecialchars($row['prod_desc']); ?></p>
                                                    <p class="card-text font-weight-bold">₱<?php echo number_format($row['prod_price'], 2); ?> per head</p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }
                                } else { ?>
                                    <div class="col-12">
                                        <p class="text-muted">No packages available at the moment.</p>
                                    </div>
                                <?php }
                                ?>
                            </div>
                        </div>
                            <div class="form-group">
                                <label>Select Menu Package</label>
                                <select class="form-control" name="menu_bundle" required>
                                    <option value="">Choose a package</option>
                                    <?php
                                    mysqli_data_seek($result, 0);
                                    while($row = mysqli_fetch_assoc($result)) {
                                        echo "<option value='" . htmlspecialchars($row['prod_name']) . "'>" . 
                                             htmlspecialchars($row['prod_name']) . " - ₱" . number_format($row['prod_price'], 2) . "/head</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Additional Services Needed</label>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="setup" name="options[]" value="setup">
                                    <label class="custom-control-label" for="setup">Buffet Setup (₱2,000)</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="tables" name="options[]" value="tables">
                                    <label class="custom-control-label" for="tables">Tables and Chairs (₱3,500)</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="decoration" name="options[]" value="decoration">
                                    <label class="custom-control-label" for="decoration">Venue Decoration (₱5,000)</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Special Requirements/Requests</label>
                                <textarea class="form-control" name="other_requests" rows="3"></textarea>
                            </div>

                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="mb-3">Estimated Total</h5>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <p class="mb-2">Package Cost: <span id="packageCost">₱0.00</span></p>
                                            <p class="mb-2">Additional Services: <span id="servicesCost">₱0.00</span></p>
                                            <hr>
                                            <p class="font-weight-bold">Total Amount: <span id="totalAmount" class="text-primary">₱0.00</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-accent px-5">Submit Catering Request</button>
                            </div>

                            <script>
                            function calculateTotal() {
                                const numPersons = parseInt(document.querySelector('input[name="num_persons"]').value) || 0;
                                const menuBundle = document.querySelector('select[name="menu_bundle"]').value;
                                let total = 0;
                                let packageCost = 0;
                                let servicesCost = 0;

                                // Calculate package cost
                                switch(menuBundle) {
                                    case 'Basic Filipino Package':
                                        packageCost = numPersons * 250;
                                        break;
                                    case 'Premium Filipino Package':
                                        packageCost = numPersons * 450;
                                        break;
                                    case 'Executive Package':
                                        packageCost = numPersons * 650;
                                        break;
                                }

                                // Calculate additional services
                                const services = document.querySelectorAll('input[name="options[]"]:checked');
                                services.forEach(service => {
                                    switch(service.value) {
                                        case 'setup':
                                            servicesCost += 2000;
                                            break;
                                        case 'tables':
                                            servicesCost += 3500;
                                            break;
                                        case 'decoration':
                                            servicesCost += 5000;
                                            break;
                                    }
                                });

                                total = packageCost + servicesCost;

                                // Update display
                                document.getElementById('packageCost').textContent = '₱' + packageCost.toFixed(2);
                                document.getElementById('servicesCost').textContent = '₱' + servicesCost.toFixed(2);
                                document.getElementById('totalAmount').textContent = '₱' + total.toFixed(2);
                            }

                            // Add event listeners
                            document.querySelector('input[name="num_persons"]').addEventListener('input', calculateTotal);
                            document.querySelector('select[name="menu_bundle"]').addEventListener('change', calculateTotal);
                            document.querySelectorAll('input[name="options[]"]').forEach(checkbox => {
                                checkbox.addEventListener('change', calculateTotal);
                            });

                            // Reset form if submission was successful
                            <?php if(isset($_SESSION['success'])): ?>
                                document.querySelector('form').reset();
                                calculateTotal(); // Reset the total amount display
                            <?php endif; ?>
                            </script>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include('authenticate.php'); ?>
</body>
</html>