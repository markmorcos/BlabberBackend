<?php

use yii\db\Migration;

/**
 * Handles adding titleAr to table `poll`.
 */
class m171205_094905_add_titleAr_column_to_poll_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('poll', 'titleAr', $this->string(255)->append('CHARACTER SET utf8 COLLATE utf8_general_ci'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('poll', 'titleAr');
    }
}
