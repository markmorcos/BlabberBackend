<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "interest".
 *
 * @property string $id
 * @property string $name
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
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }
}
