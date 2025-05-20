<?php
require_once __DIR__ . '/../config.php';
session_start();
include("dbconi.php");

// Check if user is admin
if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {        
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Check if action is set
if (!isset($_POST['action'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'No action specified']);
    exit;
}

$action = $_POST['action'];

// Handle different actions
switch ($action) {
    case 'add_ingredient':
        addIngredient($dbc);
        break;
    case 'edit_ingredient':
        editIngredient($dbc);
        break;
    case 'delete_ingredient':
        deleteIngredient($dbc);
        break;
    case 'add_type':
        addType($dbc);
        break;
    case 'edit_type':
        editType($dbc);
        break;
    case 'delete_type':
        deleteType($dbc);
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        exit;
}

// Function to add a new ingredient
function addIngredient($dbc) {
    // Check required fields
    if (!isset($_POST['name']) || !isset($_POST['unit']) || !isset($_POST['type_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }

    // Sanitize inputs
    $name = mysqli_real_escape_string($dbc, $_POST['name']);
    $unit = mysqli_real_escape_string($dbc, $_POST['unit']);
    $type_id = (int)$_POST['type_id'];

    // Check if ingredient exists
    $check_query = "SELECT * FROM ingredients WHERE name = '$name'";
    $check_result = mysqli_query($dbc, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Ingredient already exists']);
        exit;
    }    // Check if type_id exists
    $check_type_query = "SELECT * FROM ingredient_types WHERE type_id = $type_id";
    $check_type_result = mysqli_query($dbc, $check_type_query);
    if (mysqli_num_rows($check_type_result) == 0) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid ingredient type']);
        exit;
    }

    // Insert into the database
    $query = "INSERT INTO ingredients (name, unit, type_id) VALUES ('$name', '$unit', $type_id)";
    $result = mysqli_query($dbc, $query);

    if ($result) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Ingredient added successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Error adding ingredient: ' . mysqli_error($dbc)]);
    }
}

// Function to edit an existing ingredient
function editIngredient($dbc) {
    // Check required fields
    if (!isset($_POST['ingredient_id']) || !isset($_POST['name']) || !isset($_POST['unit']) || !isset($_POST['type_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }

    // Sanitize inputs
    $ingredient_id = (int)$_POST['ingredient_id'];
    $name = mysqli_real_escape_string($dbc, $_POST['name']);
    $unit = mysqli_real_escape_string($dbc, $_POST['unit']);
    $type_id = (int)$_POST['type_id'];

    // Check if ingredient exists (except the current one)
    $check_query = "SELECT * FROM ingredients WHERE name = '$name' AND ingredient_id != $ingredient_id";
    $check_result = mysqli_query($dbc, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Another ingredient with this name already exists']);
        exit;
    }

    // Update the database
    $query = "UPDATE ingredients SET name = '$name', unit = '$unit', type_id = $type_id WHERE ingredient_id = $ingredient_id";
    $result = mysqli_query($dbc, $query);

    if ($result) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Ingredient updated successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Error updating ingredient: ' . mysqli_error($dbc)]);
    }
}

// Function to delete an ingredient
function deleteIngredient($dbc) {
    // Check required fields
    if (!isset($_POST['ingredient_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Missing ingredient ID']);
        exit;
    }

    // Sanitize input
    $ingredient_id = (int)$_POST['ingredient_id'];

    // Check if ingredient is used in any products
    $check_query = "SELECT * FROM product_ingredients WHERE ingredient_id = $ingredient_id";
    $check_result = mysqli_query($dbc, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error', 
            'message' => 'This ingredient is used in one or more products. Please remove it from products first.'
        ]);
        exit;
    }

    // Delete from the database
    $query = "DELETE FROM ingredients WHERE ingredient_id = $ingredient_id";
    $result = mysqli_query($dbc, $query);

    if ($result) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Ingredient deleted successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Error deleting ingredient: ' . mysqli_error($dbc)]);
    }
}

// Function to add a new ingredient type
function addType($dbc) {
    // Check required fields
    if (!isset($_POST['type_name'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Missing type name']);
        exit;
    }

    // Sanitize input
    $type_name = mysqli_real_escape_string($dbc, $_POST['type_name']);

    // Check if type exists
    $check_query = "SELECT * FROM ingredient_types WHERE type_name = '$type_name'";
    $check_result = mysqli_query($dbc, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Ingredient type already exists']);
        exit;
    }

    // Insert into the database
    $query = "INSERT INTO ingredient_types (type_name) VALUES ('$type_name')";
    $result = mysqli_query($dbc, $query);

    if ($result) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Ingredient type added successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Error adding ingredient type: ' . mysqli_error($dbc)]);
    }
}

// Function to edit an existing ingredient type
function editType($dbc) {
    // Check required fields
    if (!isset($_POST['type_id']) || !isset($_POST['type_name'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }

    // Sanitize inputs
    $type_id = (int)$_POST['type_id'];
    $type_name = mysqli_real_escape_string($dbc, $_POST['type_name']);

    // Check if type exists (except the current one)
    $check_query = "SELECT * FROM ingredient_types WHERE type_name = '$type_name' AND type_id != $type_id";
    $check_result = mysqli_query($dbc, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Another ingredient type with this name already exists']);
        exit;
    }

    // Update the database
    $query = "UPDATE ingredient_types SET type_name = '$type_name' WHERE type_id = $type_id";
    $result = mysqli_query($dbc, $query);

    if ($result) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Ingredient type updated successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Error updating ingredient type: ' . mysqli_error($dbc)]);
    }
}

// Function to delete an ingredient type
function deleteType($dbc) {
    // Check required fields
    if (!isset($_POST['type_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Missing type ID']);
        exit;
    }

    // Sanitize input
    $type_id = (int)$_POST['type_id'];

    // Check if any ingredients use this type
    $check_query = "SELECT * FROM ingredients WHERE type_id = $type_id";
    $check_result = mysqli_query($dbc, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error', 
            'message' => 'This ingredient type is used by one or more ingredients. Please reassign those ingredients first.'
        ]);
        exit;
    }

    // Delete from the database
    $query = "DELETE FROM ingredient_types WHERE type_id = $type_id";
    $result = mysqli_query($dbc, $query);

    if ($result) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Ingredient type deleted successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Error deleting ingredient type: ' . mysqli_error($dbc)]);
    }
}
?>
