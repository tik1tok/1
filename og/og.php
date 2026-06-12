<?php

// Set domain

$domain = 'downloadlocked.com';

// Headers

header("Access-Control-Allow-Origin: *");

// Input vars

$u = ltrim(filter_input(INPUT_GET, 'u'), '/');

if (empty($u)) {
    throw new Exception("Missing required query parameter 'u'.");
}

unset($_GET['u']);

// Get ip

$ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR');

// Get user agent

$user_agent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');

// Get referrer

$referrer = filter_input(INPUT_SERVER, 'HTTP_REFERER');

// Set URL

$url = "https://$domain/$u?" . http_build_query($_GET);

// Start CURL

$ch = curl_init();

// Set CURL options

curl_setopt_array($ch, [
    CURLOPT_URL            => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT      => $user_agent,
    CURLOPT_REFERER        => $referrer,
    CURLOPT_HTTPHEADER     => [
        'X-OGAds-Mirrored: true',
        'X-Forwarded-For: ' . $ip,
    ],
]);

// Execute request

$content = curl_exec($ch);

// Get the host and content type of the URL we were redirected to

$url_new      = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

// Check for error

if ($content === false) {

    // Throw exception if error found

    throw new Exception(curl_error($ch));

}

// Close CURL

curl_close($ch);

// Check URL host...

if (parse_url($url_new, PHP_URL_HOST) === $domain) {
        
    // If internal

    if (!is_null($content_type)) {

        // Set content type header

        header("Content-Type: $content_type");

    }

    // Output contents

    echo $content;

} else {

    // If external; redirect

    header("Location: $url_new");

}
