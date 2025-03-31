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

		<link rel="stylesheet" href="vendor/style1.css">
		<title>Deliver</title>

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
        if (window.innerWidth <= 991) {  // Only add overflow:hidden on mobile
            $('body').css('overflow', 'hidden');
        }
    });
    
    // Close mobile menu and reset body overflow
    function closeMobileMenu() {
        $('#mobileNavMenu').removeClass('active');
        $('#mobileNavOverlay').removeClass('active');
        $('body').css('overflow', '');
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

