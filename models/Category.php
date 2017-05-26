<?php

namespace app\models;

/**
 * This is the model class for table "category".
 *
 * @property integer $id
 * @property string $name
 * @property string $nameAr
 * @property string $description
 * @property string $descriptionAr
 * @property string $main_image
 * @property string $icon
 * @property string $badge
 * @property integer $parent_id
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
            [['parent_id'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['name', 'nameAr'], 'string', 'max' => 255],
            [['description', 'descriptionAr'], 'string', 'max' => 1023],
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
