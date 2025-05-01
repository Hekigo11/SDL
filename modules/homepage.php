<?php
require_once __DIR__ . '/../config.php';
if (isset($_SESSION['loginok']) && $_SESSION['role'] == 1) {
    header('Location: ' . BASE_URL . '/modules/admindashboard.php');
	if (!headers_sent()) {
        header('Location: ' . BASE_URL . '/modules/admindashboard.php');
        exit;
    } else {
        echo '<script>window.location.href="' . BASE_URL . '/modules/admindashboard.php";</script>';
        exit;
    }
}
?>
<main>
	<!-- BANNER TO GUYS -->
<section id="home">
		<div class="banner text-white">
			<img src="<?php echo BASE_URL; ?>/images/MARJ.png" alt="Main Logo" class="logo">
			<h1>Welcome to MARJ Food Service</h1>
			<p>From Our Kitchen to Your Table – With Love.</p>
		</div>
	</section>

	<!-- OUR SERVICES PART -->
	<section id="services" class="bg-light py-5">
		<div class="container">
			<div class="row justify-content-center mb-5">
				<div class="col-md-8">
					<h2 class="text-center display-4 mb-3">Our Services</h2>
					<p class="text-center text-muted">Choose from our premium food delivery or professional catering services</p>
				</div>
			</div>
			
			<div class="row">
				<div class="col-md-6 mb-4">
					<div class="service-card h-100">
						<a href="<?php echo BASE_URL; ?>/modules/products.php" class="text-decoration-none">
							<div class="card border-0 shadow-sm h-100">
								<div class="card-img-wrapper">
									<img src="<?php echo BASE_URL; ?>/images/deliver.jpg" 
										class="card-img-top" 
										alt="Delivery Service"
										style="height: 400px; object-fit: cover;">
								</div>
								<div class="card-body text-center">
									<h3 class="card-title h4 mb-3">Food Delivery</h3>
									<p class="card-text text-muted">Quick and convenient delivery straight to your doorstep</p>
									<button class="btn btn-outline-primary mt-3">Order Now</button>
								</div>
							</div>
						</a>
					</div>
				</div>
				
				<div class="col-md-6 mb-4">
					<div class="service-card h-100">
						<a href="#catering" class="text-decoration-none">
							<div class="card border-0 shadow-sm h-100">
								<div class="card-img-wrapper">
									<img src="<?php echo BASE_URL; ?>/images/cater.jpg" 
										class="card-img-top" 
										alt="Catering Service"
										style="height: 400px; object-fit: cover;">
								</div>
								<div class="card-body text-center">
									<h3 class="card-title h4 mb-3">Catering Service</h3>
									<p class="card-text text-muted">Professional catering for your special events</p>
									<button class="btn btn-outline-primary mt-3">Learn More</button>
								</div>
							</div>
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- WHAT KIND OF SERVICE PART -->
	<section id="wkos" class="py-5">
		<div class="container">
			<h2 class="text-center mb-5">What Kind of Service?</h2>
			
			<!-- Carousel -->
			<div id="mainCarousel" class="carousel slide mb-5" data-ride="carousel">
				<ol class="carousel-indicators">
					<li data-target="#mainCarousel" data-slide-to="0" class="active"></li>
					<li data-target="#mainCarousel" data-slide-to="1"></li>
					<li data-target="#mainCarousel" data-slide-to="2"></li>
				</ol>
				<div class="carousel-inner rounded shadow">
					<div class="carousel-item active">
						<img src="<?php echo BASE_URL; ?>/images/wkos_delivery.jpg" class="d-block w-100" alt="Delivery Service" style="height: 500px; object-fit: cover;">
						<div class="carousel-caption">
							<h3>Fast Delivery Service</h3>
							<p>Quick and reliable food delivery to your doorstep</p>
						</div>
					</div>
					<div class="carousel-item">
						<img src="<?php echo BASE_URL; ?>/images/wkos_catering.jpg" class="d-block w-100" alt="Catering Service" style="height: 500px; object-fit: cover;">
						<div class="carousel-caption">
							<h3>Professional Catering</h3>
							<p>Making your events special with our catering services</p>
						</div>
					</div>
					<div class="carousel-item">
						<img src="<?php echo BASE_URL; ?>/images/wkos_quality.jpg" class="d-block w-100" alt="Kitchen" style="height: 500px; object-fit: cover;">
						<div class="carousel-caption">
							<h3>Quality Food</h3>
							<p>Prepared with care in our professional kitchen</p>
						</div>
					</div>
				</div>
				<a class="carousel-control-prev" href="#mainCarousel" role="button" data-slide="prev">
					<span class="carousel-control-prev-icon" aria-hidden="true"></span>
					<span class="sr-only">Previous</span>
				</a>
				<a class="carousel-control-next" href="#mainCarousel" role="button" data-slide="next">
					<span class="carousel-control-next-icon" aria-hidden="true"></span>
					<span class="sr-only">Next</span>
				</a>
			</div>

			<!-- Description -->
			<div class="row justify-content-center">
				<div class="col-md-10">
					<p class="text-center">
						At MARJ Food Services, we specialize in providing home-style, quality-cooked meals crafted with care and experience. From small gatherings to regular food orders, we offer reliable catering and meal delivery services designed to bring comfort and satisfaction to every customer. With a fully equipped kitchen and a committed delivery team, we serve a wide range of clients—from loyal longtime patrons to new customers seeking delicious and dependable food. Built on a foundation of dedication, trust, and a deep passion for cooking, our services reflect years of industry experience, personalized attention, and an unwavering commitment to excellence
					</p>
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
					Marj Food Services is conveniently located at 9799 Kamagong Street in San Antonio Village, Makati, Philippines 1204. This central Makati location places us within easy reach of both residential and business clients throughout the metropolitan area.
					</p>
				</div>
				<div class="col-md-6">
					<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3861.5695831065186!2d121.00357437492329!3d14.566588985915761!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397c99e15d5ce81%3A0x568d57cf890d8c29!2s9799%20Kamagong%2C%20Makati%2C%201203%20Kalakhang%20Maynila!5e0!3m2!1sen!2sph!4v1745330629574!5m2!1sen!2sph" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
				</div>
			</div>
		</div>
	</section>

	<!-- ABOUT US PART -->
	<section id="about" class="py-5">
		<div class="container">
			<h2 class="text-center">About Us</h2>
			<p class="text-center">
				MARJ Food Services is a self-made business born out of necessity and passion. Founded by Raquel R. Dayao and Webster G. Dayao, the business was established in response to the challenges of low wages. With a deep love for cooking and management, Raquel leveraged her experience in the food industry to build something of her own.
				What started as a small venture—serving friends and clients during her employment years—grew through sheer hard work, commitment, and dedication. However, the journey was not without challenges. In the early days, transportation was a major hurdle, making food delivery impossible. To meet customer demands, Raquel created the illusion of a delivery service, knowing that her reputation for excellence would carry her through. Her customers, well aware of her capabilities, remained loyal and supportive.
				Through perseverance, MARJ Food Services expanded. Today, it operates with a full kitchen and a dedicated delivery service, a testament to Raquel’s resilience and determination. The business continues to thrive, adapting to modern food service trends while maintaining its core values. Though it has long since passed its peak, Raquel remains committed, ensuring that her passion keeps the business alive.
				Looking ahead, MARJ Food Services aims for longevity, with the hope that it will be passed down to someone who shares the same fire and dedication.
				The success of MARJ Food Services is also credited to its loyal team, who have stood by the business for over a decade. The company's key members include:
				 Lorena Pereira, Nelia Romero, Ricardo Romero, Danny Dulay, and Belinda Regacho, all of whom have dedicated 10–18 years of service.
				Their hard work and loyalty have been instrumental in shaping the business into what it is today.
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
					<p>Phone: +63 949 4257 628 / +63 917 8957 757</p>
					<p>Email: raquel_dayao1619@yahoo.com</p>
					<p>Telephone: (02) 899-1926 / (02) 896-2666</p>
					<h3>Follow Us</h3>
					<ul class="list-unstyled">
						<li><a href="https://www.facebook.com/MarjFoodServices" target="_blank"><i class="fab fa-facebook"></i> Facebook</a></li>
					</ul>
				</div>
				<div class="col-md-6">
					<h3>Send Us a Message</h3>
					<form action="<?php echo BASE_URL; ?>/#" method="POST">
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
    }, 3000);
}
function checkLogin(event) {
    <?php if(!isset($_SESSION['loginok'])) { ?>
        event.preventDefault();
		showAlert('You need to be logged in to view orders.', 'warning', true);
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
