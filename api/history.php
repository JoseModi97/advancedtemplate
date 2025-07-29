<?php
// api/history.php

require_once 'db.php';
session_start();

header('Content-Type: application/json');

// --- Security Check ---
// Ensure the user is logged in to view their history.
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to view quiz history.']);
    exit;
}

// --- Main Logic ---

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get user ID from the session
    $userId = $_SESSION['user_id'];

    // Get database connection
    $pdo = getDBConnection();

    try {
        // Prepare SQL statement to select quiz history for the logged-in user
        // Order by creation date descending to show the most recent quizzes first
        $stmt = $pdo->prepare(
            "SELECT score, total, created_at FROM quiz_history WHERE user_id = :user_id ORDER BY created_at DESC"
        );

        // Bind the user_id parameter
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        // Execute the statement
        $stmt->execute();

        // Fetch all results
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // The client-side code expects timestamps in milliseconds for charts.
        // Let's convert the 'created_at' timestamp (which is a string like 'YYYY-MM-DD HH:MM:SS')
        // to a UNIX timestamp (seconds) and then multiply by 1000.
        $processedHistory = array_map(function($item) {
            // Convert SQL timestamp string to UNIX timestamp (integer)
            $timestamp = strtotime($item['created_at']);
            // Add a 'timestamp' field in milliseconds for JavaScript
            $item['timestamp'] = $timestamp * 1000;
            return $item;
        }, $history);


        // Respond with the fetched data
        http_response_code(200); // OK
        echo json_encode($processedHistory);

    } catch (PDOException $e) {
        // Catch any database exceptions
        http_response_code(500); // Internal Server Error
        // In production, log this error instead of echoing it.
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }

} else {
    // Handle non-GET requests
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method. Please use GET.']);
}
?>
