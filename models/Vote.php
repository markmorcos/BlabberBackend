<?php

namespace app\models;

use yii\helpers\Url;

/**
 * This is the model class for table "vote".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $poll_id
 * @property string $answer
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
            [['user_id', 'poll_id', 'answer'], 'required'],
            [['answer'], 'string'],
            [['user_id', 'poll_id'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['answer'], 'string', 'max' => 255],
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
            'poll_id' => 'Poll',
            'answer' => 'Answer',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getPoll()
    {
        return $this->hasOne(Poll::className(), ['id' => 'poll_id']);
    }
}
