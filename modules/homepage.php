<?php
require_once __DIR__ . '/../config.php';

?>
<main>
	<!-- BANNER TO GUYS -->
<section id="home">
		<div class="banner text-white">
			<img src="<?php echo BASE_URL; ?>/images/MARJ.png" alt="Main Logo" class="logo">
			<h1>Welcome to Our Delivery Service</h1>
			<p>Your packages delivered on time, every time!</p>
		</div>
	</section>

	<!-- OUR SERVICES PART -->
	<section id="services" class="bg-light py-5">
		<div class="d-flex flex-column align-items-center space-around">
			<h2 class="text-center">Our Services</h2>
			<div class="row text-center">
				<div class="col-md-6 mb-4">
					<a href="<?php echo BASE_URL; ?>/modules/products.php">
						<img src="<?php echo BASE_URL; ?>/images/dg.jpg" class="img-fluid rounded" alt="Delivery">
						<h5 class="mt-2">Delivery</h5>
					</a>
				</div>
				<div class="col-md-6 mb-4">
					<a href="#catering">
						<img src="<?php echo BASE_URL; ?>/images/dg.jpg" class="img-fluid rounded" alt="Catering">
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
					<img src="<?php echo BASE_URL; ?>/images/dg.jpg" alt="Delivery Service" class="img-fluid">
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
					<h3>Follow Us</h3>
					<ul class="list-unstyled">
						<li><a href="https://facebook.com" target="_blank"><i class="fab fa-facebook"></i> Facebook</a></li>
						<li><a href="https://instagram.com" target="_blank"><i class="fab fa-instagram"></i> Instagram</a></li>
					</ul>
				</div>
				<div class="col-md-6">
					<h3>Send Us a Message</h3>
					<form action="<?php echo BASE_URL; ?>/contact_form_handler.php" method="POST">
						<div class="form-group">
							<label for="name">Name</label>
							<input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" required>
						</div>
						<div class="form-group">
							<label for="email">Email</label>
							<input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
						</div>
						<div class="form-group">
							<label for="message">Message</label>
							<textarea class="form-control" id="message" name="message" rows="4" placeholder="Enter your message" required></textarea>
						</div>
						<button type="submit" class="btn btn-primary">Submit</button>
					</form>
				</div>
			</div>
		</div>
	</section>

</main>

<!-- Footeeer yung nasa baba -->
<footer class="bg-dark text-white text-center py-3">
	<p>&copy; 2023 Delivery Service. All rights reserved.</p>
</footer>

<?php include('authenticate.php')?>

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

$(document).ready(function() {
    // Update cart count when page loads
    if (typeof updateCartCount === 'function') {
        updateCartCount();
    }
});
</script>
