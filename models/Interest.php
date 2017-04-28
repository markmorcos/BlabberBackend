<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "interest".
 *
 * @property integer $id
 * @property string $name
 * @property string $nameAr
 * @property string $created
 * @property string $updated
 */
class Interest extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'interest';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
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
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }
}
