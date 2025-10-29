<?php

// Autoload Composer dependencies
require_once __DIR__ . '/../vendor/autoload.php';

use ProxyHunter\\Callback\\Config;
use ProxyHunter\\Callback\\ApiHandler;

// --- Security First: Authentication ---
$config = Config::getInstance();
$apiKey = $config->get('api_key');
$providedKey = $_SERVER['HTTP_X_PROXYHUNTER_KEY'] ?? '';

// Use hash_equals for timing-attack-safe comparison
if (empty($apiKey) || empty($providedKey) || !hash_equals($apiKey, $providedKey)) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// --- API Logic ---

// Ensure the 'id' parameter is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Bad Request: Missing interaction ID']);
    exit;
}

$interactionId = $_GET['id'];
$response = ['hit' => false, 'data' => []];

try {
    $apiHandler = new ApiHandler();
    $hits = $apiHandler->getHits($interactionId);

    if (!empty($hits)) {
        $response['hit'] = true;
        $response['data'] = $hits;
    }

} catch (\\Exception $e) {
    // If something goes wrong internally, return a generic server error.
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Internal Server Error']);
    exit;
}

// --- Response ---
header('Content-Type: application/json');
echo json_encode($response);
exit;
