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
			// $(document).ready(function() {
			// 	$('.navbar-toggler').click(function() {
			// 		console.log('Burger menu clicked');
			// 	});
			// });

			$(document).ready(function() {
    // Handle mobile menu toggle
    $('.navbar-toggler').click(function() {
        $('#mobileNavMenu').toggleClass('show');
        
        // Close the other menu if open
        if ($('#navbarNav').hasClass('show')) {
            $('#navbarNav').removeClass('show');
        }
    });
    
    // Close mobile menu when clicking outside
    $(document).click(function(event) {
        if (!$(event.target).closest('.navbar-toggler, #mobileNavMenu').length) {
            $('#mobileNavMenu').removeClass('show');
        }
    });
    
    // Close mobile menu when clicking a nav link (for smoother UX)
    $('.mobile-menu .nav-link').click(function() {
        $('#mobileNavMenu').removeClass('show');
    });
});
		</script>
	</body>
</html>

