<?php
/** @var yii\web\View $this */
/** @var int $score */
/** @var int $total */

$this->title = 'Quiz - Result';
?>
<section id="results-section" class="p-6 bg-white rounded-lg shadow-md" aria-labelledby="results-heading">
    <h2 id="results-heading" tabindex="-1" class="text-2xl font-semibold mb-4 text-center text-gray-700">Quiz Results</h2> <!-- tabindex -1 to allow JS focus -->
    <div id="score-container" class="text-lg text-center mb-4">
        <p>Correct Answers: <span id="correct-answers" class="font-bold text-green-600"><?= $score ?></span></p>
        <p>Incorrect Answers: <span id="incorrect-answers" class="font-bold text-red-600"><?= $total - $score ?></span></p>
        <p>Final Score: <span id="final-score" class="font-bold text-blue-600"><?= $total > 0 ? round(($score / $total) * 100) : 0 ?>%</span></p>
    </div>
    <div class="text-center mt-6">
        <a href="<?= \yii\helpers\Url::to(['/quiz/start']) ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow">
            Retake Quiz
        </a>
        <a href="<?= \yii\helpers\Url::to(['/']) ?>" class="ml-4 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-lg shadow">
            New Settings
        </a>
    </div>
</section>
