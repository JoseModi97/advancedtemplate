<?php
// api/categories.php

// This script fetches the list of categories from the local database.

require_once 'db.php';

header('Content-Type: application/json');

// --- Main Logic ---

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Invalid request method. Please use GET.']);
    exit;
}

$pdo = getDBConnection();

try {
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($categories);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error fetching categories.', 'details' => $e->getMessage()]);
    exit;
}

?>
