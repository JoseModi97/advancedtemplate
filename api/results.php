<?php
// api/results.php

require_once 'db.php';
session_start();

header('Content-Type: application/json');

// --- Security Check ---
// Ensure the user is logged in before allowing them to save results.
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to save quiz results.']);
    exit;
}

// --- Main Logic ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user ID from the POST request, ensuring it's an integer
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    // Get score and total from the POST request
    $score = filter_input(INPUT_POST, 'score', FILTER_VALIDATE_INT);
    $total = filter_input(INPUT_POST, 'total', FILTER_VALIDATE_INT);

    // --- Additional Security Check ---
    // Verify that the user ID from the request matches the one in the session.
    // This prevents a logged-in user from saving scores for another user.
    if (!$userId || $userId !== $_SESSION['user_id']) {
        http_response_code(403); // Forbidden
        echo json_encode(['status' => 'error', 'message' => 'User ID mismatch or invalid.']);
        exit;
    }

    // Validate the input
    // is_numeric is used because filter_input returns false for 0, which is a valid score.
    if ($score === false || $total === false || !is_numeric($_POST['score']) || !is_numeric($_POST['total'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => 'Invalid score or total provided. Both must be integers.']);
        exit;
    }

    // Further validation: score cannot be negative or greater than total
    if ($score < 0 || $total <= 0 || $score > $total) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid data: Score must be between 0 and total, and total must be positive.']);
        exit;
    }

    // Get database connection
    $pdo = getDBConnection();

    // Prepare SQL statement to insert the quiz result
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO quiz_history (user_id, score, total) VALUES (:user_id, :score, :total)"
        );

        // Bind parameters
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':score', $score, PDO::PARAM_INT);
        $stmt->bindParam(':total', $total, PDO::PARAM_INT);

        // Execute the statement
        if ($stmt->execute()) {
            // Respond with success message
            http_response_code(201); // Created
            echo json_encode(['status' => 'ok', 'message' => 'Quiz result saved successfully.']);
        } else {
            // Generic error if insertion fails
            http_response_code(500); // Internal Server Error
            echo json_encode(['status' => 'error', 'message' => 'Failed to save quiz result.']);
        }
    } catch (PDOException $e) {
        // Catch any database exceptions
        http_response_code(500);
        // In a production environment, you would log this error.
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }

} else {
    // Handle non-POST requests
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method. Please use POST.']);
}
?>
