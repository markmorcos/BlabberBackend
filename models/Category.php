<?php

namespace app\models;

/**
 * This is the model class for table "category".
 *
 * @property integer $id
 * @property string $identifier
 * @property string $name
 * @property string $nameAr
 * @property string $description
 * @property string $descriptionAr
 * @property string $main_image
 * @property string $icon
 * @property string $badge
 * @property integer $parent_id
 * @property integer $order
 * @property string $color
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
            [['identifier', 'name', 'nameAr'], 'required'],
            [['identifier'], 'unique'],
            [['parent_id', 'order'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['color'], 'string', 'max' => 9],
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
            'identifier' => 'Identifier',
            'name' => 'Name',
            'nameAr' => 'Name Ar',
            'description' => 'Description',
            'descriptionAr' => 'Description Ar',
            'main_image' => 'Main Image',
            'icon' => 'Icon',
            'badge' => 'Badge',
            'parent_id' => 'Parent',
            'order' => 'Order',
            'color' => 'Color',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getParent()
    {
        return $this->hasOne(Category::className(), ['id' => 'parent_id']);
    }

    public function getTopParent()
    {
        $parent = $this->parent;
        while (true) {
            if ($parent === null || $parent->parent === null) {
                break;
            }
            $parent = $parent->parent;
        }

        return $parent;
    }
}
