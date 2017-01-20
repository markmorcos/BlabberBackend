<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sponsor".
 *
 * @property string $id
 * @property string $name
 * @property string $description
 * @property string $main_image
 * @property string $link
 * @property string $created
 * @property string $updated
 */
class Sponsor extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sponsor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'description', 'link'], 'required'],
            [['created', 'updated'], 'safe'],
            [['name', 'main_image'], 'string', 'max' => 255],
            [['description', 'link'], 'string', 'max' => 1023],
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
            'description' => 'Description',
            'main_image' => 'Main Image',
            'link' => 'Link',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }
}
