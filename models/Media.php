<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "media".
 *
 * @property string $id
 * @property string $url
 * @property string $type
 * @property string $user_id
 * @property string $object_id
 * @property string $object_type
 * @property string $created
 * @property string $updated
 */
class Media extends \yii\db\ActiveRecord
{
    /**
     * @var UploadedFile
     */
    public $file;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'media';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url', 'type', 'user_id', 'object_id', 'object_type'], 'required'],
            [['type', 'object_type'], 'string'],
            [['user_id', 'object_id'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['url'], 'string', 'max' => 255],

            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, mp4, pdf'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'Url',
            'type' => 'Type',
            'user_id' => 'User ID',
            'object_id' => 'Object ID',
            'object_type' => 'Object Type',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
