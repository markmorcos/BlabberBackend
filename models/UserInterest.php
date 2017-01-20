<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_interest".
 *
 * @property string $id
 * @property string $interest_id
 * @property string $user_id
 * @property string $created
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
            [['interest_id', 'user_id'], 'required'],
            [['interest_id', 'user_id'], 'integer'],
            [['created'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'interest_id' => 'Flag ID',
            'user_id' => 'User ID',
            'created' => 'Created',
        ];
    }

    public function getInterest()
    {
        return $this->hasOne(Interest::className(), ['id' => 'interest_id']);
    }
}
