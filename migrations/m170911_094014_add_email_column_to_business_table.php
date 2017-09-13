<?php

use yii\db\Migration;

/**
 * Handles adding email to table `business`.
 */
class m170911_094014_add_email_column_to_business_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('business', 'email', $this->string(255));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('business', 'email');
    }
}
