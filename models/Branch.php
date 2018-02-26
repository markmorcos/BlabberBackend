<?php

namespace app\models;

use yii\helpers\Url;

/**
 * This is the model class for table "branch".
 *
 * @property integer $id
 * @property integer $business_id
 * @property integer $country_id
 * @property integer $city_id
 * @property integer $area_id
 * @property string $address
 * @property string $addressAr
 * @property string $phone
 * @property string $operation_hours
 * @property string $lat
 * @property string $lng
 * @property string $approved
 * @property string $is_reservable
 * @property string $created
 * @property string $updated
 */
class Branch extends \yii\db\ActiveRecord
{
    public $distance;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'branch';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['business_id', 'country_id', 'city_id', 'area_id', 'address', 'addressAr', 'operation_hours', 'lat', 'lng'], 'required'],
            [['business_id', 'country_id', 'city_id', 'area_id'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['phone', 'operation_hours', 'lat', 'lng'], 'string', 'max' => 255],
            [['address', 'addressAr'], 'string', 'max' => 1023],
            [['approved', 'is_reservable'], 'boolean'],
            [['operation_hours'], 'match', 'pattern' => '/^(((Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)(-(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday))?: )?(from [01][0-9]:[0-5][0-9] [a|p][m] to [01][0-9]:[0-5][0-9] [a|p][m])|((Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)(-(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday))?: )?(from [01][0-9]:[0-5][0-9] [a|p][m] to [01][0-9]:[0-5][0-9] [a|p][m])(, ((Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)(-(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday))?: )?from [01][0-9]:[0-5][0-9] [a|p][m] to [01][0-9]:[0-5][0-9] [a|p][m])+)+$/'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'business_id' => 'Business',
            'country_id' => 'Country',
            'city_id' => 'City',
            'area_id' => 'Area',
            'address' => 'Address',
            'addressAr' => 'Address Ar',
            'phone' => 'Phone',
            'operation_hours' => 'Operation Hours',
            'lat' => 'Lat',
            'lng' => 'Lng',
            'approved' => 'Approved',
            'is_reservable' => 'Reservable',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getBusiness()
    {
        return $this->hasOne(Business::className(), ['id' => 'business_id']);
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }

    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    public function getArea()
    {
        return $this->hasOne(Area::className(), ['id' => 'area_id']);
    }

    public function getFlags()
    {
        return $this->hasMany(BranchFlag::className(), ['branch_id' => 'id']);
    }

    public function getFlagList()
    {
        if( empty($this->id) ) return null;

        $branch_flags = BranchFlag::find()->where('branch_id = '.$this->id)->all();
        $count = count($branch_flags);
        $flag_list = [];
        for ($i=0; $i < $count; $i++) {

            $flag_list[] = $branch_flags[$i]->flag->name;
        }

        return $flag_list;
    }

    public function getFlagListAr()
    {
        if( empty($this->id) ) return null;

        $branch_flags = BranchFlag::find()->where('branch_id = '.$this->id)->all();
        $count = count($branch_flags);
        $flag_list = [];
        for ($i=0; $i < $count; $i++) {
            if (empty($branch_flags[$i]->flag)) {
                continue;
            }

            $flag_list[] = $branch_flags[$i]->flag->nameAr;
        }

        return $flag_list;
    }

    public function getImages()
    {
        return $this->hasMany(Media::className(), ['object_id' => 'id'])
            ->where(['object_type' => 'Branch', 'type' => 'image']);
    }

    public function getCheckins()
    {
        return $this->hasMany(Checkin::className(), ['branch_id' => 'id'])
            ->orderBy(['id' => SORT_DESC])
            ->with('user');
    }

    public function getReviews()
    {
        return $this->hasMany(Review::className(), ['branch_id' => 'id'])
            ->orderBy(['id' => SORT_DESC]);
    }

    public function getIsOpen()
    {
        date_default_timezone_set($this->country_id === 424 ? 'Asia/Dubai' : 'Africa/Cairo');

        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $isOpen = False;
        $operation_hours = $this->operation_hours;
        $operation_hours_array = explode(',', $operation_hours);

        foreach ($operation_hours_array as $operation_hour) {
            if (empty($operation_hour)) {
                continue;
            }

            $operation_hour = trim($operation_hour);
            $days = strlen($operation_hour) > 25 ? explode('-', substr($operation_hour, 0, stripos($operation_hour, ':'))) : '';
            $from = substr($operation_hour, -20, -12);
            $to = substr($operation_hour, -8);
            if (empty($days)) {
                $fromTime = new \DateTime($from);
                $toTime = new \DateTime($to);
                $now = new \DateTime(date('h:i a'));
                if ($toTime->format('U') <= $fromTime->format('U')) {
                    $toTime->modify('+1 day');
                }

                $isOpen = $isOpen || ($now->format('U') >= $fromTime->format('U') && $now->format('U') <= $toTime->format('U'));
            } else if (count($days) === 1 && date('l') === $days[0]) {
                $fromTime = new \DateTime($from);
                $toTime = new \DateTime($to);
                $now = new \DateTime(date('h:i a'));
                if ($toTime->format('U') <= $fromTime->format('U')) {
                    $toTime->modify('+1 day');
                }

                $isOpen = $isOpen || ($now->format('U') >= $fromTime->format('U') && $now->format('U') <= $toTime->format('U'));
            } else if (count($days) === 2) {
                $startIndex = array_search($days[0], $daysOfWeek);
                $endIndex = array_search($days[1], $daysOfWeek);
                if ($endIndex < $startIndex) $endIndex += 7;
                for ($i = $startIndex; $i <= $endIndex; ++$i) {
                    if (date('l') === $daysOfWeek[$i % 7]) {
                        $fromTime = new \DateTime($from);
                        $toTime = new \DateTime($to);
                        $now = new \DateTime(date('h:i a'));
                        if ($toTime->format('U') <= $fromTime->format('U')) {
                            $toTime->modify('+1 day');
                        }

                        $isOpen = $isOpen || ($now->format('U') >= $fromTime->format('U') && $now->format('U') <= $toTime->format('U'));
                    }
                }
            }
        }

        return $isOpen;
    }
}
