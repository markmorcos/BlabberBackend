<?php

use yii\db\Migration;

/**
 * Handles adding product to table `media`.
 */
class m180128_093101_add_product_column_to_media_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $type = "VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
        $this->addColumn('media', 'section', $type);
        $this->addColumn('media', 'sectionAr', $type);
        $this->addColumn('media', 'title', $type);
        $this->addColumn('media', 'titleAr', $type);
        $this->addColumn('media', 'captionAr', $type);
        $this->addColumn('media', 'currency', $type);
        $this->addColumn('media', 'currencyAr', $type);
        $this->addColumn('media', 'discount', $type);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('media', 'section');
        $this->dropColumn('media', 'sectionAr');
        $this->dropColumn('media', 'title');
        $this->dropColumn('media', 'titleAr');
        $this->dropColumn('media', 'captionAr');
        $this->dropColumn('media', 'currency');
        $this->dropColumn('media', 'currencyAr');
        $this->dropColumn('media', 'discount');
    }
}
