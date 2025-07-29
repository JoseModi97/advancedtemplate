<?php
/** @var yii\web\View $this */
$this->title = 'Quiz';
?>
<div class="quiz-index text-center">
    <div class="jumbotron">
        <h1 class="display-4">Welcome to the Quiz!</h1>
        <p class="lead">Test your knowledge with our fun and challenging quiz.</p>
        <hr class="my-4">
        <p>
            <a class="btn btn-lg btn-success" href="<?= \yii\helpers\Url::to(['/quiz/start']) ?>">Start Quiz</a>
        </p>
    </div>
</div>
