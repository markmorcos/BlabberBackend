<?php

use yii\db\Migration;

/**
 * Handles adding color to table `category`.
 */
class m180116_143432_add_color_column_to_category_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('category', 'color', $this->string(9));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('category', 'color');
    }
}
