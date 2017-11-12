<?php

use yii\db\Migration;

/**
 * Handles adding approved to table `business`.
 */
class m171106_101019_add_approved_column_to_business_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('business', 'approved', $this->boolean()->defaultValue(1));
        $this->update('business', ['approved' => true]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('business', 'approved');
    }
}
