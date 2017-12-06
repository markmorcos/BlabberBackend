<?php

namespace app\models;

/**
 * This is the model class for table "reservation".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $business_id
 * @property string $mobile
 * @property integer $guests
 * @property string $date
 * @property string $time
 * @property string $notes
 * @property string $created
 * @property string $updated
 */
class Reservation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'reservation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'business_id', 'mobile'], 'required'],
            [['user_id', 'business_id', 'guests'], 'integer'],
            [['date', 'time', 'created', 'updated'], 'safe'],
            [['mobile'], 'string', 'max' => 20],
            [['notes'], 'string', 'max' => 1023],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User',
            'business_id' => 'Business',
            'mobile' => 'Mobile',
            'guests' => 'Guests',
            'date' => 'Date',
            'time' => 'Time',
            'notes' => 'Notes',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getBusiness()
    {
        return $this->hasOne(Business::className(), ['id' => 'business_id']);
    }
}
