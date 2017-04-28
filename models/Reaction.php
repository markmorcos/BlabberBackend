<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "reaction".
 *
 * @property integer $id
 * @property string $type
 * @property integer $user_id
 * @property integer $object_id
 * @property string $object_type
 * @property string $created
 * @property string $updated
 */
class Reaction extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'reaction';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'user_id', 'object_id', 'object_type'], 'required'],
            [['type', 'object_type'], 'string'],
            [['user_id', 'object_id'], 'integer'],
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
            'type' => 'Type',
            'user_id' => 'User ID',
            'object_id' => 'Object ID',
            'object_type' => 'Object Type',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }
}
