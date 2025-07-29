<?php
/** @var yii\web\View $this */
/** @var frontend\models\Question $question */
/** @var array $answers */
/** @var \yii\base\DynamicModel $model */

$this->title = 'Quiz - Question';
?>
<div class="quiz-question">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><?= $question->question ?></h5>
        </div>
        <div class="card-body">
            <?php $form = \yii\widgets\ActiveForm::begin(); ?>

            <?= $form->field($model, 'answer')->radioList($answers, [
                'item' => function($index, $label, $name, $checked, $value) {
                    return '<div class="radio">' . \yii\helpers\Html::radio($name, $checked, ['value' => $value]) . '<label>' . $label . '</label></div>';
                }
            ])->label(false) ?>

            <div class="form-group">
                <?= \yii\helpers\Html::submitButton('Next', ['class' => 'btn btn-primary']) ?>
            </div>

            <?php \yii\widgets\ActiveForm::end(); ?>
        </div>
    </div>
</div>
