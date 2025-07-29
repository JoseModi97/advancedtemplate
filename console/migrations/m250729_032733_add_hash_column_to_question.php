<?php

use yii\db\Migration;

class m250729_032733_add_hash_column_to_question extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%question}}', 'question_hash', $this->string(40)->unique()->after('question'));
    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250729_032733_add_hash_column_to_question cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250729_032733_add_hash_column_to_question cannot be reverted.\n";

        return false;
    }
    */
}
