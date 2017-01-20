<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "city".
 *
 * @property string $id
 * @property string $name
 * @property string $country_id
 * @property string $created
 * @property string $updated
 */
class City extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'city';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['country_id'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'country_id' => 'Country',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }
}
