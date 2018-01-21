<?php

namespace app\models;

/**
 * This is the model class for table "blog".
 *
 * @property integer $id
 * @property integer $business_id
 * @property string $title
 * @property string $image
 * @property string $content
 * @property string $created
 * @property string $updated
 */
class Blog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'blog';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['business_id'], 'integer'],
            [['title', 'content'], 'required'],
            [['created', 'updated'], 'safe'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'business_id' => 'Business',
            'title' => 'Title',
            'image' => 'Image',
            'content' => 'Content',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getBusiness()
    {
        return $this->hasOne(Business::className(), ['id' => 'business_id']);
    }
}
