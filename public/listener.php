<?php

// Autoload Composer dependencies
require_once __DIR__ . '/../vendor/autoload.php';

use ProxyHunter\Callback\Logger;

// The listener should be 100% passive and never fail loudly.
// It returns a 200 OK immediately to prevent the payload from hanging.
http_response_code(200);

// --- Data Capture ---

// 1. Determine Protocol (HTTPS/HTTP)
// Priority to X-Forwarded-Proto for compatibility with load balancers/proxies.
$protocol = 'HTTP';
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $protocol = 'HTTPS';
} elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    $protocol = 'HTTPS';
}

// 2. Extract Interaction ID from the subdomain
// e.g., "pxh-rce-abc123.callback.proxyhunter.com" -> "pxh-rce-abc123"
$interactionId = explode('.', $_SERVER['HTTP_HOST'])[0] ?? '';

// 3. Get Source IP
$sourceIp = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

// 4. Capture comprehensive request data into a JSON object
$requestData = json_encode([
    'method' => $_SERVER['REQUEST_METHOD'] ?? '',
    'path' => $_SERVER['REQUEST_URI'] ?? '',
    'query_string' => $_SERVER['QUERY_STRING'] ?? '',
    'headers' => getallheaders(),
    'body' => file_get_contents('php://input'),
]);

// --- Logging ---

// Only proceed if we have an interaction ID to log against.
if (!empty($interactionId)) {
    try {
        $logger = new Logger();
        $logger->logHit($interactionId, $protocol, $sourceIp, $requestData);
    } catch (\Exception $e) {
        // Fail silently. We do not want to alert the scanner or the victim machine.
        // In a debug scenario, this could be logged to a local file.
    }
}

// Ensure the script exits cleanly with no output.
exit;
