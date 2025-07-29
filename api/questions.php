<?php
// api/questions.php

// This script acts as a proxy to the Open Trivia Database (OTDB) API.
// It helps to hide the OTDB API structure from the client and can be used to manage session tokens on the server side.

// Set content type to JSON
header('Content-Type: application/json');

// --- Configuration ---
define('OTDB_API_BASE_URL', 'https://opentdb.com/api.php');

// --- Main Logic ---

// Check if it's a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Invalid request method. Please use GET.']);
    exit;
}

// Start session to manage the OTDB session token
session_start();

// --- API Parameters ---
// Get parameters from the query string
$amount = filter_input(INPUT_GET, 'amount', FILTER_VALIDATE_INT, ['options' => ['default' => 10, 'min_range' => 1, 'max_range' => 50]]);
$category = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);
$difficulty = filter_input(INPUT_GET, 'difficulty', FILTER_SANITIZE_STRING);
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);

// Retrieve our OTDB session token from the PHP session
$token = $_SESSION['otdb_token'] ?? null;

// --- Build the API URL ---
$queryParams = [
    'amount' => $amount,
];
if ($category) {
    $queryParams['category'] = $category;
}
if ($difficulty && in_array($difficulty, ['easy', 'medium', 'hard'])) {
    $queryParams['difficulty'] = $difficulty;
}
if ($type && in_array($type, ['multiple', 'boolean'])) {
    $queryParams['type'] = $type;
}
if ($token) {
    $queryParams['token'] = $token;
}

$apiUrl = OTDB_API_BASE_URL . '?' . http_build_query($queryParams);

// --- cURL Request ---
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10-second timeout

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// --- Handle Response ---
if ($curl_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to fetch data from the trivia API.', 'details' => $curl_error]);
    exit;
}

if ($httpcode !== 200) {
    http_response_code($httpcode);
    echo json_encode(['error' => 'Trivia API returned a non-200 status code.', 'response' => $response]);
    exit;
}

$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to parse JSON response from trivia API.']);
    exit;
}

// --- OTDB Response Code Handling ---
// See: https://opentdb.com/api_config.php
$responseCode = $data['response_code'];

if ($responseCode == 1) { // No Results
    // This is not a server error, but the API couldn't find questions for the query.
    // Return an empty array of results, which the client should handle gracefully.
    echo json_encode(['response_code' => $responseCode, 'results' => []]);

} elseif ($responseCode == 2) { // Invalid Parameter
    // This indicates a problem with our request.
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid parameter sent to the trivia API.']);

} elseif ($responseCode == 3 || $responseCode == 4) { // Token Not Found or Token Empty
    // The session token is invalid or has been exhausted.
    // We should reset it by requesting a new one.
    unset($_SESSION['otdb_token']); // Unset the invalid token
    // For a more advanced implementation, you could automatically fetch a new token and retry the request.
    // For now, we'll let the client know to try again.
    http_response_code(409); // Conflict - state of the token is no longer valid
    echo json_encode(['error' => 'Session token is invalid or exhausted. Please try again.']);

} elseif ($responseCode == 0) { // Success
    // The response is good. Echo it back to the client.
    // The client-side JS expects an array of questions directly
    echo json_encode($data['results']);

} else {
    // Unknown response code
    http_response_code(500);
    echo json_encode(['error' => 'Received an unknown response code from the trivia API.']);
}

?>
