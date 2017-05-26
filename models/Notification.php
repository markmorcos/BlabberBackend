<?php

namespace app\models;

/**
 * This is the model class for table "notification".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $title
 * @property string $body
 * @property string $data
 * @property integer $seen
 * @property string $created
 * @property string $updated
 */
class Notification extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notification';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'title', 'body', 'data'], 'required'],
            [['user_id', 'seen'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['body'], 'string', 'max' => 511],
            [['data'], 'string', 'max' => 1023],
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
            'title' => 'Title',
            'body' => 'Body',
            'data' => 'Data',
            'seen' => 'Seen',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }
}
