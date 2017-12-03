<?php

use yii\db\Migration;

/**
 * Handles the creation of table `poll_option`.
 */
class m171203_082754_create_option_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('option', [
            'id' => $this->primaryKey(),
            'poll_id' => $this->integer()->notNull(),
            'option' => $this->string(255),
            'correct' => $this->boolean()->defaultValue(0),
            'created' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('option');
    }
}
