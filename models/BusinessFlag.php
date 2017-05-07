<?php

namespace app\models;

/**
 * This is the model class for table "business_flag".
 *
 * @property integer $id
 * @property integer $business_id
 * @property integer $flag_id
 * @property string $created
 * @property string $updated
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
            [['business_id', 'flag_id'], 'required'],
            [['business_id', 'flag_id'], 'integer'],
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
            'flag_id' => 'Flag ID',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getFlag()
    {
        return $this->hasOne(Flag::className(), ['id' => 'flag_id']);
    }
}
