<?php

namespace app\models;

use yii\helpers\Url;

/**
 * This is the model class for table "vote".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $option_id
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
            [['user_id', 'option_id'], 'required'],
            [['user_id', 'option_id'], 'integer'],
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
            'created' => 'Created',
            'updated' => 'Updated',
        ];
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
