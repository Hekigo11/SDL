<?php
require_once '../config.php';
header('Content-Type: text/html; charset=utf-8');

// Check admin authentication
if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    echo "Unauthorized";
    exit;
}

include("dbconi.php");

// Get date range from request or session, defaulting to current month
$default_start = date('Y-m-01'); // First day of current month
$default_end = date('Y-m-t');    // Last day of current month
error_log("Default dates - Start: $default_start, End: $default_end");
$start_date = $default_start;
$end_date = $default_end;

// Priority for date values:
// 1. GET parameters (for AJAX requests)
// 2. Session values
// 3. Default values (current month)

if (isset($_GET['is_ajax']) && $_GET['is_ajax']) {
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $parsed_date = date_create_from_format('Y-m-d', $_GET['start_date']);
        if ($parsed_date !== false) {
            $start_date = $parsed_date->format('Y-m-d');
        }
    }
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $parsed_date = date_create_from_format('Y-m-d', $_GET['end_date']);
        if ($parsed_date !== false) {
            $end_date = $parsed_date->format('Y-m-d');
        }
    }
} elseif (isset($_SESSION['sales_start_date']) && isset($_SESSION['sales_end_date'])) {
    $start_date = $_SESSION['sales_start_date'];
    $end_date = $_SESSION['sales_end_date'];
}

// Validate dates
$start = strtotime($start_date);
$end = strtotime($end_date);

if ($start === false || $end === false || $end < $start) {
    // Invalid dates, reset to defaults
    $start_date = $default_start;
    $end_date = $default_end;
}

// Add time components for database queries
$start_datetime = $start_date . ' 00:00:00';
$end_datetime = $end_date . ' 23:59:59';

// Debug log
error_log("Loading sales data for period: $start_date to $end_date");

// Get sales data
function getSalesData($dbc, $start_datetime, $end_datetime) {
    // Extract dates without time for daily sales array
    $start_date = date('Y-m-d', strtotime($start_datetime));
    $end_date = date('Y-m-d', strtotime($end_datetime));
    
    $data = [
        'total_sales' => 0,
        'delivery_sales' => 0,
        'standard_catering_sales' => 0,
        'custom_catering_sales' => 0,
        'order_counts' => [
            'delivery' => 0,
            'standard_catering' => 0,
            'custom_catering' => 0
        ],
        'daily_sales' => [],
        'top_products' => [],
        'total_customers' => 0
    ];

    // Initialize daily_sales array for the date range
    $current = new DateTime($start_date);
    $end = new DateTime($end_date);
    while ($current <= $end) {
        $date = $current->format('Y-m-d');
        $data['daily_sales'][$date] = [
            'delivery' => 0,
            'standard_catering' => 0,
            'custom_catering' => 0
        ];
        $current->modify('+1 day');
    }

    // Debug output
    error_log("Date range: $start_datetime to $end_datetime");
    error_log("Daily sales dates initialized: " . print_r(array_keys($data['daily_sales']), true));

    // Delivery Orders
    $query = "SELECT COUNT(*) as count, SUM(COALESCE(total_amount, 0)) as total, DATE(created_at) as date 
              FROM orders 
              WHERE created_at BETWEEN ? AND ? 
              AND status = 'completed'
              GROUP BY DATE(created_at)";
    
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "ss", $start_datetime, $end_datetime);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    error_log("Delivery orders query executed");
    while ($row = mysqli_fetch_assoc($result)) {
        $data['delivery_sales'] += floatval($row['total']);
        $data['order_counts']['delivery'] += intval($row['count']);
        $data['daily_sales'][$row['date']]['delivery'] = floatval($row['total']);
        error_log("Added delivery sales for {$row['date']}: {$row['total']}");
    }

    // Standard Catering Orders
    $query = "SELECT COUNT(*) as count, SUM(COALESCE(total_amount, 0)) as total, DATE(created_at) as date 
              FROM catering_orders 
              WHERE created_at BETWEEN ? AND ? 
              AND status = 'completed'
              GROUP BY DATE(created_at)";
    
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "ss", $start_datetime, $end_datetime);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    error_log("Standard catering orders query executed");
    while ($row = mysqli_fetch_assoc($result)) {
        $data['standard_catering_sales'] += floatval($row['total']);
        $data['order_counts']['standard_catering'] += intval($row['count']);
        $data['daily_sales'][$row['date']]['standard_catering'] = floatval($row['total']);
        error_log("Added standard catering sales for {$row['date']}: {$row['total']}");
    }

    // Custom Catering Orders
    $query = "SELECT co.*, 
              SUM(CASE WHEN needs_setup = 1 THEN 2000 ELSE 0 END + 
                  CASE WHEN needs_tablesandchairs = 1 THEN 3500 ELSE 0 END + 
                  CASE WHEN needs_decoration = 1 THEN 5000 ELSE 0 END + 
                  COALESCE(quote_amount, 0)) as total_with_services,
              DATE(created_at) as date,
              COUNT(*) as count
              FROM custom_catering_orders co
              WHERE created_at BETWEEN ? AND ? 
              AND status = 'completed'
              GROUP BY DATE(created_at)";
    
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "ss", $start_datetime, $end_datetime);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    error_log("Custom catering orders query executed");
    while ($row = mysqli_fetch_assoc($result)) {
        $data['custom_catering_sales'] += floatval($row['total_with_services']);
        $data['order_counts']['custom_catering'] += intval($row['count']);
        $data['daily_sales'][$row['date']]['custom_catering'] = floatval($row['total_with_services']);
        error_log("Added custom catering sales for {$row['date']}: {$row['total_with_services']}");
    }

    // Get top selling products
    $query = "SELECT p.prod_name, SUM(oi.quantity) as total_qty, SUM(oi.quantity * p.prod_price) as total_sales
              FROM order_items oi
              JOIN products p ON oi.product_id = p.product_id
              JOIN orders o ON oi.order_id = o.order_id
              WHERE o.created_at BETWEEN ? AND ? 
              AND o.status = 'completed'
              GROUP BY p.product_id
              ORDER BY total_qty DESC
              LIMIT 5";
    
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "ss", $start_datetime, $end_datetime);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $data['top_products'][] = $row;
    }

    // Get total unique customers
    $query = "SELECT COUNT(DISTINCT user_id) as total_customers
              FROM (
                  SELECT user_id FROM orders 
                  WHERE created_at BETWEEN ? AND ? AND status = 'completed'
                  UNION
                  SELECT user_id FROM catering_orders 
                  WHERE created_at BETWEEN ? AND ? AND status = 'completed'
                  UNION
                  SELECT user_id FROM custom_catering_orders 
                  WHERE created_at BETWEEN ? AND ? AND status = 'completed'
              ) as combined_orders";
    
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "ssssss", $start_datetime, $end_datetime, $start_datetime, $end_datetime, $start_datetime, $end_datetime);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $data['total_customers'] = $row['total_customers'];
    }

    $data['total_sales'] = floatval($data['delivery_sales']) + floatval($data['standard_catering_sales']) + floatval($data['custom_catering_sales']);
    
    error_log("Final data structure: " . print_r($data, true));
    return $data;
}

// Call the function with the datetime values
$sales_data = getSalesData($dbc, $start_datetime, $end_datetime);

// Debug output
echo "<!--\n";
echo "Debug Info:\n";
echo "Start Date: " . $start_date . "\n";
echo "End Date: " . $end_date . "\n";
echo "Data:\n";
print_r($sales_data);
echo "\n-->\n";

?>

<div id="loading-indicator" style="display:none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; background: rgba(255,255,255,0.8); padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <div class="d-flex align-items-center">
        <div class="spinner-border text-primary mr-2" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <span>Loading data...</span>
    </div>
</div>

<div class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Sales Report</h3>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary period-selector" data-period="today">Today</button>
                    <button type="button" class="btn btn-outline-primary period-selector" data-period="week">This Week</button>
                    <button type="button" class="btn btn-outline-primary period-selector" data-period="month">This Month</button>
                    <button type="button" class="btn btn-outline-primary period-selector" data-period="year">This Year</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form id="reportFilterForm" class="row align-items-end">
                        <div class="col-md-4">
                            <label>Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                        </div>
                        <div class="col-md-4">
                            <label>End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                        </div>                        <div class="col-md-4">
                            <button type="button" id="apply-filter" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Apply Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Sales</div>
                            <div class="h5 mb-0 font-weight-bold">₱<?php echo number_format($sales_data['total_sales'], 2); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-peso-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Delivery Orders</div>
                            <div class="h5 mb-0 font-weight-bold">₱<?php echo number_format($sales_data['delivery_sales'], 2); ?></div>
                            <div class="text-xs text-muted"><?php echo $sales_data['order_counts']['delivery']; ?> orders</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Standard Catering</div>
                            <div class="h5 mb-0 font-weight-bold">₱<?php echo number_format($sales_data['standard_catering_sales'], 2); ?></div>
                            <div class="text-xs text-muted"><?php echo $sales_data['order_counts']['standard_catering']; ?> orders</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-utensils fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Custom Catering</div>
                            <div class="h5 mb-0 font-weight-bold">₱<?php echo number_format($sales_data['custom_catering_sales'], 2); ?></div>
                            <div class="text-xs text-muted"><?php echo $sales_data['order_counts']['custom_catering']; ?> orders</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-birthday-cake fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Sales Summary</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr>
                                <td>Total Orders</td>
                                <td class="text-right"><?php echo array_sum($sales_data['order_counts']); ?></td>
                            </tr>
                            <tr>
                                <td>Unique Customers</td>
                                <td class="text-right"><?php echo $sales_data['total_customers']; ?></td>
                            </tr>
                            <tr>
                                <td>Average Order Value</td>
                                <td class="text-right">₱<?php 
                                    $total_orders = array_sum($sales_data['order_counts']);
                                    echo $total_orders > 0 ? 
                                        number_format($sales_data['total_sales'] / $total_orders, 2) : 
                                        '0.00'; 
                                ?></td>
                            </tr>
                            <tr>
                                <td>Revenue Share - Delivery</td>
                                <td class="text-right"><?php 
                                    echo $sales_data['total_sales'] > 0 ? 
                                        number_format(($sales_data['delivery_sales'] / $sales_data['total_sales']) * 100, 1) : 
                                        '0'; 
                                ?>%</td>
                            </tr>
                            <tr>
                                <td>Revenue Share - Standard Catering</td>
                                <td class="text-right"><?php 
                                    echo $sales_data['total_sales'] > 0 ? 
                                        number_format(($sales_data['standard_catering_sales'] / $sales_data['total_sales']) * 100, 1) : 
                                        '0'; 
                                ?>%</td>
                            </tr>
                            <tr>
                                <td>Revenue Share - Custom Catering</td>
                                <td class="text-right"><?php 
                                    echo $sales_data['total_sales'] > 0 ? 
                                        number_format(($sales_data['custom_catering_sales'] / $sales_data['total_sales']) * 100, 1) : 
                                        '0'; 
                                ?>%</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr>
                                <th>Service Type</th>
                                <th class="text-right">Orders</th>
                                <th class="text-right">Avg. Order Value</th>
                                <th class="text-right">Total Sales</th>
                            </tr>
                            <tr>
                                <td>Delivery</td>
                                <td class="text-right"><?php echo $sales_data['order_counts']['delivery']; ?></td>
                                <td class="text-right">₱<?php 
                                    echo $sales_data['order_counts']['delivery'] > 0 ? 
                                        number_format($sales_data['delivery_sales'] / $sales_data['order_counts']['delivery'], 2) : 
                                        '0.00'; 
                                ?></td>
                                <td class="text-right">₱<?php echo number_format($sales_data['delivery_sales'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>Standard Catering</td>
                                <td class="text-right"><?php echo $sales_data['order_counts']['standard_catering']; ?></td>
                                <td class="text-right">₱<?php 
                                    echo $sales_data['order_counts']['standard_catering'] > 0 ? 
                                        number_format($sales_data['standard_catering_sales'] / $sales_data['order_counts']['standard_catering'], 2) : 
                                        '0.00'; 
                                ?></td>
                                <td class="text-right">₱<?php echo number_format($sales_data['standard_catering_sales'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>Custom Catering</td>
                                <td class="text-right"><?php echo $sales_data['order_counts']['custom_catering']; ?></td>
                                <td class="text-right">₱<?php 
                                    echo $sales_data['order_counts']['custom_catering'] > 0 ? 
                                        number_format($sales_data['custom_catering_sales'] / $sales_data['order_counts']['custom_catering'], 2) : 
                                        '0.00'; 
                                ?></td>
                                <td class="text-right">₱<?php echo number_format($sales_data['custom_catering_sales'], 2); ?></td>
                            </tr>
                            <tr class="font-weight-bold">
                                <td>Total</td>
                                <td class="text-right"><?php echo array_sum($sales_data['order_counts']); ?></td>
                                <td class="text-right">₱<?php 
                                    $total_orders = array_sum($sales_data['order_counts']);
                                    echo $total_orders > 0 ? 
                                        number_format($sales_data['total_sales'] / $total_orders, 2) : 
                                        '0.00'; 
                                ?></td>
                                <td class="text-right">₱<?php echo number_format($sales_data['total_sales'], 2); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Selling Products</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-right">Quantity</th>
                                    <th class="text-right">Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sales_data['top_products'] as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['prod_name']); ?></td>
                                    <td class="text-right"><?php echo number_format($product['total_qty']); ?></td>
                                    <td class="text-right">₱<?php echo number_format($product['total_sales'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loadContent(startDate, endDate) {
    $('#loading-indicator').show();
    $.ajax({
        url: 'admin_content_loader.php',
        type: 'GET',
        data: {
            page: 'sales',
            start_date: startDate || $('#start_date').val(),
            end_date: endDate || $('#end_date').val(),
            is_ajax: 1
        },
        success: function(response) {
            // Only update the content within the container, not the container itself
            let $temp = $('<div>').html(response);
            let $newContent = $temp.find('.container-fluid').children();
            $('.container-fluid').empty().append($newContent);
            
            // Reattach event handlers
            initializeEventHandlers();
            $('#loading-indicator').hide();
        },
        error: function(xhr, status, error) {
            console.error('Error loading content:', error);
            $('#loading-indicator').hide();
            alert('Error loading data. Please try again.');
        },
        complete: function() {
            $('#loading-indicator').hide();
        }
    });
}

function initializeEventHandlers() {
    // Handle date filter application
    $('#apply-filter').off('click').on('click', function() {
        loadContent();
    });
      // Handle period selector buttons
    $('.period-selector').off('click').on('click', function() {
        // Make sure we have a fresh Date object with no time component
        let today = new Date();
        let year = today.getFullYear();
        let month = today.getMonth();
        let date = today.getDate();
        
        let startDate, endDate;
        
        // Add detailed debugging for current date
        console.log('Current date components:', {
            year: year,
            month: month, // 0-based (0 = January)
            date: date,
            isoString: today.toISOString(),
            localeDateString: today.toLocaleDateString()
        });
        
        switch($(this).data('period')) {
            case 'today':
                // Today only
                startDate = endDate = formatDateForInput(year, month, date);
                break;
            case 'week':
                // Calculate Monday of current week
                let dayOfWeek = today.getDay(); // 0 = Sunday, 1 = Monday, etc.
                let daysToMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1; // If Sunday, go back 6 days, otherwise go back (dayOfWeek - 1) days
                
                let mondayDate = new Date(year, month, date - daysToMonday);
                let mondayYear = mondayDate.getFullYear();
                let mondayMonth = mondayDate.getMonth();
                let mondayDay = mondayDate.getDate();
                
                // Calculate Sunday of current week
                let sundayDate = new Date(year, month, date + (7 - dayOfWeek) % 7);
                let sundayYear = sundayDate.getFullYear();
                let sundayMonth = sundayDate.getMonth();
                let sundayDay = sundayDate.getDate();
                
                startDate = formatDateForInput(mondayYear, mondayMonth, mondayDay);
                endDate = formatDateForInput(sundayYear, sundayMonth, sundayDay);
                
                console.log('Week calculation debug:', {
                    dayOfWeek: dayOfWeek,
                    daysToMonday: daysToMonday,
                    mondayDate: mondayDate.toLocaleDateString(),
                    sundayDate: sundayDate.toLocaleDateString()
                });
                break;
            case 'month':
                // First day of current month
                startDate = formatDateForInput(year, month, 1);
                
                // Last day of current month
                let lastDay = new Date(year, month + 1, 0).getDate();
                endDate = formatDateForInput(year, month, lastDay);
                
                console.log('Month calculation debug:', {
                    firstDay: formatDateForInput(year, month, 1),
                    lastDay: formatDateForInput(year, month, lastDay),
                    daysInMonth: lastDay
                });
                break;
            case 'year':
                // January 1st of current year
                startDate = formatDateForInput(year, 0, 1);
                
                // December 31st of current year
                endDate = formatDateForInput(year, 11, 31);
                
                console.log('Year calculation debug:', {
                    firstDay: formatDateForInput(year, 0, 1),
                    lastDay: formatDateForInput(year, 11, 31)
                });
                break;        }
        
        // Log the calculated dates
        console.log('Selected period:', $(this).data('period'));
        console.log('Start date:', startDate);
        console.log('End date:', endDate);
        
        // Update the date inputs
        $('#start_date').val(startDate);
        $('#end_date').val(endDate);
        
        // Load the content
        loadContent(startDate, endDate);
        
        // Update active state
        $('.period-selector').removeClass('active');
        $(this).addClass('active');
    });
}

// Helper function to format date as YYYY-MM-DD
function formatDateForInput(year, month, day) {
    // Pad month and day with leading zeros if needed
    let paddedMonth = (month + 1).toString().padStart(2, '0'); // month is 0-based
    let paddedDay = day.toString().padStart(2, '0');
    return `${year}-${paddedMonth}-${paddedDay}`;
}

$(document).ready(function() {
    // Initialize event handlers when page loads
    initializeEventHandlers();
});
</script>