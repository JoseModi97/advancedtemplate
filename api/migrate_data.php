<?php
// api/migrate_data.php

require_once 'db.php';

// --- Configuration ---
define('OTDB_API_CATEGORIES_URL', 'https://opentdb.com/api_category.php');
define('OTDB_API_QUESTIONS_URL', 'https://opentdb.com/api.php');
define('QUESTIONS_PER_CATEGORY', 50); // Number of questions to fetch per category

// --- Helper Functions ---

function fetchJson($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// --- Main Logic ---

function migrate_data() {
    $pdo = getDBConnection();

    // 1. Migrate Categories
    echo "Migrating categories...\n";
    $categoriesData = fetchJson(OTDB_API_CATEGORIES_URL);
    if (isset($categoriesData['trivia_categories'])) {
        $categories = $categoriesData['trivia_categories'];
        $stmt = $pdo->prepare("INSERT INTO categories (id, name) VALUES (:id, :name) ON DUPLICATE KEY UPDATE name = :name");
        foreach ($categories as $category) {
            $stmt->execute(['id' => $category['id'], 'name' => $category['name']]);
        }
        echo "Categories migrated successfully.\n";

        // 2. Migrate Questions for each category
        echo "Migrating questions...\n";
        $questionStmt = $pdo->prepare(
            "INSERT INTO questions (category_id, difficulty, question, correct_answer, incorrect_answers)
             VALUES (:category_id, :difficulty, :question, :correct_answer, :incorrect_answers)"
        );

        foreach ($categories as $category) {
            echo "Fetching questions for category: {$category['name']}...\n";
            $questionsData = fetchJson(OTDB_API_QUESTIONS_URL . '?amount=' . QUESTIONS_PER_CATEGORY . '&category=' . $category['id']);
            if (isset($questionsData['results'])) {
                $questions = $questionsData['results'];
                foreach ($questions as $question) {
                    $questionStmt->execute([
                        'category_id' => $category['id'],
                        'difficulty' => $question['difficulty'],
                        'question' => $question['question'],
                        'correct_answer' => $question['correct_answer'],
                        'incorrect_answers' => json_encode($question['incorrect_answers']),
                    ]);
                }
                echo "Successfully migrated " . count($questions) . " questions for category: {$category['name']}.\n";
            }
            // Add a small delay to avoid overwhelming the API
            sleep(1);
        }
        echo "Questions migrated successfully.\n";
    } else {
        echo "Could not fetch categories.\n";
    }
}

// Run the migration if the script is called directly
if (php_sapi_name() === 'cli') {
    migrate_data();
}
?>
