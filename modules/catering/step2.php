<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../dbconi.php';
session_start();

if (!isset($_SESSION['loginok'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Store step 1 data in session
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['catering_step1'] = $_POST;
}

// Redirect if step 1 data is missing
if (!isset($_SESSION['catering_step1'])) {
    header('Location: step1.php');
    exit;
}

// Get package price and calculate total cost
$package_price = 0;
if (isset($_SESSION['catering_step1']['menu_bundle']) && $_SESSION['catering_step1']['menu_bundle'] !== 'Custom Package') {
    $package_query = "SELECT base_price FROM packages WHERE name = ?";
    $stmt = mysqli_prepare($dbc, $package_query);
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['catering_step1']['menu_bundle']);
    mysqli_stmt_execute($stmt);
    $package_result = mysqli_stmt_get_result($stmt);
    if ($package_data = mysqli_fetch_assoc($package_result)) {
        $package_price = $package_data['base_price'];
    }
}

// Calculate total cost
$num_persons = intval($_SESSION['catering_step1']['num_persons']);
$total_package_cost = $package_price * $num_persons;

// Get menu requirements for the selected package
if(isset($_SESSION['catering_step1']['menu_bundle']) && 
   $_SESSION['catering_step1']['menu_bundle'] !== 'Custom Package') {
    
    $package_req_query = "SELECT pp.category_id, c.category_name, pp.amount 
                         FROM packages p
                         JOIN package_products pp ON p.package_id = pp.package_id 
                         JOIN categories c ON pp.category_id = c.category_id
                         WHERE p.name = ?";
    $stmt = mysqli_prepare($dbc, $package_req_query);
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['catering_step1']['menu_bundle']);
    mysqli_stmt_execute($stmt);
    $package_req_result = mysqli_stmt_get_result($stmt);
    
    $package_requirements = [];
    while($req = mysqli_fetch_assoc($package_req_result)) {
        $package_requirements[$req['category_name']] = $req['amount'];
    }
} else {
    $package_requirements = [];
}

// Get menu items with halal status
$query = "SELECT DISTINCT p.*, c.category_name,
          CASE WHEN EXISTS (
              SELECT 1 FROM product_ingredients pi 
              WHERE pi.product_id = p.product_id 
              AND pi.ingredient_id = 1
          ) THEN 0 ELSE 1 END as is_halal 
          FROM products p
          JOIN categories c ON p.prod_cat_id = c.category_id
          ORDER BY c.category_id, p.prod_name";
$result = mysqli_query($dbc, $query);
if (!$result) {
    die("Query failed: " . mysqli_error($dbc));
}

// Group products by category
$menu_items = [];
while ($row = mysqli_fetch_assoc($result)) {
    if (!isset($menu_items[$row['category_name']])) {
        $menu_items[$row['category_name']] = [];
    }
    $menu_items[$row['category_name']][] = $row;
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
    <link rel="stylesheet" href="styles.css">
    <title>Catering Request - Step 2 - MARJ Food Services</title>
    <style>
        .menu-category {
            margin-bottom: 2rem;
            padding: 1rem;
            border-radius: 15px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .menu-category h3 {
            color: var(--accent);
            margin-bottom: 1.5rem;
        }
        .menu-item {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 10px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }
        .menu-item:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .menu-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        .halal-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #28a745;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
        }
        .non-halal-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #dc3545;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
        }
        .required-badge {
            display: inline-block;
            background-color: var(--accent);
            color: #fff;
            padding: 0.2rem 0.4rem;
            border-radius: 10px;
            margin-left: 0.5rem;
            font-size: 0.8rem;
            font-weight: normal;
        }
        
        .nav-pills .nav-link.active .required-badge {
            background-color: #fff;
            color: var(--accent);
        }
        
        .category-error {
            color: #dc3545;
            font-size: 0.875rem;
            padding: 0.5rem;
            margin: 0.5rem 0;
            border-radius: 4px;
            display: none;
        }
        
        .category-error.show {
            display: block;
            animation: fadeIn 0.3s ease-in;
        }
        
        .menu-item.selected {
            border: 2px solid var(--accent);
            transform: translateY(-2px);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .menu-actions {
            margin-top: 1rem;
            text-align: right;
        }
        .card-img-top {
            aspect-ratio: 16/9;
            object-fit: cover;
            width: 100%;
        }
    </style>
</head>
<body>
    <?php include("../navigation.php"); ?>
    
    <main class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-accent text-white rounded-top" style="background-color: var(--accent);">
                        <h2 class="h4 mb-0">Menu Selection - Step 2</h2>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-4">
                            <div class="progress-bar bg-accent" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">Step 2 of 2</div>
                        </div>

                        <form action="../submit_catering.php" method="POST">
                            <?php 
                            // Get package price from database first
                            $package_price = 0;
                            if(isset($_SESSION['catering_step1']['menu_bundle']) && $_SESSION['catering_step1']['menu_bundle'] !== 'Custom Package') {
                                $package_query = "SELECT base_price FROM packages WHERE name = ?";
                                $stmt = mysqli_prepare($dbc, $package_query);
                                mysqli_stmt_bind_param($stmt, "s", $_SESSION['catering_step1']['menu_bundle']);
                                mysqli_stmt_execute($stmt);
                                $package_result = mysqli_stmt_get_result($stmt);
                                if($package_data = mysqli_fetch_assoc($package_result)) {
                                    $package_price = $package_data['base_price'];
                                }
                            }

                            // Add package price as a hidden field
                            echo "<input type='hidden' name='package_price' value='" . htmlspecialchars($package_price) . "'>";

                            // Output session values as hidden fields
                            foreach ($_SESSION['catering_step1'] as $key => $value): ?>
                                <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" 
                                       value="<?php echo htmlspecialchars($value); ?>"
                                       <?php if($key === 'menu_bundle'): ?>
                                       data-price="<?php echo htmlspecialchars($package_price); ?>"
                                       class="package-select"
                                       <?php endif; ?>>
                            <?php endforeach; ?>

                            <!-- Halal Filter -->
                            <div class="mb-4 text-center">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="halalOnly">
                                    <label class="custom-control-label" for="halalOnly">Show Halal Items Only</label>
                                </div>
                            </div>

                            <!-- Menu Categories -->
                            <div class="nav nav-pills justify-content-center mb-4" id="menu-tabs" role="tablist">
                                <?php 
                                $first = true;
                                foreach ($menu_items as $category => $items): 
                                    $required = isset($package_requirements[$category]) ? $package_requirements[$category] : 0;
                                    $categoryId = strtolower(str_replace(' ', '-', $category));
                                ?>
                                    <a class="nav-link <?php echo $first ? 'active' : ''; ?>" 
                                       id="<?php echo $categoryId; ?>-tab" 
                                       data-toggle="pill" 
                                       href="#<?php echo $categoryId; ?>" 
                                       role="tab">
                                        <?php echo $category; ?>
                                        <?php if($required > 0): ?>
                                            <span class="required-badge" title="Required selections">
                                                <span class="selected-count">0</span>/<?php echo $required; ?>
                                            </span>
                                        <?php endif; ?>
                                    </a>
                                <?php 
                                    $first = false;
                                endforeach; 
                                ?>
                            </div>

                            <!-- Menu Items -->
                            <div class="tab-content" id="menu-content">
                                <?php 
                                $first = true;
                                foreach ($menu_items as $category => $items): 
                                    $categoryId = strtolower(str_replace(' ', '-', $category));
                                ?>
                                    <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?>" 
                                         id="<?php echo $categoryId; ?>" 
                                         role="tabpanel">
                                        <div class="menu-category">
                                            <h3>
                                                <?php echo $category; ?>
                                                <?php if(isset($package_requirements[$category]) && $package_requirements[$category] > 0): ?>
                                                    <small class="text-muted">
                                                        (Select <?php echo $package_requirements[$category]; ?> items)
                                                    </small>
                                                <?php endif; ?>
                                            </h3>
                                            <div id="<?php echo $categoryId; ?>-error" class="category-error"></div>
                                            <div class="row">
                                                <?php foreach ($items as $item): ?>
                                                    <div class="col-md-4 mb-4 menu-item-card" data-halal="<?php echo $item['is_halal']; ?>">
                                                        <div class="card h-100">
                                                            <div class="position-relative">
                                                                <?php if ($item['is_halal']): ?>
                                                                    <span class="halal-badge">Halal</span>
                                                                <?php else: ?>
                                                                    <span class="non-halal-badge">Non-Halal</span>
                                                                <?php endif; ?>
                                                                <img src="<?php echo BASE_URL; ?>/images/Products/<?php echo $item['prod_img']; ?>" 
                                                                     class="card-img-top" 
                                                                     alt="<?php echo htmlspecialchars($item['prod_name']); ?>">
                                                            </div>
                                                            <div class="card-body">
                                                                <h5 class="card-title"><?php echo htmlspecialchars($item['prod_name']); ?></h5>
                                                                <p class="card-text"><?php echo htmlspecialchars($item['prod_desc']); ?></p>
                                                                <p class="card-text"><strong>₱<?php echo number_format($item['prod_price'], 2); ?></strong></p>                                                                <?php $required = isset($package_requirements[$category]) ? $package_requirements[$category] : 0; ?>
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" 
                                                                           class="custom-control-input menu-item-select" 
                                                                           id="item_<?php echo $item['product_id']; ?>" 
                                                                           name="menu_selections[<?php echo $category; ?>][]" 
                                                                           value="<?php echo $item['product_id']; ?>"
                                                                           data-price="<?php echo $item['prod_price']; ?>"
                                                                           data-category="<?php echo $category; ?>"
                                                                           data-required="<?php echo $required; ?>">
                                                                    <label class="custom-control-label" for="item_<?php echo $item['product_id']; ?>">
                                                                        Select this item
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php 
                                $first = false;
                                endforeach; ?>
                            </div>

                            <hr class="my-4">

                            <div class="form-group">
                                <label>Payment Method</label>
                                <select class="form-control" name="payment_method" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="cash">Cash</option>
                                    <option value="gcash">GCash</option>
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
                                    <h5 class="mb-3">Final Cost Summary</h5>
                                    <div class="row">                                        <div class="col-md-8">
                                            <p class="mb-2">Package Cost: <span id="packageCost">₱<?php echo number_format($total_package_cost, 2); ?></span></p>
                                            <p class="mb-2">Additional Services: <span id="servicesCost">₱0.00</span></p>
                                            <hr>
                                            <p class="font-weight-bold">Total Amount: <span id="totalAmount" class="text-primary">₱<?php echo number_format($total_package_cost, 2); ?></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <a href="step1.php" class="btn btn-secondary px-4"><i class="fas fa-arrow-left mr-2"></i> Back</a>
                                <button type="submit" class="btn btn-accent px-5">Submit Catering Request</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="scripts.js"></script>    <script>
        $(document).ready(function() {
    // Initialize package cost calculation and show it right away
    calculateTotal();
    
    // Halal filter functionality
    $('#halalOnly').change(function() {
        const showHalalOnly = $(this).is(':checked');
        $('.menu-item-card').each(function() {
            const isHalal = $(this).data('halal') === 1;
            $(this).toggle(!showHalalOnly || isHalal);
        });
    });
    
    // Menu item selection with requirements check
    $('.menu-item-select').change(function() {
        const category = $(this).data('category');
        const required = $(this).data('required') || 0;
        
        // Add or remove .selected class on card
        const card = $(this).closest('.card');
        if ($(this).is(':checked')) {
            card.addClass('border-primary');
        } else {
            card.removeClass('border-primary');
        }
        
        // Count selections in this category
        const selectedCount = $(`input[data-category="${category}"]:checked`).length;
        
        // If trying to select more than required and required is greater than 0
        if (selectedCount > required && required > 0) {
            $(this).prop('checked', false);
            card.removeClass('border-primary');
            alert(`You can only select ${required} item(s) from ${category}`);
            return;
        }
        
        // Update the selection counter in the tab
        const safeCategory = category.toLowerCase().replace(/ /g, '-');
        $(`#${safeCategory}-tab .selected-count`).text(selectedCount);
        
        // Show or hide error message
        const errorMsg = $(`#${safeCategory}-error`);
        if (required > 0) {
            if (selectedCount < required) {
                errorMsg.show().html(`Please select ${required - selectedCount} more item(s) from ${category}`);
                errorMsg.addClass('show');
            } else {
                errorMsg.removeClass('show').hide();
            }
        }
        
        calculateTotal();
        validateMenuSelections();
    });
    
    // Add services cost calculation
    $('input[name="options[]"]').change(function() {
        calculateTotal();
    });
    
    // Form validation
    $('form').submit(function(e) {
        if (!validateMenuSelections()) {
            e.preventDefault();
            $('html, body').animate({ scrollTop: 0 }, 'slow');
            showAlert('Please complete all required menu selections before proceeding.', 'warning');
            return false;
        }
    });
    
    // Calculate the total cost of the order
    function calculateTotal() {
        let packageCost = parseFloat($('.package-select').data('price')) || 0;
        const numPersons = parseInt($('input[name="num_persons"]').val()) || 0;
        
        // Calculate package base cost
        let servicesCost = 0;
    $('input[name="options[]"]:checked').each(function() {
        const service = $(this).val();
        if (service === 'setup') servicesCost += 2000;
        if (service === 'tables') servicesCost += 3500;
        if (service === 'decoration') servicesCost += 5000;
    });
        // For custom package, add up selected items
        if ($('.package-select').val() === 'Custom Package') {
    $('#packageCost').text('To be determined');
    $('#servicesCost').text('₱' + servicesCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#totalAmount').text('To be determined');
    return;
}
        
        // Calculate services cost
        // let servicesCost = 0;
        // $('input[name="options[]"]:checked').each(function() {
        //     const service = $(this).val();
        //     if (service === 'setup') servicesCost += 2000;
        //     if (service === 'tables') servicesCost += 3500;
        //     if (service === 'decoration') servicesCost += 5000;
        // });
        let baseCost = packageCost * numPersons;
        // Update the display
        if (numPersons < 50) {
            $('#packageCost').text('₱' + (baseCost).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})+ ' (Subject to change)');
        } else {
           $('#packageCost').text('₱' + baseCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        }
        

        $('#servicesCost').text('₱' + servicesCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#totalAmount').text('₱' + (baseCost + servicesCost).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }
    
    function validateMenuSelections() {
        let isValid = true;
        let unfulfilled = [];
        
        // Iterate through each category tab to check requirements
        $('#menu-tabs .nav-link').each(function() {
            const tab = $(this);
            const badge = tab.find('.required-badge');
            
            if (badge.length > 0) {
                const required = parseInt(badge.text().split('/')[1]);
                const selected = parseInt(badge.find('.selected-count').text());
                const category = tab.text().trim().split('\n')[0];  // Get category name from tab text
                
                if (selected < required) {
                    isValid = false;
                    unfulfilled.push(`${category} (${required - selected} more needed)`);
                    
                    // Show error in the tab pane
                    const categoryId = tab.attr('href').substring(1);
                    $(`#${categoryId}-error`).show().html(`Please select ${required - selected} more item(s) from ${category}`);
                    $(`#${categoryId}-error`).addClass('show');
                }
            }
        });
        
        // Show a summary warning if needed
        if (!isValid) {
            showAlert('Please complete all required selections: ' + unfulfilled.join(', '), 'warning');
        }
        
        return isValid;
    }
    
    function showAlert(message, type) {
        // Create alert container if it doesn't exist
        if ($('#alertContainer').length === 0) {
            $('<div id="alertContainer" class="alert-container my-3"></div>').insertBefore($('form'));
        }
        
        const alertHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>`;
        
        $('#alertContainer').html(alertHTML);
        
        // Auto-close after 5 seconds
        setTimeout(() => {
            $('.alert').alert('close');
        }, 5000);
    }
    
    // Initialize validation on page load
    validateMenuSelections();
});
    </script>
    <?php include('../authenticate.php'); ?>
</body>
</html>