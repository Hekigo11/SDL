<?php
require_once __DIR__ . '/../config.php';

?>
<header>
    <nav class="navbar navbar-expand-lg">
        <!-- Alert Container -->
        <div id="alertContainer" class="position-fixed w-100" style="top: 70px; z-index: 1051;"></div>
        
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>/index.php" aria-label="Home">MARJ Food Services</a>
        
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#mobileNavMenu" aria-controls="mobileNavMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
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
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/modules/products.php" aria-label="Deliver">Deliver</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/modules/catering.php" aria-label="Cater">Cater</a></li>
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
</header>
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
</script>
<?php include('authenticate.php'); ?>


