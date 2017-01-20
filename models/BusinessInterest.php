<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "business_interest".
 *
 * @property string $id
 * @property string $interest_id
 * @property string $business_id
 * @property string $created
 */
class BusinessInterest extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'business_interest';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['interest_id', 'business_id'], 'required'],
            [['interest_id', 'business_id'], 'integer'],
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
            'interest_id' => 'Flag ID',
            'business_id' => 'Business ID',
            'created' => 'Created',
        ];
    }

    public function getInterest()
    {
        return $this->hasOne(Interest::className(), ['id' => 'interest_id']);
    }
}
