<?php

use yii\db\Migration;

/**
 * Handles adding identifier to table `category`.
 */
class m180221_100750_add_identifier_column_to_category_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('category', 'identifier', $this->string(255));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('category', 'identifier');
    }
}
