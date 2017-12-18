<?php

namespace app\models;

use yii\helpers\Url;

/**
 * This is the model class for table "business_v2".
 *
 * @property integer $id
 * @property string $name
 * @property string $nameAr
 * @property string $phone
 * @property string $main_image
 * @property string $rating
 * @property string $price
 * @property string $website
 * @property string $fb_page
 * @property string $description
 * @property string $descriptionAr
 * @property string $featured
 * @property string $verified
 * @property string $show_in_home
 * @property integer $category_id
 * @property integer $admin_id
 * @property string $approved
 * @property string $created
 * @property string $updated
 */
class BusinessV2 extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'business_v2';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'nameAr', 'price', 'category_id', 'admin_id'], 'required'],
            [['category_id', 'admin_id'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['name', 'nameAr', 'phone', 'website', 'fb_page'], 'string', 'max' => 255],
            [['description', 'descriptionAr'], 'string', 'max' => 1023],
            [['rating', 'price', 'featured', 'verified', 'show_in_home'], 'string', 'max' => 1],
            [['approved'], 'boolean'],
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
            'phone' => 'Phone',
            'main_image' => 'Main Image',
            'rating' => 'Rating',
            'price' => 'Price',
            'website' => 'Website',
            'fb_page' => 'Facebook Page',
            'description' => 'Description',
            'descriptionAr' => 'Description Ar',
            'featured' => 'Featured',
            'verified' => 'Verified',
            'show_in_home' => 'Show In Home',
            'category_id' => 'Category',
            'admin_id' => 'Admin',
            'approved' => 'Approved',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    public function getAdmin()
    {
        return $this->hasOne(User::className(), ['id' => 'admin_id']);
    }

    // public function getInterests()
    // {
    //     return $this->hasMany(BusinessV2Interest::className(), ['business_v2_id' => 'id']);
    // }

    // public function getInterestsList()
    // {
    //     if( empty($this->id) ) return null;
        
    //     $business_v2_interests = BusinessV2Interest::find()->where('business_v2_id = '.$this->id)->all();
    //     $interests_list = '';
    //     $count = count($business_v2_interests);
    //     for ($i=0; $i < $count; $i++) { 
    //         if (empty($business_v2_interests[$i]->interest)) {
    //             continue;
    //         }

    //         $interests_list .= $business_v2_interests[$i]->interest->name . ($i==$count-1?'':',');
    //     }
        
    //     return $interests_list;
    // }

    // public function getMenus()
    // {
    //     return $this->hasMany(Media::className(), ['object_id' => 'id'])
    //         ->where(['object_type' => 'BusinessV2', 'type' => 'menu']);
    // }

    // public function getProducts()
    // {
    //     return $this->hasMany(Media::className(), ['object_id' => 'id'])
    //         ->where(['object_type' => 'BusinessV2', 'type' => 'product']);
    // }

    // public function getBrochures()
    // {
    //     return $this->hasMany(Media::className(), ['object_id' => 'id'])
    //         ->where(['object_type' => 'BusinessV2', 'type' => 'brochure']);
    // }

    // public function getCigarettes()
    // {
    //     return $this->hasMany(Media::className(), ['object_id' => 'id'])
    //         ->where(['object_type' => 'BusinessV2', 'type' => 'cigarette']);
    // }

    // public function getViews()
    // {
    //     return $this->hasMany(BusinessV2View::className(), ['business_v2_id' => 'id'])
    //         ->orderBy(['id' => SORT_DESC]);
    // }
}