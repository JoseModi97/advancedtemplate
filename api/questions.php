<?php
// api/questions.php

// This script fetches quiz questions from the local database.

require_once 'db.php';

header('Content-Type: application/json');

// --- Main Logic ---

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Invalid request method. Please use GET.']);
    exit;
}

// --- Parameters ---
$amount = filter_input(INPUT_GET, 'amount', FILTER_VALIDATE_INT, ['options' => ['default' => 10, 'min_range' => 1, 'max_range' => 50]]);
$category = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);
$difficulty = filter_input(INPUT_GET, 'difficulty', FILTER_SANITIZE_STRING);
// The 'type' parameter (multiple/boolean) is not directly supported in this simplified version, as we'd need to store it.
// We can add it to the 'questions' table if needed. For now, we ignore it.

$pdo = getDBConnection();

// --- Build Query ---
$sql = "SELECT question, correct_answer, incorrect_answers, difficulty FROM questions";
$params = [];

$whereClauses = [];
if ($category) {
    $whereClauses[] = "category_id = :category";
    $params[':category'] = $category;
}
if ($difficulty && in_array($difficulty, ['easy', 'medium', 'hard'])) {
    $whereClauses[] = "difficulty = :difficulty";
    $params[':difficulty'] = $difficulty;
}

if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}

// Randomize and limit the number of questions
$sql .= " ORDER BY RAND() LIMIT :amount";
$params[':amount'] = $amount;

try {
    $stmt = $pdo->prepare($sql);
    // Bind parameters
    foreach ($params as $key => &$val) {
        // PDOStatement::bindValue requires the third parameter to be explicit for LIMIT.
        if ($key === ':amount') {
            $stmt->bindValue($key, $val, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $val);
        }
    }

    $stmt->execute();
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // The client expects 'incorrect_answers' to be an array, but it's stored as JSON.
    $processedQuestions = array_map(function($q) {
        $q['incorrect_answers'] = json_decode($q['incorrect_answers']);
        return $q;
    }, $questions);

    echo json_encode($processedQuestions);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error fetching questions.', 'details' => $e->getMessage()]);
    exit;
}

?>
