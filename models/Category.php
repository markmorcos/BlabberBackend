<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "category".
 *
 * @property string $id
 * @property string $name
 * @property string $description
 * @property string $main_image
 * @property string $icon
 * @property string $badge
 * @property string $parent_id
 * @property string $created
 * @property string $updated
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'description'], 'required'],
            [['parent_id'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 1023],
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
            'icon' => 'Icon',
            'badge' => 'Badge',
            'parent_id' => 'Parent',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getParent()
    {
        return $this->hasOne(Category::className(), ['id' => 'parent_id']);
    }
}
