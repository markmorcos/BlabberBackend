<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "business_view".
 *
 * @property string $id
 * @property string $business_id
 * @property string $user_id
 * @property string $created
 */
class BusinessView extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'business_view';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['business_id'], 'required'],
            [['business_id', 'user_id'], 'integer'],
            [['created'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'business_id' => 'Business ID',
            'user_id' => 'User ID',
            'created' => 'Created',
        ];
    }

    public function getBusiness()
    {
        return $this->hasOne(Business::className(), ['id' => 'business_id']);
    }
}
