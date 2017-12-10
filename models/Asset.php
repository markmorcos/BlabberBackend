<?php

namespace app\models;

use yii\helpers\Url;

/**
 * This is the model class for table "asset".
 *
 * @property integer $id
 * @property string $asset
 * @property string $caption
 * @property string $created
 * @property string $updated
 */
class Asset extends LikeableActiveRecored
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'asset';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created', 'updated'], 'safe'],
            [['caption'], 'required'],
            [['caption'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'asset' => 'Asset',
            'caption' => 'Caption',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }
}
