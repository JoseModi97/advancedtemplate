<?php
// api/register.php

// Include the database connection file
require_once 'db.php';

// Set content type to JSON
header('Content-Type: application/json');

// --- Helper Functions ---

/**
 * Validates the registration input.
 *
 * @param string $username The username to validate.
 * @param string $password The password to validate.
 * @return array An array of error messages. Empty if validation succeeds.
 */
function validateInput($username, $password) {
    $errors = [];
    if (empty($username)) {
        $errors['username'] = 'Username is required.';
    }
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 6) { // Example validation: password length
        $errors['password'] = 'Password must be at least 6 characters long.';
    }
    return $errors;
}

/**
 * Checks if a username already exists in the database.
 *
 * @param PDO $pdo The database connection object.
 * @param string $username The username to check.
 * @return bool True if the username exists, false otherwise.
 */
function isUsernameTaken(PDO $pdo, $username) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        // Log error or handle it as needed
        // For simplicity, we'll treat this as a server error
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error during username check.']);
        exit;
    }
}

// --- Main Logic ---

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get username and password from the POST request
    // Using null coalescing operator for safety
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate input
    $errors = validateInput($username, $password);
    if (!empty($errors)) {
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'errors' => $errors]);
        exit;
    }

    // Get database connection
    $pdo = getDBConnection();

    // Check if username is already taken
    if (isUsernameTaken($pdo, $username)) {
        http_response_code(409); // Conflict
        echo json_encode(['status' => 'error', 'errors' => ['username' => 'This username is already taken.']]);
        exit;
    }

    // Hash the password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL statement to insert new user
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

        // Execute the statement
        if ($stmt->execute()) {
            // Respond with success message
            http_response_code(201); // Created
            echo json_encode(['status' => 'ok', 'message' => 'Registration successful!']);
        } else {
            // Generic error if insertion fails
            http_response_code(500); // Internal Server Error
            echo json_encode(['status' => 'error', 'message' => 'Failed to register user.']);
        }
    } catch (PDOException $e) {
        // Catch any database exceptions
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }

} else {
    // Handle non-POST requests
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
