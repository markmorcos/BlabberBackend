<?php

namespace app\models;

/**
 * This is the model class for table "follow".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $receiver_id
 * @property string $status 0 -> requested, 1 -> accepted 
 * @property string $created
 * @property string $updated
 */
class Follow extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'follow';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'receiver_id', 'status'], 'required'],
            [['user_id', 'receiver_id'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['status'], 'string', 'max' => 1],
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
            'receiver_id' => 'Receiver ID',
            'status' => 'Status',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getReceiver()
    {
        return $this->hasOne(User::className(), ['id' => 'receiver_id']);
    }
}
