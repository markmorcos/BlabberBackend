<?php

namespace app\models;

use yii\helpers\Url;

/**
 * This is the model class for table "vote".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $option_id
 * @property integer $business_id
 * @property string $vote
 * @property string $created
 * @property string $updated
 */
class Vote extends LikeableActiveRecored
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vote';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'option_id', 'business_id'], 'required'],
            [['user_id', 'option_id', 'business_id'], 'integer'],
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
            'user_id' => 'User',
            'option_id' => 'Option',
            'business_id' => 'Business',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getBusiness()
    {
       return $this->hasOne(Business::className(), ['id' => 'business_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getOption()
    {
        return $this->hasOne(Option::className(), ['id' => 'option_id']);
    }
}
