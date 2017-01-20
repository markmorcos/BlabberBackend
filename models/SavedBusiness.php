<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "saved-business".
 *
 * @property string $id
 * @property string $user_id
 * @property string $business_id
 * @property string $created
 * @property string $updated
 */
class SavedBusiness extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'saved_business';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'business_id'], 'required'],
            [['user_id', 'business_id'], 'integer'],
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
            'user_id' => 'User ID',
            'business_id' => 'Business ID',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }
}
