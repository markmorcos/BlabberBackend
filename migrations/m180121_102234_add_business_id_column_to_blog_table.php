<?php

use yii\db\Migration;

/**
 * Handles adding business_id to table `blog`.
 */
class m180121_102234_add_business_id_column_to_blog_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('blog', 'business_id', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('blog', 'business_id');
    }
}
