<?php
require_once __DIR__ . '/../config.php';
?>
<header>
    <nav class="navbar navbar-expand-lg">
        <!-- Alert Container -->
        <div id="alertContainer" class="position-fixed w-100" style="top: 70px; z-index: 1051;"></div>
        
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>/index.php" aria-label="Home">MARJ Food Services</a>
        
        <button class="navbar-toggler custom-toggler" type="button" data-toggle="collapse" data-target="#mobileNavMenu" aria-controls="mobileNavMenu" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars" style="color: #fff;"></i>
        </button>
        
        <!-- Desktop Navigation -->
        <div class="collapse navbar-collapse d-lg-flex justify-content-between" id="navbarNav">
            <ul class="navbar-nav pullDown">
                <li class="nav-item mx-2"><a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#home" aria-label="Home">Home</a></li>
                <li class="nav-item mx-2"><a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#services" aria-label="Services">Services</a></li>
                <li class="nav-item mx-2"><a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#about" aria-label="About Us">About Us</a></li>
                <li class="nav-item mx-2"><a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#contact" aria-label="Contact Us">Contact Us</a></li>
            </ul>
            
            <div class="navbar-actions d-none d-lg-flex">
                <ul class="navbar-nav">
                    <li class="nav-item mx-2 no-dropdown">
                        <a href="<?php echo BASE_URL; ?>/modules/cart.php" class="btn rounded-pill btn-outline-light" onclick="checkLogin(event)" aria-label="Cart">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge badge-light cart-count">0</span>
                        </a>
                    </li>
                    <li class="nav-item mx-2 no-dropdown">
                        <a href="<?php echo BASE_URL; ?>/modules/orders.php" class="btn rounded-pill btn-outline-light" onclick="checkLogin(event)" aria-label="My Orders">My Orders</a>
                    </li>
                    <?php
                    if(isset($_SESSION['loginok'])){
                        echo '<li class="nav-item mx-2 no-dropdown">
                        <button class="btn rounded-pill btn-outline-light" data-toggle="modal" data-target="#logoutModal" aria-label="Logout">Logout</button>
                        </li>';
                    } else {
                        echo '<li class="nav-item mx-2 no-dropdown">
                        <button class="btn rounded-pill btn-outline-light" data-toggle="modal" data-target="#loginModal" aria-label="Login">Login</button>
                        </li>';
                    }
                    ?>
                </ul>   
            </div>
        </div>
        
        <!-- Mobile Navigation Menu -->
        <div class="collapse navbar-collapse mobile-nav" style="background-color:var(--accent);" id="mobileNavMenu">
            <ul class="navbar-nav mobile-menu">
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#home">Home</a></li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/cart.php" onclick="checkLogin(event)">
                        <i class="fas fa-shopping-cart"></i> Cart
                        <span class="badge badge-light cart-count">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/orders.php" onclick="checkLogin(event)">My Orders</a>
                </li>
                <li class="nav-item"><a class="nav-link" href="#" aria-label="Cater">Cater</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#services">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#about">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#contact">Contact Us</a></li>
                <?php
                    if(isset($_SESSION['loginok'])){
                        echo '<li class="nav-item">
                                <div class="d-flex flex-column">
                                <button class="btn btn-light mb-2 w-100" data-toggle="modal" data-target="#logoutModal">Logout</button>
                                </div>
                            </li>';
                    } else {
                        echo '<li class="nav-item">
                                <div class="d-flex flex-column">
                                <button class="btn btn-light mb-2 w-100" data-toggle="modal" data-target="#loginModal">Login</button>
                                </div>
                            </li>';
                    }
                    ?>
            </ul>
        </div>
    </nav>

    <script>
    function showAlert(message, type = 'warning', showLoginButton = false) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show mx-3`;
        alertDiv.role = 'alert';
        
        let alertContent = `
            <div class="d-flex justify-content-between align-items-center">
                <div>${message}</div>
                ${showLoginButton ? '<button type="button" class="btn btn-primary btn-sm mx-2" onclick="$(\'#loginModal\').modal(\'show\')">Login Now</button>' : ''}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        
        alertDiv.innerHTML = alertContent;
        document.getElementById('alertContainer').appendChild(alertDiv);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            $(alertDiv).alert('close');
        }, 5000);
    }

    function checkLogin(event) {
        <?php if(!isset($_SESSION['loginok'])) { ?>
            event.preventDefault();
            showAlert('You need to be logged in to view orders.', 'warning', true);
            return false;
        <?php } ?>
    }

    $(document).ready(function() {
        // Function to clear catering sessions when navigating away from catering pages
        function clearCateringSessions() {
            const currentPath = window.location.pathname;
            // Only clear if we're on a catering page
            if (currentPath.includes('/modules/catering/')) {
                $.get('<?php echo BASE_URL; ?>/modules/clear_catering_session.php');
            }
        }

        // Attach event handlers to navigation links that aren't catering-related
        $('a.nav-link').not('[href*="catering"]').click(function(e) {
            clearCateringSessions();
        });

        // Existing code
        function updateCartCount() {
            <?php if(isset($_SESSION['loginok'])) { ?>
                $.get('<?php echo BASE_URL; ?>/modules/get_cart_count.php', function(response) {
                    if (response.success) {
                        $('.cart-count').text(response.count);
                    }
                }, 'json');
            <?php } else { ?>
                $('.cart-count').text('0');
            <?php } ?>
        }
    });
    </script>
    <?php include('authenticate.php'); ?>
    
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
</header>
