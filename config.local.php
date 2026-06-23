<?php
// Local override config values for development/testing.
// Copy this file to config.local.php and update values for your local environment.
return [
    // Local site URL for testing
    'SITE_URL' => 'http://localhost/posterwall-v2-final/posterwall2',

    // Local Google OAuth redirect URI
    'G_CLIENT_ID' => '230702917818-f3qmj48s0hmdhn70pk21s9pp8hmu78qo.apps.googleusercontent.com',
    'G_CLIENT_SECRET' => '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',
    'G_REDIRECT' => 'http://localhost/posterwall-v2-final/posterwall2/auth/callback.php',

    // Local database credentials
    'DB_HOST' => '127.0.0.1:3307',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    'DB_NAME' => 'posterwall2',

    // Debug mode
    'DEBUG_MODE' => true,

    // OpenRouter API key for Vision + HTML generation
    // 'OR_API_KEY' => 'your_openrouter_api_key_here',

    // Optionally override Razorpay / API keys for test mode
    // 'RZP_KEY_ID' => 'rzp_test_xxx',
    // 'RZP_KEY_SECRET' => 'xxx',
];
