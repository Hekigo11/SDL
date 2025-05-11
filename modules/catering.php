<?php
require_once __DIR__ . '/../config.php';
require_once 'dbconi.php';
session_start();

if (!isset($_SESSION['loginok'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Redirect to the new step1.php
header('Location: ' . BASE_URL . '/modules/catering/step1.php');
exit;
?>
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
            color: white !important;
        }
        @media (max-width: 768px) {
            .packages-container {
                max-height: 400px;
            }
        }
        .bg-accent {
            background-color: var(--accent) !important;
        }
        .btn:hover {
            color: white !important;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
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

                            <div class="form-group mb-4">
                                <label><strong>Event Date & Time Selection</strong></label>
                                
                                <div class="card">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Select Event Date Type</label>
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="event_date_soon" name="event_date_type" value="next_available" class="custom-control-input" checked>
                                                <label class="custom-control-label" for="event_date_soon">Next Available Date <small class="text-muted">(Based on group size)</small></label>
                                            </div>
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="event_date_specific" name="event_date_type" value="specific_date" class="custom-control-input">
                                                <label class="custom-control-label" for="event_date_specific">Select Specific Date</label>
                                            </div>
                                        </div>
                                        
                                        <!-- Next Available Date Options -->
                                        <div id="next_available_container" class="mt-3">
                                            <?php 
                                            $today = new DateTime('today');
                                            $min_date = clone $today;
                                            $min_date->modify('+4 days'); // Default 3 days advance (4 days from today)
                                            $sug_dates = [];
                                            
                                            // Generate 5 suggested dates starting from earliest available
                                            for ($i = 0; $i < 5; $i++) {
                                                $date = clone $min_date;
                                                $date->modify("+$i days");
                                                $sug_dates[] = $date;
                                            }
                                            ?>
                                            
                                            <label class="mb-2">Recommended Dates:</label>
                                            <div class="row">
                                                <?php foreach ($sug_dates as $idx => $date): ?>
                                                <div class="col-md-6 mb-2">
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="quick_date_<?php echo $idx; ?>" 
                                                            name="quick_date" 
                                                            value="<?php echo $date->format('Y-m-d'); ?>" 
                                                            class="custom-control-input"
                                                            <?php echo ($idx === 0) ? 'checked' : ''; ?>>
                                                        <label class="custom-control-label" for="quick_date_<?php echo $idx; ?>">
                                                            <strong><?php echo $date->format('l, F j, Y'); ?></strong>
                                                        </label>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            
                                            <div class="form-group mt-3">
                                                <label>Event Time</label>
                                                <select class="form-control" name="quick_event_time" id="quick_event_time">
                                                    <option value="09:00">9:00 AM</option>
                                                    <option value="10:00">10:00 AM</option>
                                                    <option value="11:00">11:00 AM</option>
                                                    <option value="12:00">12:00 PM</option>
                                                    <option value="13:00" selected>1:00 PM</option>
                                                    <option value="14:00">2:00 PM</option>
                                                    <option value="15:00">3:00 PM</option>
                                                    <option value="16:00">4:00 PM</option>
                                                    <option value="17:00">5:00 PM</option>
                                                    <option value="18:00">6:00 PM</option>
                                                    <option value="19:00">7:00 PM</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <!-- Specific Date Selection -->
                                        <div id="specific_date_container" class="mt-3" style="display:none;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Event Date</label>
                                                        <?php 
                                                        $min_date_str = $min_date->format('Y-m-d');
                                                        
                                                        $max_date = clone $today;
                                                        $max_date->modify('+3 months');
                                                        $max_date_str = $max_date->format('Y-m-d');
                                                        ?>
                                                        <input type="date" 
                                                            class="form-control" 
                                                            name="event_date" 
                                                            id="event_date"
                                                            min="<?php echo $min_date_str; ?>"
                                                            max="<?php echo $max_date_str; ?>">
                                                        <small class="form-text text-muted" id="dateHelperText">
                                                            Please book at least 3 days in advance (14 days for 100+ persons).
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Event Time</label>
                                                        <select class="form-control" name="event_time" id="event_time">
                                                            <option value="08:00">8:00 AM</option>
                                                            <option value="09:00">9:00 AM</option>
                                                            <option value="10:00">10:00 AM</option>
                                                            <option value="11:00">11:00 AM</option>
                                                            <option value="12:00">12:00 PM</option>
                                                            <option value="13:00" selected>1:00 PM</option>
                                                            <option value="14:00">2:00 PM</option>
                                                            <option value="15:00">3:00 PM</option>
                                                            <option value="16:00">4:00 PM</option>
                                                            <option value="17:00">5:00 PM</option>
                                                            <option value="18:00">6:00 PM</option>
                                                            <option value="19:00">7:00 PM</option>
                                                            <option value="20:00">8:00 PM</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label><strong>Venue Address</strong></label>
                                
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="venue_street_number">Street Number</label>
                                                    <input type="text" class="form-control" id="venue_street_number" name="venue_street_number" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="venue_street_name">Street Name</label>
                                                    <input type="text" class="form-control" id="venue_street_name" name="venue_street_name" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="venue_barangay">Barangay</label>
                                                    <input type="text" class="form-control" id="venue_barangay" name="venue_barangay" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="venue_city">City/Municipality</label>
                                                    <input type="text" class="form-control" id="venue_city" name="venue_city" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="venue_province">Province</label>
                                                    <input type="text" class="form-control" id="venue_province" name="venue_province" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="venue_zip">ZIP Code</label>
                                                    <input type="text" class="form-control" id="venue_zip" name="venue_zip" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="venue_details">Venue Details <small class="text-muted">(Room/Floor/Building/Landmark)</small></label>
                                            <textarea class="form-control" id="venue_details" name="venue_details" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
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

                            <div class="form-group">
                                <label>Number of Persons</label>
                                <input type="number" class="form-control" name="num_persons" id="num_persons" min="1" value="50" required>
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
                                        include_once("dbconi.php");
                                        $query = "SELECT * FROM products WHERE prod_cat_id = 5 ORDER BY prod_price";
                                        $result = mysqli_query($dbc, $query);
                                        
                                        if (mysqli_num_rows($result) > 0) {
                                            while($row = mysqli_fetch_assoc($result)) { ?>
                                                <div class="col-md-12 mb-3">
                                                    <div class="package-card card">
                                                        <div class="card-body">
                                                            <div class="custom-control custom-radio">
                                                                <input type="radio" id="package_<?php echo $row['product_id']; ?>" 
                                                                       name="menu_bundle" 
                                                                       value="<?php echo htmlspecialchars($row['prod_name']); ?>" 
                                                                       class="custom-control-input package-select"
                                                                       data-price="<?php echo $row['prod_price']; ?>"
                                                                       required>
                                                                <label class="custom-control-label font-weight-bold" for="package_<?php echo $row['product_id']; ?>">
                                                                    <?php echo htmlspecialchars($row['prod_name']); ?> - ₱<?php echo number_format($row['prod_price'], 2); ?> per head
                                                                </label>
                                                            </div>
                                                            <p class="card-text ml-4 mt-2"><?php echo htmlspecialchars($row['prod_desc']); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php }
                                            // Add custom package option
                                            ?>
                                            <div class="col-md-12 mb-3">
                                                <div class="package-card card">
                                                    <div class="card-body">
                                                        <div class="custom-control custom-radio">
                                                            <input type="radio" id="package_custom" 
                                                                   name="menu_bundle" 
                                                                   value="Custom Package" 
                                                                   class="custom-control-input package-select"
                                                                   data-custom="true"
                                                                   data-price="0">
                                                            <label class="custom-control-label font-weight-bold" for="package_custom">
                                                                Custom Package - Contact us for pricing
                                                            </label>
                                                        </div>
                                                        <p class="card-text ml-4 mt-2">Request a custom menu tailored to your needs. Our staff will contact you to discuss options.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } else { ?>
                                            <div class="col-12">
                                                <p class="text-muted">No packages available at the moment.</p>
                                            </div>
                                        <?php }
                                        ?>
                                    </div>
                                </div>
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
                            // Date Selection Toggle between Next Available and Specific Date
                            document.getElementById('event_date_soon').addEventListener('change', function() {
                                if(this.checked) {
                                    document.getElementById('next_available_container').style.display = 'block';
                                    document.getElementById('specific_date_container').style.display = 'none';
                                }
                            });
                            
                            document.getElementById('event_date_specific').addEventListener('change', function() {
                                if(this.checked) {
                                    document.getElementById('next_available_container').style.display = 'none';
                                    document.getElementById('specific_date_container').style.display = 'block';
                                }
                            });
                            
                            function updateDateRestrictions() {
                                const numPersons = parseInt(document.getElementById('num_persons').value) || 0;
                                const dateInput = document.getElementById('event_date');
                                const helperText = document.getElementById('dateHelperText');
                                
                                // Get today's date at midnight
                                const today = new Date();
                                today.setHours(0, 0, 0, 0);
                                
                                // Calculate min date based on number of persons
                                const minDays = numPersons >= 100 ? 15 : 4; // Adding 1 to get full days
                                const minDate = new Date(today);
                                minDate.setDate(today.getDate() + minDays);
                                
                                // Format date for input
                                const minDateStr = minDate.toISOString().split('T')[0];
                                dateInput.min = minDateStr;
                                
                                // Calculate max date (3 months from today)
                                const maxDate = new Date(today);
                                maxDate.setMonth(today.getMonth() + 3);
                                const maxDateStr = maxDate.toISOString().split('T')[0];
                                dateInput.max = maxDateStr;
                                
                                // Update helper text
                                const advanceDays = minDays - 1;
                                helperText.textContent = `Please book at least ${advanceDays} days in advance${numPersons >= 100 ? ' for large groups (100+ persons)' : ''}.`;
                                
                                // If current value is before minimum date, reset it
                                if (dateInput.value) {
                                    const selectedDate = new Date(dateInput.value);
                                    selectedDate.setHours(0, 0, 0, 0);
                                    if (selectedDate < minDate) {
                                        dateInput.value = '';
                                        showAlert(`Please book at least ${advanceDays} days in advance. Earliest available date is ${minDate.toLocaleDateString()}.`, 'warning');
                                    }
                                }
                                
                                // Also update the quick date options based on new min date
                                updateQuickDateOptions(minDate);
                            }
                            
                            // Function to update the quick date options based on minimum date required
                            function updateQuickDateOptions(minDate) {
                                const quickDateContainer = document.querySelector('#next_available_container .row');
                                quickDateContainer.innerHTML = '';
                                
                                // Generate 5 suggested dates starting from earliest available
                                for (let i = 0; i < 5; i++) {
                                    const date = new Date(minDate);
                                    date.setDate(minDate.getDate() + i);
                                    
                                    const dateStr = date.toISOString().split('T')[0];
                                    const dateFormatOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                                    const formattedDate = date.toLocaleDateString('en-US', dateFormatOptions);
                                    
                                    const div = document.createElement('div');
                                    div.className = 'col-md-6 mb-2';
                                    div.innerHTML = `
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="quick_date_${i}" 
                                                name="quick_date" 
                                                value="${dateStr}" 
                                                class="custom-control-input"
                                                ${i === 0 ? 'checked' : ''}>
                                            <label class="custom-control-label" for="quick_date_${i}">
                                                <strong>${formattedDate}</strong>
                                            </label>
                                        </div>
                                    `;
                                    
                                    quickDateContainer.appendChild(div);
                                }
                            }

                            // Helper function to show alerts
                            function showAlert(message, type) {
                                const alertContainer = document.getElementById('alertContainer');
                                const alertHTML = `
                                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                                        ${message}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                `;
                                
                                if (!alertContainer) {
                                    // Create alert container if it doesn't exist
                                    const container = document.createElement('div');
                                    container.id = 'alertContainer';
                                    container.className = 'mb-3';
                                    container.innerHTML = alertHTML;
                                    
                                    // Insert at the top of the form
                                    const form = document.querySelector('form');
                                    form.insertBefore(container, form.firstChild);
                                } else {
                                    // Add to existing container
                                    alertContainer.innerHTML = alertHTML;
                                }
                                
                                // Auto dismiss after 5 seconds
                                setTimeout(() => {
                                    $('.alert').alert('close');
                                }, 5000);
                            }

                            function calculateTotal() {
                                const numPersons = parseInt(document.querySelector('input[name="num_persons"]').value) || 0;
                                const selectedPackage = document.querySelector('input[name="menu_bundle"]:checked');
                                
                                // Handle custom package selection
                                if (selectedPackage && selectedPackage.id === "package_custom") {
                                    document.getElementById('packageCost').textContent = 'To be determined';
                                    document.getElementById('totalAmount').textContent = 'To be determined';
                                    
                                    // Still calculate services cost
                                    let servicesCost = 0;
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
                                    document.getElementById('servicesCost').textContent = '₱' + servicesCost.toFixed(2);
                                    return;
                                }
                                
                                // Normal calculation for standard packages
                                let total = 0;
                                let packageCost = 0;
                                let servicesCost = 0;

                                // Calculate package cost
                                if (selectedPackage) {
                                    packageCost = numPersons * parseFloat(selectedPackage.dataset.price);
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

                            // Check for custom package selection or small group
                            function checkForSpecialCases() {
                                const numPersons = parseInt(document.getElementById('num_persons').value) || 0;
                                const customPackage = document.querySelector('#package_custom:checked');
                                
                                return (numPersons < 50 || customPackage);
                            }

                            // Add event listeners
                            document.querySelector('input[name="num_persons"]').addEventListener('input', function() {
                                updateDateRestrictions();
                                calculateTotal();
                                const numPersons = parseInt(this.value) || 0;
                                const warningText = document.getElementById('smallGroupWarning');
                                warningText.style.display = numPersons < 50 ? 'block' : 'none';
                            });

                            // Add listeners for the radio buttons
                            document.querySelectorAll('.package-select').forEach(radio => {
                                radio.addEventListener('change', calculateTotal);
                            });

                            document.querySelectorAll('input[name="options[]"]').forEach(checkbox => {
                                checkbox.addEventListener('change', calculateTotal);
                            });

                            // Form submission handler for special cases
                            document.querySelector('form').addEventListener('submit', function(e) {
                                if (checkForSpecialCases()) {
                                    e.preventDefault(); // Stop form submission
                                    
                                    // Set appropriate message
                                    const numPersons = parseInt(document.getElementById('num_persons').value) || 0;
                                    const customPackage = document.querySelector('#package_custom:checked');
                                    let message = '';
                                    
                                    if (customPackage && numPersons < 50) {
                                        message = 'You have selected a custom package and specified fewer than 50 persons. For these special requests, our staff will need to contact you directly.';
                                    } else if (customPackage) {
                                        message = 'You have selected a custom package. Our staff will contact you to discuss your requirements and provide a quote.';
                                    } else if (numPersons < 50) {
                                        message = 'You have requested catering for fewer than 50 persons. For smaller groups, our staff will need to contact you to discuss options.';
                                    }
                                    
                                    document.getElementById('customRequestMessage').textContent = message;
                                    $('#customRequestModal').modal('show');
                                }
                            });

                            // Handle the checkbox in the modal
                            document.getElementById('proceedAnywayCheck').addEventListener('change', function() {
                                document.getElementById('proceedAnywayBtn').disabled = !this.checked;
                            });

                            // Handle the proceed anyway button
                            document.getElementById('proceedAnywayBtn').addEventListener('click', function() {
                                $('#customRequestModal').modal('hide');
                                document.querySelector('form').submit();
                            });

                            // Initialize
                            document.getElementById('proceedAnywayBtn').disabled = true;
                            updateDateRestrictions();
                            calculateTotal();
                            
                            // Check if we need to show the small group warning on page load
                            const initialNumPersons = parseInt(document.getElementById('num_persons').value) || 0;
                            if (initialNumPersons < 50) {
                                document.getElementById('smallGroupWarning').style.display = 'block';
                            }

                            // Reset form if submission was successful
                            <?php if(isset($_SESSION['success'])): ?>
                                document.querySelector('form').reset();
                                calculateTotal(); // Reset the total amount display
                                document.getElementById('smallGroupWarning').style.display = 'none'; // Hide warning after reset
                            <?php endif; ?>
                            </script>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Custom Package / Small Group Modal -->
    <div class="modal fade" id="customRequestModal" tabindex="-1" role="dialog" aria-labelledby="customRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-accent text-white">
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
                    <button type="button" id="proceedAnywayBtn" class="btn btn-accent">Proceed with Request</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Additional init script to ensure modal button works properly
        $(document).ready(function() {
            // Direct handler for the checkbox to ensure button is enabled
            $('#proceedAnywayCheck').on('change', function() {
                $('#proceedAnywayBtn').prop('disabled', !this.checked);
            });
            
            // Make sure button handlers are working
            $('#proceedAnywayBtn').on('click', function() {
                $('#customRequestModal').modal('hide');
                $('form').submit();
            });
            
            // Direct button management when modal shows
            $('#customRequestModal').on('shown.bs.modal', function() {
                // Force initialize the button state
                $('#proceedAnywayBtn').prop('disabled', !$('#proceedAnywayCheck').is(':checked'));
                
                // Direct onclick handler - backup method
                document.getElementById('proceedAnywayBtn').onclick = function() {
                    $('#customRequestModal').modal('hide');
                    document.querySelector('form').submit();
                };
            });
        });
    </script>
    <?php include('authenticate.php'); ?>
</body>
</html>