<?php
// Load Stripe secret key from environment variable only
// Do NOT put any secret key directly in this file!
$paymongo_secret_key = getenv('STRIPE_TEST_KEY');
define('PAYMONGO_SECRET_KEY', $paymongo_secret_key);