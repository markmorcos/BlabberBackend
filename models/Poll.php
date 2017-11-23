<?php

namespace app\models;

use yii\helpers\Url;

/**
 * This is the model class for table "poll".
 *
 * @property integer $id
 * @property integer $business_id
 * @property integer $title
 * @property string $type
 * @property string $options
 * @property string $correct
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
            [['business_id', 'title', 'type', 'options', 'correct'], 'required'],
            [['title', 'type', 'options', 'correct'], 'string'],
            [['business_id'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['title', 'type', 'options', 'correct'], 'string', 'max' => 255],
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
            'type' => 'Type',
            'options' => 'Options',
            'correct' => 'Correct',
            'caption' => 'Caption',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getBusiness()
    {
        return $this->hasOne(Business::className(), ['id' => 'business_id']);
    }
}
