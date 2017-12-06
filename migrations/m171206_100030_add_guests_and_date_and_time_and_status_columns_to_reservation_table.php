<?php

use yii\db\Migration;

/**
 * Handles adding guests_and_date_and_time_and_status to table `reservation`.
 */
class m171206_100030_add_guests_and_date_and_time_and_status_columns_to_reservation_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('reservation', 'guests', $this->integer());
        $this->addColumn('reservation', 'date', $this->date());
        $this->addColumn('reservation', 'time', $this->time());
        $this->addColumn('reservation', 'status', 'ENUM("requested", "confirmed")');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('reservation', 'guests');
        $this->dropColumn('reservation', 'date');
        $this->dropColumn('reservation', 'time');
        $this->dropColumn('reservation', 'status');
    }
}
