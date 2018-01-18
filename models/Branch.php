<?php

namespace app\models;

use yii\helpers\Url;

/**
 * This is the model class for table "branch".
 *
 * @property integer $id
 * @property integer $business_id
 * @property string $name
 * @property string $nameAr
 * @property string $address
 * @property string $addressAr
 * @property integer $area_id
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
            [['business_id', 'area_id', 'address', 'addressAr', 'operation_hours', 'lat', 'lng'], 'required'],
            [['business_id', 'area_id'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['name', 'nameAr', 'phone', 'operation_hours', 'lat', 'lng'], 'string', 'max' => 255],
            [['address', 'addressAr'], 'string', 'max' => 1023],
            [['approved', 'is_reservable'], 'boolean'],
            [['operation_hours'], 'match', 'pattern' => '/^((from [01][0-9]:[0-5][0-9] [a|p][m] to [01][0-9]:[0-5][0-9] [a|p][m])|(from [01][0-9]:[0-5][0-9] [a|p][m] to [01][0-9]:[0-5][0-9] [a|p][m])(\s*,?\s*from [01][0-9]:[0-5][0-9] [a|p][m] to [01][0-9]:[0-5][0-9] [a|p][m])+)+$/'],
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
            'name' => 'Name',
            'nameAr' => 'Name Ar',
            'address' => 'Address',
            'addressAr' => 'Address Ar',
            'area_id' => 'Area',
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

    public function getArea()
    {
        return $this->hasOne(Area::className(), ['id' => 'area_id']);
    }

    public function getFlags()
    {
        return $this->hasMany(BranchFlag::className(), ['branch_id' => 'id']);
    }

    public function getFlagsList()
    {
        if( empty($this->id) ) return null;
        
        $branch_flags = BranchFlag::find()->where('branch_id = '.$this->id)->all();
        $flags_list = [];
        $count = count($branch_flags);
        for ($i=0; $i < $count; $i++) { 
            if (empty($branch_flags[$i]->flag)) {
                continue;
            }

            $branch_flags[$i]->flag->icon = Url::base(true).'/'.$branch_flags[$i]->flag->icon;
            $flags_list[] = $branch_flags[$i]->flag->attributes;
        }
        
        return $flags_list;
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
