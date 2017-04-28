<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sponsor".
 *
 * @property integer $id
 * @property string $name
 * @property string $nameAr
 * @property string $description
 * @property string $descriptionAr
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
            [['name', 'nameAr', 'main_image'], 'string', 'max' => 255],
            [['description', 'descriptionAr', 'link'], 'string', 'max' => 1023],
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
            'description' => 'Description',
            'descriptionAr' => 'Description Ar',
            'main_image' => 'Main Image',
            'link' => 'Link',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }
}
