<?php

use yii\db\Migration;

/**
 * Handles adding optionAr to table `option`.
 */
class m171205_095344_add_optionAr_column_to_option_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('option', 'optionAr', $this->string(255)->append('CHARACTER SET utf8 COLLATE utf8_general_ci'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('option', 'optionAr');
    }
}
