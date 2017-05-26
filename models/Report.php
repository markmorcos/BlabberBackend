<?php

namespace app\models;

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
            $image = Media::findOne($this->object_id);
            $preview = '<img src="'.Url::base(true).'/'.$image['url'].'" style="max-width: 700px;" />';
        }else if( $this->object_type === 'review' ){
            $review = Review::findOne($this->object_id);
            $preview = $review->text;
        }
        
        return $preview;
    }

    public function getLink()
    {
        if( $this->object_type === 'image' ){
            $link = "<a href='".Url::to(['media/view', 'id' => $this->object_id], true)."'>Link</a>";
        }else if( $this->object_type === 'review' ){
            $link = "<a href='".Url::to([$this->object_type.'/view', 'id' => $this->object_id], true)."'>Link</a>";
        }
        
        return $link;
    }
}
