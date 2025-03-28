
<header>
	<nav class="navbar nav">
		<div class="container d-flex justify-content-between align-items-center">
			<button class="navbar-toggler" type="button" id="burgerMenu" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>

			<a class="navbar-brand mx-auto" href="index.php">JIM</a> <!-- Change Reference -->

			<a href="#" class="btn btn-outline-primary">Cart</a> <!-- Change Reference -->
		</div>
	</nav>

	<div class="custom-dropdown" id="customDropdown">
		<ul class="navbar-nav">
			<li class="nav-item">
				<a class="nav-link" href="#home">Home</a> 
			</li>
			<li class="nav-item">
				<a class="nav-link" href="#" data-toggle="modal" data-target="#loginModal">Login</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="#services">Services</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="#about">About Us</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="#contact">Contact</a>
			</li>
		</ul>
	</div>

</header>

<main>
	<!-- BANNER TO GUYS -->
	<section id="home" class="hero bg-primary text-white text-center py-5">
		<div class="container">
			<h1>Welcome to Our Delivery Service</h1>
			<p>Your packages delivered on time, every time!</p>
			<!-- <a href="#contact" class="btn btn-light">Get Started</a> -->
		</div>
	</section>

	<!-- OUR SERVICES PART -->
	<section id="services" class="bg-light py-5">
		<div class="container">
			<h2 class="text-center">Our Services</h2>
			<!-- ANDITO YUNG 2 IMAGES -->
			<div class="row text-center">
				<div class="col-md-6 mb-4">
					<a href="#delivery"> <!--Change Reference-->
						<img src="images/dg.jpg" class="img-fluid" alt="Delivery"> <!--Change IMG-->
						<h5 class="mt-2">Delivery</h5>
					</a>
				</div>
				<div class="col-md-6 mb-4">
					<a href="#catering"> <!--Change Reference-->
						<img src="images/dg.jpg" class="img-fluid" alt="Catering"> <!--Change IMG-->
						<h5 class="mt-2">Catering</h5>
					</a>
				</div>
			</div>

		</div>
	</section>

	<!-- WHAT KIND OF SERVICE PART -->
	<section id="wkos" class="py-5">
		<div class="container">
			<h2 class="text-center">What Kind of Service?</h2>
			<div class="row">
				<div class="col-md-6">
					<p class="text-justify">
					Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
					</p>
				</div>
				<div class="col-md-6">
					<img src="images/dg.jpg" alt="Delivery Service" class="img-fluid">
				</div>
			</div>
		</div>
	</section>

	<!-- LOCATION PART -->
	<section id="location" class="py-5">
		<div class="container">
			<h2 class="text-center">Location</h2>
			<div class="row">
				<div class="col-md-6">
					<p class="text-justify">
					Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
					</p>
				</div>
				<div class="col-md-6">
					<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7722.507035746922!2d120.98203526711872!3d14.58462483667834!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397ca20f5c0aea9%3A0x52dea1e12d0145a0!2sAdamson%20University%20(AdU)!5e0!3m2!1sen!2sph!4v1742358958145!5m2!1sen!2sph" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
				</div>
			</div>
		</div>
	</section>

	<!-- ABOUT US PART -->
	<section id="about" class="py-5">
		<div class="container">
			<h2 class="text-center">About Us</h2>
			<p class="text-center">
				Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.	
			</p>
		</div>
	</section>

	<!-- CONTACT US PART -->
		<!-- paltan mga contact infoooss -->
	<section id="contact" class="py-5">
		<div class="container">
			<div class="row">
				<div class="col-md-6">
					<h3>Contact Us</h3>
					<p>Phone: 0917-123-4567</p>
					<p>Email: marj123@gmail.com</p>
					<p>Telephone: (02) 123-4567</p>
				</div>
				<div class="col-md-6">
					<h3>Follow Us</h3>
					<ul class="list-unstyled">
					<!-- change yung href at pwede gawing logo shitt -->
					<li><a href="#" target="_blank"><i class="fab fa-facebook"></i> Facebook</a></li>
					<li><a href="#" target="_blank"><i class="fab fa-instagram"></i> Instagram</a></li>
					</ul>
				</div>
			</div>
		</div>
	</section>

</main>

<!-- Footeeer yung nasa baba -->
<footer class="bg-dark text-white text-center py-3">
	<p>&copy; 2023 Delivery Service. All rights reserved.</p>
</footer>

<!-- LOGIN MODAL -->
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="loginModalLabel">Login</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<!-- Include login.php here -->
				<?php include("login.php"); ?>
				
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function() {
		$('#burgerMenu').click(function() {
			$('#customDropdown').toggleClass('show');
		});
	});
</script>