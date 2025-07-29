<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "question".
 *
 * @property int $id
 * @property int $category_id
 * @property string $type
 * @property string $difficulty
 * @property string $question
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property Answer[] $answers
 * @property Category $category
 */
class Question extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const TYPE_MULTIPLE = 'multiple';
    const TYPE_BOOLEAN = 'boolean';
    const DIFFICULTY_EASY = 'easy';
    const DIFFICULTY_MEDIUM = 'medium';
    const DIFFICULTY_HARD = 'hard';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'question';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'default', 'value' => null],
            [['category_id', 'type', 'difficulty', 'question'], 'required'],
            [['category_id', 'created_at', 'updated_at'], 'integer'],
            [['type', 'difficulty', 'question'], 'string'],
            ['type', 'in', 'range' => array_keys(self::optsType())],
            ['difficulty', 'in', 'range' => array_keys(self::optsDifficulty())],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Category ID',
            'type' => 'Type',
            'difficulty' => 'Difficulty',
            'question' => 'Question',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Answers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAnswers()
    {
        return $this->hasMany(Answer::class, ['question_id' => 'id']);
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }


    /**
     * column type ENUM value labels
     * @return string[]
     */
    public static function optsType()
    {
        return [
            self::TYPE_MULTIPLE => 'multiple',
            self::TYPE_BOOLEAN => 'boolean',
        ];
    }

    /**
     * column difficulty ENUM value labels
     * @return string[]
     */
    public static function optsDifficulty()
    {
        return [
            self::DIFFICULTY_EASY => 'easy',
            self::DIFFICULTY_MEDIUM => 'medium',
            self::DIFFICULTY_HARD => 'hard',
        ];
    }

    /**
     * @return string
     */
    public function displayType()
    {
        return self::optsType()[$this->type];
    }

    /**
     * @return bool
     */
    public function isTypeMultiple()
    {
        return $this->type === self::TYPE_MULTIPLE;
    }

    public function setTypeToMultiple()
    {
        $this->type = self::TYPE_MULTIPLE;
    }

    /**
     * @return bool
     */
    public function isTypeBoolean()
    {
        return $this->type === self::TYPE_BOOLEAN;
    }

    public function setTypeToBoolean()
    {
        $this->type = self::TYPE_BOOLEAN;
    }

    /**
     * @return string
     */
    public function displayDifficulty()
    {
        return self::optsDifficulty()[$this->difficulty];
    }

    /**
     * @return bool
     */
    public function isDifficultyEasy()
    {
        return $this->difficulty === self::DIFFICULTY_EASY;
    }

    public function setDifficultyToEasy()
    {
        $this->difficulty = self::DIFFICULTY_EASY;
    }

    /**
     * @return bool
     */
    public function isDifficultyMedium()
    {
        return $this->difficulty === self::DIFFICULTY_MEDIUM;
    }

    public function setDifficultyToMedium()
    {
        $this->difficulty = self::DIFFICULTY_MEDIUM;
    }

    /**
     * @return bool
     */
    public function isDifficultyHard()
    {
        return $this->difficulty === self::DIFFICULTY_HARD;
    }

    public function setDifficultyToHard()
    {
        $this->difficulty = self::DIFFICULTY_HARD;
    }
}
