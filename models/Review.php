<?php

namespace app\models;

/**
 * This is the model class for table "review".
 *
 * @property integer $id
 * @property string $text
 * @property string $rating
 * @property integer $user_id
 * @property integer $business_id
 * @property string $created
 * @property string $updated
 */
class Review extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'review';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['text', 'rating', 'user_id', 'business_id'], 'required'],
            [['user_id', 'business_id'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['text'], 'string', 'max' => 1023],
            [['rating'], 'string', 'max' => 1],
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
            'rating' => 'Rating',
            'user_id' => 'User ID',
            'business_id' => 'Business ID',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getBusiness()
    {
        return $this->hasOne(Business::className(), ['id' => 'business_id']);
    }
}
