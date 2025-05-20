<?php
// Load Stripe secret key from environment variable
// Make sure to use a package like vlucas/phpdotenv if you want to support .env files directly, or rely on getenv if your environment loads them
$paymongo_secret_key = getenv('STRIPE_TEST_KEY');
define('PAYMONGO_SECRET_KEY', $paymongo_secret_key);