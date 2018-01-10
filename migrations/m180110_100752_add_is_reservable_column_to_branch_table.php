<?php

use yii\db\Migration;

/**
 * Handles adding is_reservable to table `branch`.
 */
class m180110_100752_add_is_reservable_column_to_branch_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('branch', 'is_reservable', $this->boolean());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('branch', 'is_reservable');
    }
}
