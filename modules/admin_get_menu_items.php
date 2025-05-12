<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

include("dbconi.php");

try {
    // Get all menu items with their categories and halal status
    $query = "SELECT p.product_id, p.prod_name, p.prod_desc, p.prod_price, c.category_name,
              CASE WHEN EXISTS (
                  SELECT 1 FROM product_ingredients pi 
                  WHERE pi.product_id = p.product_id 
                  AND pi.ingredient_id = 1 -- Assuming 1 is non-halal ingredient
              ) THEN 0 ELSE 1 END as is_halal
              FROM products p
              JOIN categories c ON p.prod_cat_id = c.category_id
              WHERE c.category_name IN ('Main Dishes', 'Sides', 'Desserts', 'Beverages')
              ORDER BY c.category_id, p.prod_name";

    $result = mysqli_query($dbc, $query);
    
    if (!$result) {
        throw new Exception("Error fetching menu items: " . mysqli_error($dbc));
    }

    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = [
            'product_id' => $row['product_id'],
            'prod_name' => $row['prod_name'],
            'prod_desc' => $row['prod_desc'],
            'prod_price' => $row['prod_price'],
            'category_name' => $row['category_name'],
            'is_halal' => (bool)$row['is_halal']
        ];
    }

    echo json_encode(['success' => true, 'items' => $items]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
