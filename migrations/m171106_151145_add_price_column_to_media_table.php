<?php

use yii\db\Migration;

/**
 * Handles adding price to table `media`.
 */
class m171106_151145_add_price_column_to_media_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('media', 'price', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('media', 'price');
    }
}
