<?php

use yii\db\Migration;

/**
 * Handles the creation of table `poll`.
 */
class m171121_100815_create_poll_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('poll', [
            'id' => $this->primaryKey(),
            'business_id' => $this->integer()->notNull(),
            'title' => $this->string(255),
            'type' => 'ENUM("cigarette")',
            'options' => $this->string(255),
            'correct' => $this->string(255),
            'created' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('poll');
    }
}
