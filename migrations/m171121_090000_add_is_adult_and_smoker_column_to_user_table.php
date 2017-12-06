<?php

use yii\db\Migration;

/**
 * Handles adding is_adult_and_smoker to table `user`.
 */
class m171121_090000_add_is_adult_and_smoker_column_to_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('user', 'is_adult_and_smoker', $this->string(1));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('user', 'is_adult_and_smoker');
    }
}
