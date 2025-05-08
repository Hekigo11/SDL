<?php
require_once __DIR__ . '/../config.php';
include("dbconi.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
        case 'update':
            $address_id = $_POST['address_id'] ?? null;
            $street_number = mysqli_real_escape_string($dbc, $_POST['street_number']);
            $street_name = mysqli_real_escape_string($dbc, $_POST['street_name']);
            $barangay = mysqli_real_escape_string($dbc, $_POST['barangay']);
            $city = mysqli_real_escape_string($dbc, $_POST['city']);
            $province = mysqli_real_escape_string($dbc, $_POST['province']);
            $zip_code = mysqli_real_escape_string($dbc, $_POST['zip_code']);
            $label = mysqli_real_escape_string($dbc, $_POST['label']);
            // Handle custom label if "Other" is selected
            if ($label === 'Other' && !empty($_POST['customLabel'])) {
                $label = mysqli_real_escape_string($dbc, $_POST['customLabel']);
            }
            $is_default = isset($_POST['is_default']) ? 1 : 0;

            if ($is_default) {
                // Reset all other addresses to non-default
                mysqli_query($dbc, "UPDATE address SET is_default = 0 WHERE user_id = $user_id");
            }

            if ($action === 'add') {
                $query = "INSERT INTO address (user_id, street_number, street_name, barangay, city, province, zip_code, label, is_default) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($dbc, $query);
                mysqli_stmt_bind_param($stmt, "isssssssi", $user_id, $street_number, $street_name, $barangay, $city, $province, $zip_code, $label, $is_default);
            } else {
                $query = "UPDATE address SET street_number = ?, street_name = ?, barangay = ?, city = ?, province = ?, 
                         zip_code = ?, label = ?, is_default = ? WHERE address_id = ? AND user_id = ?";
                $stmt = mysqli_prepare($dbc, $query);
                mysqli_stmt_bind_param($stmt, "sssssssiis", $street_number, $street_name, $barangay, $city, $province, $zip_code, $label, $is_default, $address_id, $user_id);
            }

            if (mysqli_stmt_execute($stmt)) {
                $response['success'] = true;
                $response['message'] = $action === 'add' ? 'Address added successfully' : 'Address updated successfully';
            } else {
                $response['message'] = 'Error saving address';
            }
            break;

        case 'delete':
            $address_id = $_POST['address_id'] ?? 0;
            $query = "DELETE FROM address WHERE address_id = ? AND user_id = ?";
            $stmt = mysqli_prepare($dbc, $query);
            mysqli_stmt_bind_param($stmt, "ii", $address_id, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $response['success'] = true;
                $response['message'] = 'Address deleted successfully';
            } else {
                $response['message'] = 'Error deleting address';
            }
            break;

        case 'get':
            $query = "SELECT * FROM address WHERE user_id = ? ORDER BY is_default DESC, created_at DESC";
            $stmt = mysqli_prepare($dbc, $query);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $addresses = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $addresses[] = $row;
            }
            
            $response['success'] = true;
            $response['addresses'] = $addresses;
            break;
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>