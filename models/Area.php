<?php

namespace app\models;

/**
 * This is the model class for table "area".
 *
 * @property integer $id
 * @property string $name
 * @property string $nameAr
 * @property integer $city_id
 * @property string $lat
 * @property string $lng 
 * @property string $created
 * @property string $updated
 */
class Area extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'area';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['city_id', 'name', 'nameAr', 'lat', 'lng'], 'required'],
            [['city_id'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['name', 'nameAr'], 'string', 'max' => 255],
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
            'nameAr' => 'Name Ar',
            'city_id' => 'City',
            'lat' => 'Latitude',
            'lng' => 'Longitude',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }
}
