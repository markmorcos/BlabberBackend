<?php

use yii\db\Migration;

/**
 * Handles adding lang to table `user`.
 */
class m171024_120612_add_lang_column_to_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('user', 'lang', $this->string(2));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('business', 'email');
    }
}
