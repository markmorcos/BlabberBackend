<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "business_flag".
 *
 * @property string $id
 * @property string $flag_id
 * @property string $business_id
 * @property string $created
 */
class BusinessFlag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'business_flag';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['flag_id', 'business_id'], 'required'],
            [['flag_id', 'business_id'], 'integer'],
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
            'flag_id' => 'Flag ID',
            'business_id' => 'Business ID',
            'created' => 'Created',
        ];
    }

    public function getFlag()
    {
        return $this->hasOne(Flag::className(), ['id' => 'flag_id']);
    }
}
