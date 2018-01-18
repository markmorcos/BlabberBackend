<?php

use yii\db\Migration;

/**
 * Handles the creation of table `area`.
 */
class m180117_101318_create_area_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('area', [
            'id' => $this->primaryKey(),
            'city_id' => $this->integer(),
            'name' => $this->string(255),
            'nameAr' => $this->string(255),
            'lat' => $this->string(255),
            'lng' => $this->string(255),
            'created' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('area');
    }
}
