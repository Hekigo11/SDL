<?php
session_start();
require_once '../config.php';

// Check admin authentication
if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {    
    echo "Unauthorized";
    exit;
}

include("dbconi.php");

// Get date range from request or default to current month
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Get sales data
function getSalesData($dbc, $start_date, $end_date) {
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

    // Delivery Orders
    $query = "SELECT COUNT(*) as count, SUM(total_amount) as total, DATE(created_at) as date 
              FROM orders 
              WHERE created_at BETWEEN ? AND ? 
              AND status != 'cancelled'
              GROUP BY DATE(created_at)";
    
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $data['delivery_sales'] += $row['total'];
        $data['order_counts']['delivery'] += $row['count'];
        $data['daily_sales'][$row['date']]['delivery'] = $row['total'];
    }

    // Standard Catering Orders
    $query = "SELECT COUNT(*) as count, SUM(total_amount) as total, DATE(created_at) as date 
              FROM catering_orders 
              WHERE created_at BETWEEN ? AND ? 
              AND status != 'cancelled'
              GROUP BY DATE(created_at)";
    
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $data['standard_catering_sales'] += $row['total'];
        $data['order_counts']['standard_catering'] += $row['count'];
        $data['daily_sales'][$row['date']]['standard_catering'] = $row['total'];
    }

    // Custom Catering Orders (including additional services)
    $query = "SELECT co.*, 
              (CASE WHEN needs_setup = 1 THEN 2000 ELSE 0 END + 
               CASE WHEN needs_tablesandchairs = 1 THEN 3500 ELSE 0 END + 
               CASE WHEN needs_decoration = 1 THEN 5000 ELSE 0 END + 
               COALESCE(quote_amount, 0)) as total_with_services,
              DATE(created_at) as date,
              COUNT(*) as count
              FROM custom_catering_orders co
              WHERE created_at BETWEEN ? AND ? 
              AND status != 'cancelled'
              GROUP BY DATE(created_at)";
    
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $data['custom_catering_sales'] += $row['total_with_services'];
        $data['order_counts']['custom_catering'] += $row['count'];
        $data['daily_sales'][$row['date']]['custom_catering'] = $row['total_with_services'];
    }

    // Get top selling products
    $query = "SELECT p.prod_name, SUM(oi.quantity) as total_qty, SUM(oi.quantity * p.prod_price) as total_sales
              FROM order_items oi
              JOIN products p ON oi.product_id = p.product_id
              JOIN orders o ON oi.order_id = o.order_id
              WHERE o.created_at BETWEEN ? AND ?
              AND o.status != 'cancelled'
              GROUP BY p.product_id
              ORDER BY total_qty DESC
              LIMIT 5";
    
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $data['top_products'][] = $row;
    }

    // Get total unique customers
    $query = "SELECT COUNT(DISTINCT user_id) as total_customers
              FROM (
                  SELECT user_id FROM orders 
                  WHERE created_at BETWEEN ? AND ? AND status != 'cancelled'
                  UNION
                  SELECT user_id FROM catering_orders 
                  WHERE created_at BETWEEN ? AND ? AND status != 'cancelled'
                  UNION
                  SELECT user_id FROM custom_catering_orders 
                  WHERE created_at BETWEEN ? AND ? AND status != 'cancelled'
              ) as combined_orders";
    
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "ssssss", $start_date, $end_date, $start_date, $end_date, $start_date, $end_date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $data['total_customers'] = $row['total_customers'];
    }

    $data['total_sales'] = $data['delivery_sales'] + $data['standard_catering_sales'] + $data['custom_catering_sales'];
    
    return $data;
}

$sales_data = getSalesData($dbc, $start_date, $end_date);
?>

<div class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Sales Report</h3>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary" onclick="setDateRange('today')">Today</button>
                    <button type="button" class="btn btn-outline-primary" onclick="setDateRange('week')">This Week</button>
                    <button type="button" class="btn btn-outline-primary" onclick="setDateRange('month')">This Month</button>
                    <button type="button" class="btn btn-outline-primary" onclick="setDateRange('year')">This Year</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="reportFilterForm" class="row align-items-end">
                        <div class="col-md-4">
                            <label>Start Date</label>
                            <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-md-4">
                            <label>End Date</label>
                            <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Apply Filter
                            </button>
                            <button type="button" class="btn btn-success" onclick="exportToExcel()">
                                <i class="fas fa-file-excel"></i> Export
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary h-100 py-2">
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
            <div class="card border-left-success h-100 py-2">
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
            <div class="card border-left-info h-100 py-2">
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
            <div class="card border-left-warning h-100 py-2">
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
        <div class="col-xl-8 col-lg-7">
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Sales Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Sales Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="salesDistributionChart"></canvas>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Selling Products</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prepare data for trend chart
    const dailySales = <?php echo json_encode($sales_data['daily_sales']); ?>;
    const dates = Object.keys(dailySales).sort();
    
    new Chart(document.getElementById('salesChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Delivery',
                data: dates.map(date => dailySales[date]['delivery'] || 0),
                borderColor: 'rgb(40, 167, 69)',
                tension: 0.1
            },
            {
                label: 'Standard Catering',
                data: dates.map(date => dailySales[date]['standard_catering'] || 0),
                borderColor: 'rgb(23, 162, 184)',
                tension: 0.1
            },
            {
                label: 'Custom Catering',
                data: dates.map(date => dailySales[date]['custom_catering'] || 0),
                borderColor: 'rgb(255, 193, 7)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => '₱' + value.toLocaleString()
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: context => context.dataset.label + ': ₱' + context.raw.toLocaleString()
                    }
                }
            }
        }
    });

    // Sales distribution pie chart
    new Chart(document.getElementById('salesDistributionChart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: ['Delivery', 'Standard Catering', 'Custom Catering'],
            datasets: [{
                data: [
                    <?php echo $sales_data['delivery_sales']; ?>,
                    <?php echo $sales_data['standard_catering_sales']; ?>,
                    <?php echo $sales_data['custom_catering_sales']; ?>
                ],
                backgroundColor: [
                    'rgb(40, 167, 69)',
                    'rgb(23, 162, 184)',
                    'rgb(255, 193, 7)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: context => {
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${context.label}: ₱${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
});

function setDateRange(range) {
    const today = new Date();
    let start = new Date();
    let end = new Date();

    switch(range) {
        case 'today':
            break;
        case 'week':
            start.setDate(today.getDate() - today.getDay());
            end.setDate(start.getDate() + 6);
            break;
        case 'month':
            start.setDate(1);
            end.setDate(new Date(today.getFullYear(), today.getMonth() + 1, 0).getDate());
            break;
        case 'year':
            start = new Date(today.getFullYear(), 0, 1);
            end = new Date(today.getFullYear(), 11, 31);
            break;
    }

    document.querySelector('input[name="start_date"]').value = start.toISOString().split('T')[0];
    document.querySelector('input[name="end_date"]').value = end.toISOString().split('T')[0];
    document.getElementById('reportFilterForm').submit();
}

function exportToExcel() {
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    window.location.href = `admin_export_sales.php?start_date=${startDate}&end_date=${endDate}`;
}

// Handle date filter form submission
document.getElementById('reportFilterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const params = new URLSearchParams(formData);
    loadContent('sales?' + params.toString());
});
</script>
