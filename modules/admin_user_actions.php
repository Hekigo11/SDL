<?php

require_once __DIR__ . '/../config.php';
session_start();
include("dbconi.php");

header('Content-Type: application/json');

// Check if user is admin (role_id == 1)
if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

if (!isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'No action specified.']);
    exit;
}

$action = $_POST['action'];

switch ($action) {
    case 'update_role':
        updateUserRole($dbc);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        exit;
}

function updateUserRole($dbc) {
    if (!isset($_POST['user_id']) || !isset($_POST['role_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing user ID or role ID.']);
        exit;
    }

    $user_id_to_update = (int)$_POST['user_id'];
    $new_role_id = (int)$_POST['role_id'];
    $admin_user_id = (int)$_SESSION['user_id'];

    // Admin cannot change their own role through this action
    if ($user_id_to_update === $admin_user_id) {
        echo json_encode(['status' => 'error', 'message' => 'Administrators cannot change their own role using this form.']);
        exit;
    }
    
    // Ensure the target role is valid
    $valid_roles = [1, 2, 3]; // Admin, Customer, Staff
    if (!in_array($new_role_id, $valid_roles)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid role selected.']);
        exit;
    }

    $query = "UPDATE users SET role_id = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "ii", $new_role_id, $user_id_to_update);

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode(['status' => 'success', 'message' => 'User role updated successfully.']);
        } else {
            // This can happen if the role was already set to the new_role_id
            echo json_encode(['status' => 'success', 'message' => 'User role is already set to the selected value. No changes made.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update user role: ' . mysqli_error($dbc)]);
    }
    mysqli_stmt_close($stmt);
}

?>