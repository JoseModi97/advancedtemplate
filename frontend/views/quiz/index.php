<?php
/** @var yii\web\View $this */
$this->title = 'Quiz';
?>
<div class="container mx-auto p-4">
    <header class="text-center py-8 relative">
        <h1 class="text-4xl font-bold text-blue-600">Trivia Quiz</h1>
    </header>

    <section id="settings-section" class="mb-8 p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold mb-4 text-gray-700">Quiz Settings</h2>
        <div class="mt-6 text-center">
            <a href="<?= \yii\helpers\Url::to(['/quiz/start']) ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow">
                Start Quiz
            </a>
        </div>
    </section>
</div>
