<?php
/** @var yii\web\View $this */
/** @var frontend\models\Question $question */
/** @var array $answers */
/** @var \yii\base\DynamicModel $model */

$this->title = 'Quiz - Question';
?>
<section id="quiz-section" class="mb-8 p-6 bg-white rounded-lg shadow-md" aria-labelledby="quiz-heading">
    <h2 id="quiz-heading" class="sr-only">Quiz Content</h2> <!-- For section landmark labelling -->
    <div id="question-container" class="mb-4">
        <h2 id="question-text" tabindex="-1" class="text-xl font-semibold text-gray-800 mb-3"><?= $question->question ?></h2> <!-- tabindex -1 to allow JS focus -->
        <div id="answers-container" class="space-y-2">
            <?php $form = \yii\widgets\ActiveForm::begin(); ?>

            <?= $form->field($model, 'answer')->radioList($answers, [
                'item' => function($index, $label, $name, $checked, $value) {
                    return '<div class="radio">' . \yii\helpers\Html::radio($name, $checked, ['value' => $value]) . '<label class="block w-full text-left p-3 my-2 rounded-md border border-gray-300 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition-colors duration-150">' . $label . '</label></div>';
                }
            ])->label(false) ?>

            <div class="form-group">
                <?= \yii\helpers\Html::submitButton('Next', ['class' => 'bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded']) ?>
            </div>

            <?php \yii\widgets\ActiveForm::end(); ?>
        </div>
    </div>
</section>
