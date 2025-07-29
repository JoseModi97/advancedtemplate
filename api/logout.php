<?php
// api/logout.php

// Start the session to access session variables
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Unset all session variables
$_SESSION = [];

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Respond with a success message
http_response_code(200); // OK
echo json_encode(['status' => 'ok', 'message' => 'You have been logged out successfully.']);

?>
