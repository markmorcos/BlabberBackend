<?php

namespace app\models;

/**
 * This is the model class for table "user_interest".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $interest_id
 * @property string $created
 * @property string $updated
 */
class UserInterest extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_interest';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'interest_id'], 'required'],
            [['user_id', 'interest_id'], 'integer'],
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
            'interest_id' => 'Interest ID',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getInterest()
    {
        return $this->hasOne(Interest::className(), ['id' => 'interest_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
