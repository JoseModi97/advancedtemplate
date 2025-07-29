<?php

namespace frontend\controllers;

use frontend\models\Answer;
use frontend\models\Question;
use Yii;
use yii\web\Controller;

/**
 * Quiz controller
 */
class QuizController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionStart()
    {
        $questions = Question::find()->asArray()->all();
        $questionIds = \yii\helpers\ArrayHelper::getColumn($questions, 'id');
        shuffle($questionIds);

        Yii::$app->session->set('quiz_questions', $questionIds);
        Yii::$app->session->set('quiz_current', 0);
        Yii::$app->session->set('quiz_score', 0);

        return $this->redirect(['question']);
    }

    public function actionQuestion()
    {
        $questionIds = Yii::$app->session->get('quiz_questions');
        $currentIndex = Yii::$app->session->get('quiz_current');

        if ($currentIndex >= count($questionIds)) {
            return $this->redirect(['result']);
        }

        $question = Question::findOne($questionIds[$currentIndex]);
        $answers = \yii\helpers\ArrayHelper::map($question->answers, 'id', 'answer');

        $model = new \yii\base\DynamicModel(['answer']);
        $model->addRule(['answer'], 'required');

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $answer = Answer::findOne($model->answer);
            if ($answer && $answer->is_correct) {
                $score = Yii::$app->session->get('quiz_score', 0);
                Yii::$app->session->set('quiz_score', $score + 1);
            }
            Yii::$app->session->set('quiz_current', $currentIndex + 1);
            return $this->redirect(['question']);
        }

        return $this->render('question', [
            'question' => $question,
            'answers' => $answers,
            'model' => $model,
        ]);
    }

    public function actionSubmit()
    {
        $questionIds = Yii::$app->session->get('quiz_questions');
        $currentIndex = Yii::$app->session->get('quiz_current');
        $question = Question::findOne($questionIds[$currentIndex]);

        $post = Yii::$app->request->post();
        if (isset($post['answer'])) {
            $answer = Answer::findOne($post['answer']);
            if ($answer && $answer->is_correct) {
                $score = Yii::$app->session->get('quiz_score', 0);
                Yii::$app->session->set('quiz_score', $score + 1);
            }
        }

        Yii::$app->session->set('quiz_current', $currentIndex + 1);

        return $this->redirect(['question']);
    }

    public function actionResult()
    {
        $score = Yii::$app->session->get('quiz_score', 0);
        $total = count(Yii::$app->session->get('quiz_questions', []));

        return $this->render('result', [
            'score' => $score,
            'total' => $total,
        ]);
    }
}
