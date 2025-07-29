<?php
// api/api_token.php

// This script handles fetching and resetting session tokens from the Open Trivia Database (OTDB).

// Set content type to JSON
header('Content-Type: application/json');

// --- Configuration ---
define('OTDB_API_TOKEN_URL', 'https://opentdb.com/api_token.php');

// --- Main Logic ---

// Get the command from the query string (e.g., ?command=request)
$command = filter_input(INPUT_GET, 'command', FILTER_SANITIZE_STRING);

if (!$command || !in_array($command, ['request', 'reset'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['response_code' => 2, 'response_message' => 'Invalid command. Use "request" or "reset".']);
    exit;
}

// --- Build the API URL ---
$queryParams = ['command' => $command];

// If resetting, a token must be provided
if ($command === 'reset') {
    $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
    if (!$token) {
        http_response_code(400);
        echo json_encode(['response_code' => 2, 'response_message' => 'Token is required for reset command.']);
        exit;
    }
    $queryParams['token'] = $token;
}

$apiUrl = OTDB_API_TOKEN_URL . '?' . http_build_query($queryParams);

// --- cURL Request ---
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// --- Handle Response ---
if ($curl_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to communicate with the trivia API.', 'details' => $curl_error]);
    exit;
}

if ($httpcode !== 200) {
    http_response_code($httpcode);
    echo json_encode(['error' => 'Trivia API returned a non-200 status code.', 'response' => $response]);
    exit;
}

// The OTDB token API response is JSON, so we decode and re-encode it.
// This allows us to potentially intercept and modify it in the future if needed.
$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to parse JSON response from trivia API.']);
    exit;
}

// If a new token was successfully requested, store it in the user's session.
// This centralizes token management on the server.
if ($command === 'request' && isset($data['response_code']) && $data['response_code'] === 0 && isset($data['token'])) {
    session_start();
    $_SESSION['otdb_token'] = $data['token'];
}

// Echo the original response from the OTDB API to the client
echo $response;

?>
