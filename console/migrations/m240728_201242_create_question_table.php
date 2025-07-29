<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%question}}`.
 */
class m240728_201242_create_question_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%question}}', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer()->notNull(),
            'type' => "ENUM('multiple', 'boolean') NOT NULL",
            'difficulty' => "ENUM('easy', 'medium', 'hard') NOT NULL",
            'question' => $this->text()->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey('fk-question-category', '{{%question}}', 'category_id', '{{%category}}', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-question-category', '{{%question}}');
        $this->dropTable('{{%question}}');
    }
}
