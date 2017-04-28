<?php

namespace app\models;

use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "business".
 *
 * @property integer $id
 * @property string $name
 * @property string $nameAr
 * @property string $address
 * @property string $addressAr
 * @property integer $country_id
 * @property integer $city_id
 * @property string $phone
 * @property string $open_from
 * @property string $open_to
 * @property string $lat
 * @property string $lng
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
 * @property string $created
 * @property string $updated
 */
class Business extends \yii\db\ActiveRecord
{
    public $distance;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'business';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'address', 'lat', 'lng', 'price', 'category_id', 'admin_id'], 'required'],
            [['country_id', 'city_id', 'category_id', 'admin_id'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['name', 'nameAr', 'lat', 'lng', 'website', 'fb_page'], 'string', 'max' => 255],
            [['address', 'addressAr', 'description', 'descriptionAr'], 'string', 'max' => 1023],
            [['phone'], 'string', 'max' => 20],
            [['open_from', 'open_to'], 'string', 'max' => 10],
            [['rating', 'price', 'featured', 'verified', 'show_in_home'], 'string', 'max' => 1],
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
            'address' => 'Address',
            'addressAr' => 'Address Ar',
            'country_id' => 'Country',
            'city_id' => 'City',
            'phone' => 'Phone',
            'open_from' => 'Open From',
            'open_to' => 'Open To',
            'lat' => 'Lat',
            'lng' => 'Lng',
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
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }

    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    public function getAdmin()
    {
        return $this->hasOne(User::className(), ['id' => 'admin_id']);
    }

    public function getFlags()
    {
        return $this->hasMany(BusinessFlag::className(), ['business_id' => 'id']);
    }

    public function getFlagsList()
    {
        if( empty($this->id) ) return null;
        
        $business_flags = BusinessFlag::find()->where('business_id = '.$this->id)->all();
        $flags_list = [];
        $count = count($business_flags);
        for ($i=0; $i < $count; $i++) { 
            if (empty($business_flags[$i]->flag)) {
                continue;
            }

            $business_flags[$i]->flag->icon = Url::base(true).'/'.$business_flags[$i]->flag->icon;
            $flags_list[] = $business_flags[$i]->flag->attributes;
        }
        
        return $flags_list;
    }

    public function getInterests()
    {
        return $this->hasMany(BusinessInterest::className(), ['business_id' => 'id']);
    }

    public function getInterestsList()
    {
        if( empty($this->id) ) return null;
        
        $business_interests = BusinessInterest::find()->where('business_id = '.$this->id)->all();
        $interests_list = '';
        $count = count($business_interests);
        for ($i=0; $i < $count; $i++) { 
            if (empty($business_interests[$i]->interest)) {
                continue;
            }

            $interests_list .= $business_interests[$i]->interest->name . ($i==$count-1?'':',');
        }
        
        return $interests_list;
    }

    public function getImages()
    {
        return $this->hasMany(Media::className(), ['object_id' => 'id'])
            ->where(['object_type' => 'Business', 'type' => 'image']);
    }

    public function getVideos()
    {
        return $this->hasMany(Media::className(), ['object_id' => 'id'])
            ->where(['object_type' => 'Business', 'type' => 'video']);
    }

    public function getMenus()
    {
        return $this->hasMany(Media::className(), ['object_id' => 'id'])
            ->where(['object_type' => 'Business', 'type' => 'menu']);
    }

    public function getProducts()
    {
        return $this->hasMany(Media::className(), ['object_id' => 'id'])
            ->where(['object_type' => 'Business', 'type' => 'product']);
    }

    public function getViews()
    {
        return $this->hasMany(BusinessView::className(), ['business_id' => 'id'])
            ->orderBy(['id' => SORT_DESC]);
    }

    public function getCheckins()
    {
        return $this->hasMany(Checkin::className(), ['business_id' => 'id'])
            ->orderBy(['id' => SORT_DESC])
            ->with('user');
    }

    public function getReviews()
    {
        return $this->hasMany(Review::className(), ['business_id' => 'id'])
            ->orderBy(['id' => SORT_DESC]);
    }
}
