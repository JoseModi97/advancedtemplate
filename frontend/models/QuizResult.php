<?php

namespace frontend\models;

use Yii;
use common\models\User;

/**
 * This is the model class for table "quiz_result".
 *
 * @property int $id
 * @property int $user_id
 * @property int $score
 * @property int $total
 * @property int $created_at
 *
 * @property User $user
 */
class QuizResult extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'quiz_result';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'score', 'total'], 'required'],
            [['user_id', 'score', 'total', 'created_at'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'score' => 'Score',
            'total' => 'Total',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
