<?php
/** @var yii\web\View $this */
/** @var int $score */
/** @var int $total */

$this->title = 'Quiz - Result';
?>
<div class="quiz-result text-center">
    <div class="jumbotron">
        <h1 class="display-4">Quiz Complete!</h1>
        <p class="lead">Your score: <?= $score ?> / <?= $total ?></p>
        <hr class="my-4">
        <p>
            <a class="btn btn-lg btn-success" href="<?= \yii\helpers\Url::to(['/quiz/start']) ?>">Try Again</a>
            <a class="btn btn-lg btn-primary" href="<?= \yii\helpers\Url::to(['/']) ?>">Back to Home</a>
        </p>
    </div>
</div>
