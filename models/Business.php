<?php

namespace app\models;

use yii\helpers\Url;

/**
 * This is the model class for table "business".
 *
 * @property integer $id
 * @property string $name
 * @property string $nameAr
 * @property string $address
 * @property string $addressAr
 * @property string $email
 * @property integer $country_id
 * @property integer $city_id
 * @property string $phone
 * @property string $operation_hours
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
 * @property string $approved
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
            [['name', 'nameAr', 'country_id', 'city_id', 'phone', 'operation_hours', 'price', 'description', 'descriptionAr', 'category_id', 'admin_id'], 'required'],
            [['country_id', 'city_id', 'category_id', 'admin_id'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['name', 'nameAr', 'email', 'phone', 'operation_hours', 'lat', 'lng', 'website', 'fb_page'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['address', 'addressAr', 'description', 'descriptionAr'], 'string', 'max' => 1023],
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
            'address' => 'Address',
            'addressAr' => 'Address Ar',
            'email' => 'Email',
            'country_id' => 'Country',
            'city_id' => 'City',
            'phone' => 'Phone',
            'operation_hours' => 'Operation Hours',
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
            'approved' => 'Approved',
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

    public function getBrochures()
    {
        return $this->hasMany(Media::className(), ['object_id' => 'id'])
            ->where(['object_type' => 'Business', 'type' => 'brochure']);
    }

    public function getCigarettes()
    {
        return $this->hasMany(Media::className(), ['object_id' => 'id'])
            ->where(['object_type' => 'Business', 'type' => 'cigarette']);
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

    public function getIsOpen()
    {
        //TODO: add support to different days & timezones
        $isOpen = False;
        $operation_hours = $this->operation_hours;
        $operation_hours_array = explode(',', $operation_hours);

        foreach ($operation_hours_array as $operation_hour) {
            if (empty($operation_hour)){
                continue;
            }

            if (preg_match_all('/(?:[01][0-9]|2[0-4]):[0-5][0-9] ([AaPp][Mm])/', $operation_hour, $matches) ||
                preg_match_all('/(?:[01][0-9]|2[0-4]):[0-5][0-9]/', $operation_hour, $matches)) {
                if (!empty($matches[0]) && count($matches[0]) == 2) {
                    $from = new \DateTime($matches[0][0]);
                    $to = new \DateTime($matches[0][1]);
                    $now = new \DateTime(date('H:i'));

                    if (strcasecmp(substr($matches[0][1], -2), 'am') === 0) {
                        $to->modify('+1 day');
                    }

                    return ($now >= $from && $now <= $to);
                }
            }

        }

        return $isOpen;
    }
}
