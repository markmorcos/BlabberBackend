<?php

namespace app\models;

use Yii;
use yii\helpers\Url;

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
            [['caption'], 'string', 'max' => 511],
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
            'caption' => 'Caption',
            'preview' => 'Preview',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getPreview()
    {
        if( in_array($this->type, ['business_image', 'category_badge', 'category_icon', 'category_image', 'flag_icon', 'image', 'menu', 'product', 'profile_photo', 'sponsor_image']) ){
            $preview = '<img src="'.Url::base(true).'/'.$this->url.'" style="max-width: 700px;" />';
        }else if( $this->type === 'video' ){
            $preview = '<video src="'.Url::base(true).'/'.$this->url.'" style="max-width: 700px;" />';
        }
        
        return $preview;
    }
}
