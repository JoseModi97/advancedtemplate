<?php
// api/db.php

// --- Database Configuration ---
define('DB_HOST', 'localhost'); // e.g., 'localhost' or '127.0.0.1'
define('DB_NAME', 'trivia');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get the PDO database connection.
 * @return PDO
 */
function getDBConnection() {
    static $pdo = null; // Static variable to hold the connection

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // If connection fails, stop the script and show an error.
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
        // SQL for creating the 'users' table for MariaDB
        $usersTableSql = "
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(255) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        // SQL for creating the 'quiz_history' table for MariaDB
        $historyTableSql = "
        CREATE TABLE IF NOT EXISTS `quiz_history` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `score` INT NOT NULL,
            `total` INT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        // SQL for creating the 'categories' table
        $categoriesTableSql = "
        CREATE TABLE IF NOT EXISTS `categories` (
            `id` INT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        // SQL for creating the 'questions' table
        $questionsTableSql = "
        CREATE TABLE IF NOT EXISTS `questions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `category_id` INT NOT NULL,
            `difficulty` VARCHAR(50) NOT NULL,
            `question` TEXT NOT NULL,
            `correct_answer` VARCHAR(255) NOT NULL,
            `incorrect_answers` JSON NOT NULL,
            FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        // Execute the SQL to create the tables
        $pdo->exec($usersTableSql);
        $pdo->exec($historyTableSql);
        $pdo->exec($categoriesTableSql);
        $pdo->exec($questionsTableSql);

        // This is a good place to check if the script is run directly to initialize
        if (php_sapi_name() === 'cli' || (isset($_GET['init']) && $_GET['init'] === 'true')) {
             echo "Database and tables initialized successfully for MariaDB.\n";
        }

    } catch (PDOException $e) {
        // Handle potential errors during table creation
        http_response_code(500);
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
