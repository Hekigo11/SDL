<?php
// Load PayMongo secret key from environment variable
$paymongo_secret_key = getenv('PAYMONGO_SECRET_KEY');

// For development/testing, you can set a default test key here
// WARNING: Never commit real secret keys to version control
if (empty($paymongo_secret_key)) {
    // This is a test key - replace with your actual test key
    $paymongo_secret_key = 'sk_test_thjRythHdEBKPzAqBVPJvKGE';
}

define('PAYMONGO_SECRET_KEY', $paymongo_secret_key);