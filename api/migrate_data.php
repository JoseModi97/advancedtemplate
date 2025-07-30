<?php
// api/migrate_data.php

require_once 'db.php';

// --- Configuration ---
define('OTDB_API_BASE_URL', 'https://opentdb.com/');
define('QUESTIONS_PER_REQUEST', 50); // Max allowed by the API

// --- Helper Functions ---

function fetchJson($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "cURL Error: $error\n";
        return null;
    }
    return json_decode($response, true);
}

function requestToken() {
    $tokenData = fetchJson(OTDB_API_BASE_URL . 'api_token.php?command=request');
    if (isset($tokenData['token'])) {
        echo "Session token obtained: {$tokenData['token']}\n";
        return $tokenData['token'];
    }
    echo "Failed to obtain session token.\n";
    return null;
}

function resetToken($token) {
    $tokenData = fetchJson(OTDB_API_BASE_URL . 'api_token.php?command=reset&token=' . $token);
    if (isset($tokenData['token'])) {
        echo "Session token reset successfully. New token: {$tokenData['token']}\n";
        return $tokenData['token'];
    }
    echo "Failed to reset session token.\n";
    return null;
}

// --- Main Logic ---

function migrate_data() {
    $pdo = getDBConnection();
    $token = requestToken();
    if (!$token) {
        return; // Stop if we can't get a token
    }

    // 1. Migrate Categories
    echo "Migrating categories...\n";
    $categoriesData = fetchJson(OTDB_API_BASE_URL . 'api_category.php');
    if (isset($categoriesData['trivia_categories'])) {
        $categories = $categoriesData['trivia_categories'];
        $stmt = $pdo->prepare("INSERT INTO categories (id, name) VALUES (:id, :name) ON DUPLICATE KEY UPDATE name = VALUES(name)");
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

        $totalCategories = count($categories);
        $currentCategoryIndex = 1;

        foreach ($categories as $category) {
            echo "----------------------------------------\n";
            echo "Fetching questions for category: {$category['name']} ({$currentCategoryIndex}/{$totalCategories})\n";

            $totalQuestionsMigrated = 0;

            while (true) {
                $apiUrl = OTDB_API_BASE_URL . 'api.php?amount=' . QUESTIONS_PER_REQUEST . '&category=' . $category['id'] . '&token=' . $token;
                $questionsData = fetchJson($apiUrl);

                if (!$questionsData) {
                    echo "Failed to fetch questions data. Moving to next category.\n";
                    break;
                }

                $responseCode = $questionsData['response_code'];

                if ($responseCode == 1) { // No results, but we might have gotten some questions before
                    echo "No more questions found for this category.\n";
                    break;
                }

                if ($responseCode == 4) { // Token has been exhausted
                    echo "Token exhausted. Resetting token...\n";
                    $token = resetToken($token);
                    if (!$token) return; // Stop if token reset fails
                    continue; // Retry the same category with the new token
                }

                if ($responseCode != 0) { // Other error
                    break; // Move to the next category
                }

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

                $count = count($questions);
                $totalQuestionsMigrated += $count;
                echo "Migrated $count questions...\n";


                // If we receive fewer than the max number of questions, we're done with this category
                if ($count < QUESTIONS_PER_REQUEST) {
                    echo "All questions for this category have been migrated.\n";
                    break;
                }

                // Add a small delay to avoid overwhelming the API
                sleep(5);
            }
            echo "Successfully migrated $totalQuestionsMigrated questions for category: {$category['name']}.\n";
            $currentCategoryIndex++;
        }
        echo "----------------------------------------\n";
        echo "All questions migrated successfully.\n";
    } else {
        echo "Could not fetch categories.\n";
    }
}

// Run the migration if the script is called directly
if (php_sapi_name() === 'cli') {
    migrate_data();
}
?>
