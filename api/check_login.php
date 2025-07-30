<?php
// api/check_login.php
session_start();
require_once 'db.php'; // Include the database connection helper

header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    $pdo = getDBConnection();
    // Check if the user still exists in the database
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        // User exists, confirm session username matches for integrity
        $_SESSION['username'] = $user['username']; // Ensure session is fresh
        echo json_encode([
            'loggedIn' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username']
            ]
        ]);
    } else {
        // User not found in DB, so destroy session and report logged out
        session_destroy();
        echo json_encode(['loggedIn' => false, 'reason' => 'User not found']);
    }
} else {
    // No user_id in session
    echo json_encode(['loggedIn' => false]);
}
?>
