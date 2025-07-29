<?php
// api/login.php

// Include the database connection file
require_once 'db.php';

// Start a session to manage user login state
// This should be at the very top before any output.
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// --- Main Logic ---

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get username and password from the POST request
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (empty($username) || empty($password)) {
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => 'Username and password are required.']);
        exit;
    }

    // Get database connection
    $pdo = getDBConnection();

    try {
        // Prepare SQL statement to find the user by username
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        // Fetch the user
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify if user exists and password is correct
        if ($user && password_verify($password, $user['password'])) {
            // Password is correct, so start a new session

            // Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);

            // Store user information in the session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Respond with success and user information
            http_response_code(200); // OK
            echo json_encode([
                'status' => 'ok',
                'message' => 'Login successful!',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username']
                ]
            ]);

        } else {
            // Invalid credentials
            http_response_code(401); // Unauthorized
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
        }
    } catch (PDOException $e) {
        // Catch any database exceptions
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }

} else {
    // Handle non-POST requests
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
