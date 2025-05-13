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

	<!-- Chatbot Button and Window -->
	<div id="chatbotButton" style="position: fixed; bottom: 30px; right: 30px; z-index: 9999;">
		<button class="btn btn-primary rounded-circle shadow" style="width:60px; height:60px; font-size:28px;" onclick="openChatbot()">
			<i class="fas fa-comments"></i>
		</button>
	</div>
	<div id="chatbotWindow" class="card shadow" style="display:none; position: fixed; bottom: 100px; right: 30px; width: 350px; max-width: 90vw; z-index: 10000;">
		<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
			<span><i class="fas fa-robot mr-2"></i>MARJ FAQ Chatbot</span>
			<button type="button" class="close text-white" aria-label="Close" onclick="closeChatbot()">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="card-body" style="max-height: 400px; overflow-y: auto; font-size: 15px; background: #f8f9fa;">
			<div id="faqSection">
				<div class="mb-2 text-center font-weight-bold">How can I help you?</div>
				<div id="faqList">
					<!-- Questions will be rendered here by JS -->
				</div>
			</div>
			<div id="chatRoom" style="display:none;">
				<div id="chatHistory"></div>
				<button class="btn btn-link mt-2 p-0" onclick="backToFaqs()"><i class="fas fa-arrow-left"></i> Back to FAQs</button>
			</div>
		</div>
	</div>
</main>

<!-- Footeeer yung nasa baba -->
<footer class="bg-dark text-white text-center py-3">
	<p>&copy; 2023 Delivery Service. All rights reserved.</p>
</footer>

<?php include('authenticate.php')?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
<script>
const faqData = [
    {
        q: "What services does MARJ Food Services offer?",
        a: "We specialize in catering services for all types of events—birthdays, weddings, corporate functions, and more. We offer custom packages to suit your event size and needs."
    },
    {
        q: "How can I place an order?",
        a: "You can place an order through our website’s Order Now page, by sending us a message via our Contact Us form, or by reaching out through our social media channels."
    },
    {
        q: "Do you offer customized catering packages?",
        a: "Yes! We can tailor menus and packages based on your event size, preferences, and budget. Just let us know your requirements, and we’ll work with you."
    },
    {
        q: "How far in advance should I place my order?",
        a: "We recommend placing orders at least 3–5 days in advance for small events, and 2 weeks ahead for larger or more complex events to ensure availability and quality preparation."
    },
    {
        q: "Is there a minimum order requirement?",
        a: "Yes, we usually require a minimum order depending on the type of service (e.g., party trays vs. full catering). Contact us for specific details."
    },
    {
        q: "Do you accommodate dietary restrictions or allergies?",
        a: "Absolutely. We offer vegetarian, vegan, halal, and allergen-aware options. Please inform us in advance of any dietary needs or allergies."
    },
    {
        q: "Where are you located? Do you deliver?",
        a: "We are based in 9799 Kamagong Street in San Antonio Village, Makati, Philippines. Yes, we deliver within a specified radius. Delivery fees may apply depending on the location."
    },
    {
        q: "What payment methods do you accept?",
        a: "We accept payments via GCash, bank transfer, and cash on delivery. Payment details will be shared upon order confirmation."
    },
    {
        q: "Can I cancel or change my order after it’s confirmed?",
        a: "Yes, but changes or cancellations must be made at least 48 hours before your scheduled delivery or event. Late cancellations may incur charges."
    },
    {
        q: "Do you provide table setup, staff, or utensils?",
        a: "We offer full-service catering packages that include table setup, servers, and utensils. Please ask us about this option when placing your order."
    },
    {
        q: "How can I contact MARJ Food Services?",
        a: `You can reach us via:<br><br>Facebook: <a href=\"https://www.facebook.com/MarjFoodServices\" target=\"_blank\">https://www.facebook.com/MarjFoodServices</a><br>Email: raquel_dayao1619@yahoo.com<br>Phone: +63 949 4257 628 / +63 917 8957 757<br>Or use our Contact Us form on the website.`
    }
];
let chatHistory = [];
function openChatbot() {
    document.getElementById('chatbotWindow').style.display = 'block';
    document.getElementById('chatbotButton').style.display = 'none';
    showFaqSection();
}
function closeChatbot() {
    document.getElementById('chatbotWindow').style.display = 'none';
    document.getElementById('chatbotButton').style.display = 'block';
}
function showFaqSection() {
    document.getElementById('faqSection').style.display = 'block';
    document.getElementById('chatRoom').style.display = 'none';
    renderFaqList();
}
function renderFaqList() {
    const faqListDiv = document.getElementById('faqList');
    faqListDiv.innerHTML = '';
    faqData.forEach((item, idx) => {
        faqListDiv.innerHTML += `<button class='btn btn-light btn-block text-left mb-2 faq-btn' data-idx='${idx}' style='white-space:normal;'>${item.q}</button>`;
    });
    // Attach click events
    $(faqListDiv).find('.faq-btn').off('click').on('click', function() {
        const idx = $(this).data('idx');
        goToChatRoom(idx);
    });
}
function goToChatRoom(idx) {
    // Add to chat history
    chatHistory.push({q: faqData[idx].q, a: faqData[idx].a});
    renderChatRoom();
    document.getElementById('faqSection').style.display = 'none';
    document.getElementById('chatRoom').style.display = 'block';
}
function renderChatRoom() {
    const chatDiv = document.getElementById('chatHistory');
    chatDiv.innerHTML = '';
    chatHistory.forEach(item => {
        chatDiv.innerHTML += `<div class='mb-2'><span class='font-weight-bold text-primary'>You:</span> ${item.q}</div>`;
        chatDiv.innerHTML += `<div class='mb-3'><span class='font-weight-bold text-success'>MARJ Bot:</span> <span>${item.a}</span></div>`;
    });
    chatDiv.scrollTop = chatDiv.scrollHeight;
}
function backToFaqs() {
    document.getElementById('faqSection').style.display = 'block';
    document.getElementById('chatRoom').style.display = 'none';
}
$(document).ready(function() {
    // Update cart count when page loads
    if (typeof updateCartCount === 'function') {
        updateCartCount();
    }
});
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
		showAlert('You need to be logged in to do this action.', 'warning', true);
        return false;
    <?php } ?>
}
</script>
