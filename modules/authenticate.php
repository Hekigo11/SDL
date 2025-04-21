<?php
require_once __DIR__ . '/../config.php';
?>
<!-- LOGIN MODAL -->
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="margin: 25vh auto;" role="document">
            <div class="modal-content" style="border-radius: 30px;">
                <div class="modal-header text-center position-center" style="background-color:var(--accent); border-radius: 30px 30px 0 0;">
                    <h5 class="modal-title text-light" id="loginModalLabel">Login</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php include("login.php"); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- LOGOUT MODAL -->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="margin: 25vh auto;" role="document">
            <div class="modal-content" style="border-radius: 30px;">
                <div class="modal-header text-center position-center" style="background-color:var(--accent); border-radius: 30px 30px 0 0;">
                    <h5 class="modal-title text-light" id="logoutModalLabel">Logout</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <p>Are you sure you want to log out?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <a href="<?php echo BASE_URL; ?>/modules/logout.php" class="btn btn-danger">Yes, Logout</a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>