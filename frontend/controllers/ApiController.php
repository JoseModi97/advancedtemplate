<?php

namespace frontend\controllers;

use yii\rest\Controller;

use common\models\User;
use frontend\models\Category;
use frontend\models\LoginForm;
use frontend\models\SignupForm;
use Yii;
use yii\web\Response;
use yii\rest\Controller;

class ApiController extends Controller
{
    public function actionCategories()
    {
        $categories = Category::find()->all();
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $categories;
    }

    public function actionQuestions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        $amount = $request->get('amount', 10);
        $category = $request->get('category');
        $difficulty = $request->get('difficulty');
        $type = $request->get('type');

        $query = Question::find()->with('answers');

        if ($category) {
            $query->andWhere(['category_id' => $category]);
        }

        if ($difficulty) {
            $query->andWhere(['difficulty' => $difficulty]);
        }

        if ($type) {
            $query->andWhere(['type' => $type]);
        }

        $questions = $query->orderBy('RAND()')->limit($amount)->asArray()->all();

        return $questions;
    }

    public function actionRegister()
    {
        $model = new SignupForm();
        $model->load(Yii::$app->request->post(), '');

        if ($model->signup()) {
            return ['status' => 'ok'];
        } else {
            return ['status' => 'error', 'errors' => $model->getErrors()];
        }
    }

    public function actionLogin()
    {
        $model = new LoginForm();
        $model->load(Yii::$app->request->post(), '');

        if ($model->login()) {
            return ['status' => 'ok', 'user' => Yii::$app->user->identity];
        } else {
            return ['status' => 'error', 'errors' => $model->getErrors()];
        }
    }

    public function actionResults()
    {
        $model = new QuizResult();
        $model->load(Yii::$app->request->post(), '');
        $model->user_id = Yii::$app->user->id;

        if ($model->save()) {
            return ['status' => 'ok'];
        } else {
            return ['status' => 'error', 'errors' => $model->getErrors()];
        }
    }

    public function actionHistory()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $history = QuizResult::find()->where(['user_id' => Yii::$app->user->id])->orderBy(['created_at' => SORT_DESC])->all();
        return $history;
    }
}
