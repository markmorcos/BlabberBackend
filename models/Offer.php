<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "offer".
 *
 * @property integer $id
 * @property string $title
 * @property string $titleAr
 * @property string $body
 * @property string $bodyAr
 * @property integer $business_id
 * @property string $image_url
 * @property integer $interest_id
 * @property integer $push
 * @property string $created
 * @property string $updated
 */
class Offer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'offer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['business_id', 'image_url', 'push'], 'required'],
            [['business_id', 'interest_id', 'push'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['title', 'titleAr', 'image_url'], 'string', 'max' => 255],
            [['body', 'bodyAr'], 'string', 'max' => 511],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'titleAr' => 'Title Ar',
            'body' => 'Body',
            'bodyAr' => 'Body Ar',
            'business_id' => 'Business',
            'image_url' => 'Image Url',
            'interest_id' => 'Interest',
            'push' => 'Push Notification',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getBusiness()
    {
        return $this->hasOne(Business::className(), ['id' => 'business_id']);
    }
}
