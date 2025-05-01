<?php
session_start();
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
		<!-- jQuery library -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
		<!-- Popper JS -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
		<!-- Latest compiled JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <!-- Font Awesome For Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
		<link rel="stylesheet" href="vendor/style1.css">
		<title>MARJ Food Services</title>

        <style>
            .service-card {
                transition: transform 0.3s ease;
            }

            .service-card:hover {
                transform: translateY(-10px);
            }

            .card {
                border-radius: 15px;
                overflow: hidden;
            }

            .card-img-wrapper {
                overflow: hidden;
            }

            .card-img-top {
                transition: transform 0.5s ease;
            }

            .card-img-wrapper:hover .card-img-top {
                transform: scale(1.05);
            }

            .btn-outline-primary {
                border-width: 2px;
                padding: 8px 24px;
                border-radius: 25px;
            }

            .btn-outline-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            }

            .display-4 {
                font-weight: 600;
            }
            .carousel-caption {
                background: rgba(0, 0, 0, 0.7);
                padding: 20px;
                border-radius: 8px;
                bottom: 30px;
            }

            .carousel-inner {
                border-radius: 10px;
                overflow: hidden;
            }

            .carousel-indicators li {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                margin: 0 5px;
            }

            .shadow {
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            }
        </style>

	</head>

	<body>
		
		<?php include("modules/navigation.php");?>
		<?php include("modules/homepage.php"); ?>
        
		<script>
//NAVIGATION JS
$(document).ready(function() {
    // Mobile menu toggle
    $('.navbar-toggler').click(function(e) {
        e.preventDefault();
        $('#mobileNavMenu').addClass('active');
        $('#mobileNavOverlay').addClass('active');
        if (window.innerWidth <= 991) { 
            $('body').addClass('mobile-menu-open');
        }
    });
    
    // Close mobile menu and reset body overflow
    function closeMobileMenu() {
        $('#mobileNavMenu').removeClass('active');
        $('#mobileNavOverlay').removeClass('active');
        // $('body').css('overflow', '');
        $('body').removeClass('mobile-menu-open');
    }
    
    // Close mobile menu when clicking the close button
    $('.mobile-close-btn').click(function() {
        closeMobileMenu();
    });
    
    // Close mobile menu when clicking outside
    $('#mobileNavOverlay').click(function() {
        closeMobileMenu();
    });
    
    // Reset body overflow on window resize
    $(window).resize(function() {
        if (window.innerWidth > 991) {
            $('body').css('overflow', '');
        }
    });
    
    // Prevent menu from closing when clicking inside it
    $('#mobileNavMenu').click(function(e) {
        e.stopPropagation();
    });
    
    // Close mobile menu when clicking a menu item
    $('.mobile-menu-items a').click(function() {
        closeMobileMenu();
    });

    // Ensure modals work on mobile
    $('.btn[data-toggle="modal"]').on('click', function(e) {
        e.preventDefault();
        var targetModal = $(this).data('target');
        $(targetModal).modal('show');
    });

    // Close mobile menu when modal opens
    $('.modal').on('show.bs.modal', function () {
        $('#mobileNavMenu').removeClass('show');
    });
});

//END OF NAVIGATION JS
		</script>
	</body>
</html>

