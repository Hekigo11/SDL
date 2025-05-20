<?php

require_once __DIR__ . '/../config.php';

// Ensure only admins (role_id == 1) can access this page
if (!isset($_SESSION['loginok']) || $_SESSION['role'] != 1) {
    echo '<div class="alert alert-danger">Unauthorized access. You do not have permission to view this page.</div>';
    exit;
}

include("dbconi.php");

// Fetch all users
$users_query = "SELECT user_id, fname, lname, email_add, role_id FROM users ORDER BY lname, fname";
$users_result = mysqli_query($dbc, $users_query);

// Define role names directly in the script
// Adjust these as per your application's role definitions
$defined_roles = [
    1 => 'Admin',
    2 => 'Customer',
    3 => 'Staff'
    // Add other roles if they exist and are managed through role_id in the users table
];

?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Manage Users</h2>
        </div>
    </div>

    <div id="userManagementAlert"></div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Current Role</th>
                            <th>Change Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($users_result && mysqli_num_rows($users_result) > 0) {
                            while ($user = mysqli_fetch_assoc($users_result)) {
                        ?>
                        <tr data-user-id="<?php echo $user['user_id']; ?>">
                            <td><?php echo $user['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($user['fname'] . ' ' . $user['lname']); ?></td>
                            <td><?php echo htmlspecialchars($user['email_add']); ?></td>
                            <td class="current-role-name">
                                <?php 
                                // Display role name from our defined_roles map, or 'Unknown' if not found
                                echo isset($defined_roles[$user['role_id']]) ? htmlspecialchars($defined_roles[$user['role_id']]) : 'Unknown (ID: ' . htmlspecialchars($user['role_id']) . ')'; 
                                ?>
                            </td>
                            <td>
                                <?php if ($user['user_id'] != $_SESSION['user_id']) : // Prevent admin from changing their own role directly here ?>
                                <select class="form-control form-control-sm change-role-select" style="width: auto; height: fit-content;">
                                    <?php foreach ($defined_roles as $role_id_key => $role_name_display) : ?>
                                        <option value="<?php echo $role_id_key; ?>" <?php echo ($user['role_id'] == $role_id_key) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($role_name_display); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php else: ?>
                                    <span class="text-muted">Cannot change own role</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['user_id'] != $_SESSION['user_id']) : ?>
                                <button class="btn btn-sm btn-success save-role-btn" disabled>
                                    <i class="fas fa-save"></i> Save
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center">No users found.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    if ($.fn.DataTable) {
        $('#usersTable').DataTable({
            "order": [[1, "asc"]], // Sort by name
            "columnDefs": [
                { "orderable": false, "targets": [4, 5] } // Disable sorting for role change and action
            ]
        });
    }

    $('.change-role-select').on('change', function() {
        const $row = $(this).closest('tr');
        const originalRoleId = $row.find('.current-role-name').data('original-role-id'); 
        if (!originalRoleId) {
             // Store original role from the initially selected value in dropdown if not already stored
            const currentSelectedRoleIdInDropdown = $(this).find('option').filter(function() {
                return $(this).html().trim() === $row.find('.current-role-name').text().trim();
            }).val();
             $row.find('.current-role-name').data('original-role-id', currentSelectedRoleIdInDropdown || $(this).val());
        }
        
        const newRoleId = $(this).val();
        const currentRoleText = $row.find('.current-role-name').text().trim();
        const newRoleText = $(this).find('option:selected').text().trim();

        if (newRoleText !== currentRoleText) {
            $row.find('.save-role-btn').prop('disabled', false);
        } else {
            $row.find('.save-role-btn').prop('disabled', true);
        }
    });

    $(document).on('click', '.save-role-btn', function() {
        const $button = $(this);
        const $row = $button.closest('tr');
        const userId = $row.data('user-id');
        const newRoleId = $row.find('.change-role-select').val();
        const newRoleName = $row.find('.change-role-select option:selected').text();

        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        $('#userManagementAlert').empty();

        $.ajax({
            url: 'admin_user_actions.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'update_role',
                user_id: userId,
                role_id: newRoleId
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#userManagementAlert').html('<div class="alert alert-success alert-dismissible fade show" role="alert">' + response.message + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                    $row.find('.current-role-name').text(newRoleName).data('original-role-id', newRoleId);
                    $button.html('<i class="fas fa-save"></i> Save').prop('disabled', true);
                } else {
                    $('#userManagementAlert').html('<div class="alert alert-danger alert-dismissible fade show" role="alert">' + (response.message || 'Error updating role.') + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                    $button.html('<i class="fas fa-save"></i> Save').prop('disabled', false); // Re-enable if select is not matching current
                     const originalRoleId = $row.find('.current-role-name').data('original-role-id');
                     if(newRoleId != originalRoleId) {
                         $button.prop('disabled', false);
                     }
                }
            },
            error: function(xhr) {
                $('#userManagementAlert').html('<div class="alert alert-danger alert-dismissible fade show" role="alert">An error occurred: ' + xhr.responseText + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                $button.html('<i class="fas fa-save"></i> Save');
                const originalRoleId = $row.find('.current-role-name').data('original-role-id');
                 if(newRoleId != originalRoleId) {
                     $button.prop('disabled', false);
                 }
            }
        });
    });
});
</script>