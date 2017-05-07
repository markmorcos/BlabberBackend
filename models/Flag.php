<?php

namespace app\models;

/**
 * This is the model class for table "flag".
 *
 * @property integer $id
 * @property string $name
 * @property string $nameAr
 * @property string $icon
 * @property string $created
 * @property string $updated
 */
class Flag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'flag';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
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
            'icon' => 'Icon',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }
}
