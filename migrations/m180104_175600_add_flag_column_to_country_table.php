<?php

use yii\db\Migration;

/**
 * Handles adding flag to table `country`.
 */
class m180104_175600_add_flag_column_to_country_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('country', 'flag', $this->string(255));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('country', 'flag');
    }
}
