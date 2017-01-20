<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "flag".
 *
 * @property string $id
 * @property string $name
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
            [['name'], 'required'],
            [['created', 'updated'], 'safe'],
            [['name'], 'string', 'max' => 1023],
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
            'icon' => 'Icon',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }
}
