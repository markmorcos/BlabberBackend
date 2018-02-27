<?php

use yii\db\Migration;

/**
 * Handles adding location to table `user`.
 */
class m180227_144217_add_location_column_to_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('user', 'lat', $this->string(255));
        $this->addColumn('user', 'lng', $this->string(255));
        $this->addColumn('user', 'area_id', $this->integer(11));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('user', 'lat');
        $this->dropColumn('user', 'lng');
        $this->dropColumn('user', 'area_id');
    }
}
