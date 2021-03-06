<?php

namespace app\models;

use yii\helpers\Url;

/**
 * This is the model class for table "poll".
 *
 * @property integer $id
 * @property string $title
 * @property string $titleAr
 * @property string $type
 * @property string $created
 * @property string $updated
 */
class Poll extends LikeableActiveRecored
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'poll';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'titleAr', 'type'], 'required'],
            [['created', 'updated'], 'safe'],
            [['title', 'titleAr', 'type'], 'string', 'max' => 255],
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
            'titleAr' => 'Title Ar',
            'type' => 'Type',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getBusiness()
    {
        return $this->hasOne(Business::className(), ['id' => 'business_id']);
    }
}
