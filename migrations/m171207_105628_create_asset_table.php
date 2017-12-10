<?php

use yii\db\Migration;

/**
 * Handles the creation of table `asset`.
 */
class m171207_105628_create_asset_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('asset', [
            'id' => $this->primaryKey(),
            'asset' => $this->string(255),
            'caption' => $this->string(255),
            'created' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('asset');
    }
}
