<?php
// api/categories.php

// This script fetches the list of categories from the Open Trivia Database (OTDB).
// It acts as a simple proxy to avoid direct client-side requests to the external API.

// Set content type to JSON
header('Content-Type: application/json');

// --- Configuration ---
define('OTDB_API_CATEGORIES_URL', 'https://opentdb.com/api_category.php');

// --- cURL Request ---
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, OTDB_API_CATEGORIES_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10-second timeout

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// --- Handle Response ---
if ($curl_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to fetch categories from the trivia API.', 'details' => $curl_error]);
    exit;
}

if ($httpcode !== 200) {
    http_response_code($httpcode);
    echo json_encode(['error' => 'Trivia API returned a non-200 status code for categories.', 'response' => $response]);
    exit;
}

$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to parse JSON response from trivia API categories endpoint.']);
    exit;
}

// The OTDB returns category data inside a 'trivia_categories' key.
// The client-side code expects a direct array of categories.
// We will extract it before sending it to the client.
if (isset($data['trivia_categories'])) {
    echo json_encode($data['trivia_categories']);
} else {
    // If the expected key is not found, return an empty array or an error.
    http_response_code(500);
    echo json_encode(['error' => 'Category data not found in the expected format from the trivia API.']);
}

?>
