<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "friendship".
 *
 * @property string $id
 * @property string $user_id
 * @property string $friend_id
 * @property string $status 0 -> no action yet, 1 -> accepted, 2 -> rejected, 3 -> cancelled, 4 -> removed 
 * @property string $created
 * @property string $updated
 */
class Friendship extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'friendship';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'friend_id', 'status'], 'required'],
            [['user_id', 'friend_id'], 'integer'],
            [['created', 'updated'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'friend_id' => 'Friend ID',
            'status' => 'Status',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getFriend()
    {
        return $this->hasOne(User::className(), ['id' => 'friend_id']);
    }
}
