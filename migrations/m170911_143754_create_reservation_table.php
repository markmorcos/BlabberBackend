<?php

use yii\db\Migration;

/**
 * Handles the creation of table `reservation`.
 */
class m170911_143754_create_reservation_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('reservation', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'business_id' => $this->integer()->notNull(),
            'mobile' => $this->string(20),
            'notes' => $this->string(1023),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('reservation');
    }
}
