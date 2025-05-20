<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {        
    echo "Unauthorized access";
    exit;
}

include("dbconi.php");

try {
    if (!isset($_GET['product_id'])) {
        throw new Exception('Product ID is required');
    }

    $product_id = mysqli_real_escape_string($dbc, $_GET['product_id']);

    $query = "SELECT pi.*, i.name, i.unit, t.type_name 
              FROM product_ingredients pi
              JOIN ingredients i ON pi.ingredient_id = i.ingredient_id
              JOIN ingredient_types t ON i.type_id = t.type_id
              WHERE pi.product_id = ?
              ORDER BY t.type_name, i.name";

    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['type_name'] . ' - ' . $row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
        echo "<td>" . htmlspecialchars($row['unit']) . "</td>";
        echo "<td>";
        echo '<button class="btn btn-sm btn-primary edit-product-ingredient mr-1" data-id="' . $row['ingredient_id'] . '"><i class="fas fa-edit"></i></button>';
        echo '<button class="btn btn-sm btn-danger delete-ingredient" data-id="' . $row['ingredient_id'] . '">';
        echo '<i class="fas fa-trash"></i>';
        echo '</button>';
        echo "</td>";
        echo "</tr>";
    }

    if (mysqli_num_rows($result) == 0) {
        echo "<tr><td colspan='4' class='text-center'>No ingredients added yet</td></tr>";
    }

} catch (Exception $e) {
    echo "<tr><td colspan='4' class='text-danger'>" . htmlspecialchars($e->getMessage()) . "</td></tr>";
} finally {
    mysqli_close($dbc);
}
?>