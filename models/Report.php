<?php

namespace app\models;

use yii\db\Exception;
use yii\helpers\Url;

/**
 * This is the model class for table "report".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $object_id
 * @property string $object_type
 * @property string $created
 * @property string $updated
 */
class Report extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'report';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'object_id', 'object_type'], 'required'],
            [['user_id', 'object_id'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['object_type'], 'string', 'max' => 20],
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
            'object_id' => 'Object ID',
            'object_type' => 'Object Type',
            'preview' => 'Item Preview',
            'link' => 'Item Link',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getPreview()
    {
        if( $this->object_type === 'image' ){
            $object = Media::findOne($this->object_id);
            if (!empty($object)) {
                return '<img src="' . Url::base(true) . '/' . $object['url'] . '" style="max-width: 700px;" />';
            }
        }else if( $this->object_type === 'review' ){
            $object = Review::findOne($this->object_id);
            if (!empty($object)) {
                return $object->text;
            }
        }else if( $this->object_type === 'comment' ){
            $object = Comment::findOne($this->object_id);
            if (!empty($object)) {
                return $object->text;
            }
        }

        return 'not exits';
    }

    public function getLink()
    {
        if ($this->preview === 'not exits'){
            return '';
        }

        if( $this->object_type === 'image' ){
            return "<a href='".Url::to(['media/view', 'id' => $this->object_id], true)."'>Link</a>";
        }else{
            return "<a href='".Url::to([$this->object_type.'/view', 'id' => $this->object_id], true)."'>Link</a>";
        }
    }
}
