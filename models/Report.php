<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "report".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $object_id
 * @property string $object_type
 * @property string $created
 * @property string $updated
 */
class Report extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'report';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'object_id', 'object_type'], 'required'],
            [['user_id', 'object_id'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['object_type'], 'string', 'max' => 20],
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
            'object_id' => 'Object ID',
            'object_type' => 'Object Type',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }
}
