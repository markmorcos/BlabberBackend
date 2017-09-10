<?php

namespace app\models;

/**
 * This is the model class for table "user_token".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $device_IMEI
 * @property string $auth_key
 * @property string $firebase_token
 * @property string $created
 * @property string $updated
 */
class UserToken extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_token';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'device_IMEI', 'auth_key'], 'required'],
            [['user_id'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['device_IMEI'], 'string', 'max' => 51],
            [['auth_key'], 'string', 'max' => 16],
            [['firebase_token'], 'string', 'max' => 255],
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
            'device_IMEI' => 'Device IMEI',
            'auth_key' => 'Auth Key',
            'firebase_token' => 'Firebase Token',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }
}
