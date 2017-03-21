<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "comment".
 *
 * @property string $id
 * @property string $text
 * @property string $user_id
 * @property string $object_id
 * @property string $object_type
 * @property string $created
 * @property string $updated
 */
class Comment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['text', 'user_id', 'object_id', 'object_type'], 'required'],
            [['user_id', 'object_id', 'business_identity'], 'integer'],
            [['object_type'], 'string'],
            [['created', 'updated'], 'safe'],
            [['text'], 'string', 'max' => 1023],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'text' => 'Text',
            'user_id' => 'User ID',
            'object_id' => 'Object ID',
            'object_type' => 'Object Type',
            'business_identity' => 'Business Identity',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
