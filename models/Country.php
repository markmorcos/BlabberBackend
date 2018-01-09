<?php

namespace app\models;

/**
 * This is the model class for table "country".
 *
 * @property integer $id
 * @property string $name
 * @property string $nameAr
 * @property string $flag
 * @property string $created
 * @property string $updated
 */
class Country extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'country';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created', 'updated'], 'safe'],
            [['name', 'nameAr'], 'required'],
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
            'flag' => 'Flag',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }
}
