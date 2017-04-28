<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "business_interest".
 *
 * @property integer $id
 * @property integer $business_id
 * @property integer $interest_id
 * @property string $created
 * @property string $updated
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
            [['business_id', 'interest_id'], 'required'],
            [['business_id', 'interest_id'], 'integer'],
            [['created', 'updated'], 'safe'],
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
            'interest_id' => 'Interest ID',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getInterest()
    {
        return $this->hasOne(Interest::className(), ['id' => 'interest_id']);
    }
}
