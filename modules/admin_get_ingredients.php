<?php
session_start();
require_once __DIR__ . '/../config.php';

// Check if user is admin
if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {        
    echo 'Unauthorized';
    exit;
}

include("dbconi.php");

// Get search term if exists
$search = isset($_GET['term']) ? mysqli_real_escape_string($dbc, trim($_GET['term'])) : '';

// Query to get ingredients that match the search term
$query = "SELECT i.ingredient_id, i.name, i.unit, t.type_name 
          FROM ingredients i 
          JOIN ingredient_types t ON i.type_id = t.type_id";

// Add search condition - prioritize name matches over type matches
if (!empty($search)) {
    // First look for direct name matches
    $query = "SELECT i.ingredient_id, i.name, i.unit, t.type_name,
                    CASE 
                        WHEN i.name LIKE '$search%' THEN 1
                        WHEN i.name LIKE '%$search%' THEN 2
                        WHEN t.type_name LIKE '%$search%' THEN 3
                        ELSE 4
                    END AS priority
              FROM ingredients i 
              JOIN ingredient_types t ON i.type_id = t.type_id
              WHERE i.name LIKE '%$search%' OR t.type_name LIKE '%$search%'
              ORDER BY priority, t.type_name, i.name";
} else {
    // Default ordering if no search term
    $query .= " ORDER BY t.type_name, i.name";
}

// Execute query
$result = mysqli_query($dbc, $query);

if (!$result) {
    echo 'Database error';
    exit;
}

// Start building HTML
echo '<div class="list-group">';

if (mysqli_num_rows($result) == 0) {
    echo '<div class="list-group-item text-center text-muted">No ingredients found</div>';
} else {
    $current_type = '';
    
    while ($row = mysqli_fetch_assoc($result)) {
        // Add type header if new type
        if ($current_type != $row['type_name']) {
            echo '<div class="list-group-item bg-primary text-white font-weight-bold non-selectable">
                    <i class="fas fa-tag mr-2"></i>' . htmlspecialchars($row['type_name']) . 
                 '</div>';
            $current_type = $row['type_name'];
        }
        
        // Add ingredient item with hover effect
        echo '<div class="list-group-item list-group-item-action ingredient-item" 
                  data-id="' . $row['ingredient_id'] . '" 
                  data-name="' . htmlspecialchars($row['name']) . '" 
                  data-unit="' . htmlspecialchars($row['unit']) . '">
                <i class="fas fa-utensil-spoon text-muted mr-2"></i>' . 
                htmlspecialchars($row['name']) . 
                ' <small class="text-muted">(' . htmlspecialchars($row['unit']) . ')</small>
             </div>';
    }
}

echo '</div>';

// Add custom CSS
echo '<style>
    .non-selectable {
        cursor: default;
        pointer-events: none;
    }
    .list-group {
        max-height: 300px;
        overflow-y: auto;
    }
</style>';
?> 