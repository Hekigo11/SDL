
<header>
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="../index.php" aria-label="Home">MARJ Food Services</a>
        
        <button class="navbar-toggler custom-toggler" type="button" data-toggle="collapse" data-target="#mobileNavMenu" aria-controls="mobileNavMenu" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars" style="color: #fff;"></i>
        </button>
        
        <!-- Desktop Navigation -->
        <div class="collapse navbar-collapse d-lg-flex justify-content-between" id="navbarNav">
            <ul class="navbar-nav pullDown">
                <li class="nav-item mx-2"><a class="nav-link" href="../index.php#home" aria-label="Home">Home</a></li>
                <li class="nav-item mx-2"><a class="nav-link" href="../index.php#services" aria-label="Services">Services</a></li>
                <li class="nav-item mx-2"><a class="nav-link" href="../index.php#about" aria-label="About Us">About Us</a></li>
                <li class="nav-item mx-2"><a class="nav-link" href="../index.php#contact" aria-label="Contact Us">Contact Us</a></li>
            </ul>
            
            <div class="navbar-actions d-none d-lg-flex">
                <ul class="navbar-nav">
                    <li class="nav-item mx-2 no-dropdown">
                        <a href="cart.php" class="btn rounded-pill btn-outline-light" onclick="checkLogin(event)" aria-label="My Orders">My Orders</a>
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
                <li class="nav-item"><a class="nav-link" href="../index.php#home">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="cart.php" aria-label="My Orders">My Orders</a></li>
                <li class="nav-item"><a class="nav-link" href="../index.php#services">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="../index.php#about">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="../index.php#contact">Contact Us</a></li>
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
        function checkLogin(event) {
            <?php if(!isset($_SESSION['loginok'])) { ?>
                event.preventDefault();
                if(confirm('You need to be logged in to view orders. Would you like to login?')) {
                    $('#loginModal').modal('show');
                }
                return false;
            <?php } ?>
        }
    </script>
    
</header>
