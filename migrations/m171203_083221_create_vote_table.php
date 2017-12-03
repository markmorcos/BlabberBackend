<?php

use yii\db\Migration;

/**
 * Handles the creation of table `poll_vote`.
 */
class m171203_083221_create_vote_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('vote', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'option_id' => $this->integer()->notNull(),
            'created' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('vote');
    }
}
