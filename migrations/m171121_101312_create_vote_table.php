<?php

use yii\db\Migration;

/**
 * Handles the creation of table `vote`.
 */
class m171121_101312_create_vote_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('vote', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'poll_id' => $this->integer()->notNull(),
            'answer' => $this->string(255),
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
