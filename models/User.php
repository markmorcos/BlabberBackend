<?php

namespace app\models;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $name
 * @property string $password
 * @property string $role
 * @property string $email
 * @property string $mobile
 * @property string $gender
 * @property string $birthdate
 * @property string $auth_key
 * @property string $profile_photo
 * @property string $cover_photo
 * @property string $facebook_id
 * @property integer $approved
 * @property integer $blocked
 * @property integer $private
 * @property string $lang
 * @property string $is_adult_and_smoker
 * @property string $created
 * @property string $updated
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    public $password_confirmation;
    public $device_IMEI;
    public $firebase_token;
    public $auth_key;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'email', 'password', 'gender', 'birthdate'], 'required'],
            [['password'], 'required', 'on' => 'create'],
            [['role', 'gender'], 'string'],
            [['birthdate', 'created', 'updated'], 'safe'],
            [['approved', 'blocked', 'private'], 'boolean'],
            [['name', 'password', 'email', 'profile_photo', 'cover_photo', 'facebook_id'], 'string', 'max' => 255],
            [['mobile'], 'string', 'max' => 20],
            [['email', 'mobile'], 'unique', 'message' => "{attribute} has already been taken."],
            [['email'], 'email'],
            [['lang'], 'string', 'max' => 2],
            ['password_confirmation', 'compare', 'compareAttribute'=>'password', 'skipOnEmpty' => false, 'on' => ['create', 'update'], 'message'=>"Passwords don't match"],
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
            'password' => 'Password',
            'password_confirmation' => 'Password Confirmation',
            'role' => 'Role',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'gender' => 'Gender',
            'birthdate' => 'Birthdate',
            'profile_photo' => 'Profile Photo',
            'cover_photo' => 'Cover Photo',
            'facebook_id' => 'Facebook ID',
            'approved' => 'Approved',
            'blocked' => 'Blocked',
            'private' => 'Private',
            'lang' => 'Lang',
            'is_adult_and_smoker' => 'Is Adult and Smoker',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['auth_key' => $token]);
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($auth_key)
    {
        foreach ($this->tokens as $token) {
            if ($token->auth_key === $auth_key) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return \Yii::$app->getSecurity()->validatePassword($password, $this->password);
    }

    /**
     * Login and set auth key
     *
     * @param string $email
     * @param string $password
     * @return static|null
     */
    public static function login($email, $password, $device_IMEI, $firebase_token, $is_facebook)
    {
        $user = static::findByEmail($email);
        if (!$user) return null;

        $user->device_IMEI = $device_IMEI;
        $user->firebase_token = $firebase_token;

        if(!$is_facebook && isset($user) && $user->validatePassword($password) || $is_facebook && isset($user)) {
            $user->auth_key = \Yii::$app->security->generateRandomString(16);
            if( $user->save() ){
                return $user;
            }
        }

        return null;
    }

    /**
     * hashing password before inserting new user
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (empty($this->password)) {
                unset($this->password);
            }else if ($this->password == $this->password_confirmation && in_array($this->scenario, ['create', 'update'])) {
                $this->password = \Yii::$app->security->generatePasswordHash($this->password);
            }

            if (!empty($this->device_IMEI)) {
                $conditions = [
                    'or',
                    ['device_IMEI' => $this->device_IMEI],
                ];
                if (!empty($this->firebase_token)) {
                    $conditions[] = ['firebase_token' => $this->firebase_token];
                }

                // get list of users with the same firebase token or device_IMEI and remove it
                UserToken::deleteAll($conditions);

                // add new token
                $token = new UserToken();
                $token->user_id = $this->id;
                $token->device_IMEI = $this->device_IMEI;
                $token->auth_key = $this->auth_key;
                $token->firebase_token = $this->firebase_token;

                if (!$token->save()) {
                    return false;
                }
            }

            return true;
        }
        return false;
    }

    public function getTokens()
    {
        return $this->hasMany(UserToken::className(), ['user_id' => 'id'])
            ->orderBy(['id' => SORT_DESC]);
    }

    public function getCategories()
    {
        return $this->hasMany(UserCategory::className(), ['user_id' => 'id']);
    }

    public function getCategoryList()
    {
        if( empty($this->id) ) return null;
        
        $user_categories = UserCategory::find()->where('user_id = '.$this->id)->all();
        $categories_list = '';
        $count = count($user_categories);
        for ($i=0; $i < $count; $i++) { 
            if (empty($user_categories[$i]->category)) {
                continue;
            }
            
            $categories_list .= $user_categories[$i]->category->name . ($i==$count-1?'':',');
        }
        
        return $categories_list;
    }

    public function getCategoryListAr()
    {
        if( empty($this->id) ) return null;
        
        $user_categories = UserCategory::find()->where('user_id = '.$this->id)->all();
        $categories_list = '';
        $count = count($user_categories);
        for ($i=0; $i < $count; $i++) { 
            if (empty($user_categories[$i]->category)) {
                continue;
            }
            
            $categories_list .= $user_categories[$i]->category->nameAr . ($i==$count-1?'':',');
        }
        
        return $categories_list;
    }
}
