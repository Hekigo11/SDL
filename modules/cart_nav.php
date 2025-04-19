<?php
require_once __DIR__ . '/../config.php';
?>
<header>
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>/index.php" aria-label="Home">MARJ Food Services</a>
        
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#mobileNavMenu" aria-controls="mobileNavMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Desktop Navigation -->
        <div class="collapse navbar-collapse d-lg-flex justify-content-between" id="navbarNav">
            <ul class="navbar-nav pullDown">
                <li class="nav-item mx-2"><a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#home" data-section="home" aria-label="Home">Home</a></li>
                <li class="nav-item mx-2"><a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#services" data-section="services" aria-label="Services">Services</a></li>
                <li class="nav-item mx-2"><a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#about" data-section="about" aria-label="About Us">About Us</a></li>
                <li class="nav-item mx-2"><a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#contact" data-section="contact" aria-label="Contact Us">Contact Us</a></li>
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
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#home" data-section="home">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#cart" onclick="checkLogin(event)">My Orders</a></li>
                <li class="nav-item"><a class="nav-link" href="#" aria-label="Cater">Cater</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#services" data-section="services">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#about" data-section="about">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#contact" data-section="contact">Contact Us</a></li>
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

        // Add smooth scrolling to nav links with offset
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('a[href^="#"]');
            const navHeight = document.querySelector('.navbar').offsetHeight;
            const offset = navHeight + 30; // Adding extra padding
            
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    if(targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if(targetElement) {
                        const elementPosition = targetElement.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - offset;
                        
                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                        
                        // Close mobile menu if open
                        const mobileMenu = document.getElementById('mobileNavMenu');
                        if(mobileMenu.classList.contains('show')) {
                            mobileMenu.classList.remove('show');
                        }
                    }
                });
            });

            // Handle navigation back to index.php sections
            const navLinksToIndex = document.querySelectorAll('a[href*="index.php#"]');
            
            navLinksToIndex.forEach(link => {
                link.addEventListener('click', function(e) {
                    const section = this.getAttribute('data-section');
                    if (section) {
                        sessionStorage.setItem('scrollToSection', section);
                    }
                });
            });
        });
    </script>
</header>


