<?php
// api/db.php

// Define the path to the SQLite database file
define('DB_FILE', __DIR__ . '/../db/quiz_app.db');

/**
 * Get the PDO database connection.
 * @return PDO
 */
function getDBConnection() {
    static $pdo = null; // Static variable to hold the connection

    if ($pdo === null) {
        try {
            // Create a new PDO instance. If the database file does not exist, it will be created.
            $pdo = new PDO('sqlite:' . DB_FILE);
            // Set PDO to throw exceptions on error, which is a good practice
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // If connection fails, stop the script and show an error.
            // In a real-world app, you might want to log this error instead of showing it.
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
            exit;
        }
    }
    return $pdo;
}

/**
 * Initializes the database, creating tables if they don't exist.
 */
function initializeDatabase() {
    $pdo = getDBConnection();

    try {
        // SQL for creating the 'users' table
        $usersTableSql = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL, -- Passwords should be hashed
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );";

        // SQL for creating the 'quiz_history' table
        $historyTableSql = "
        CREATE TABLE IF NOT EXISTS quiz_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            score INTEGER NOT NULL,
            total INTEGER NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        );";

        // Execute the SQL to create the tables
        $pdo->exec($usersTableSql);
        $pdo->exec($historyTableSql);

        // This is a good place to check if the script is run directly to initialize
        if (php_sapi_name() === 'cli' || realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)) {
             echo "Database and tables initialized successfully.\n";
        }

    } catch (PDOException $e) {
        // Handle potential errors during table creation
        http_response_code(500);
        // Using json_encode for consistency in API responses
        echo json_encode(['status' => 'error', 'message' => 'Table creation failed: ' . $e->getMessage()]);
        exit;
    }
}

// Check if the script is being run directly (e.g., from the command line or accessed via URL)
// This allows for manual initialization.
if (php_sapi_name() === 'cli' || (isset($_GET['init']) && $_GET['init'] === 'true')) {
    initializeDatabase();
}

?>
