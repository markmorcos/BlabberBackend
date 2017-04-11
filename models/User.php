<?php

namespace app\models;

/**
 * This is the model class for table "user".
 *
 * @property string $id
 * @property string $name
 * @property string $password
 * @property string $role
 * @property string $email
 * @property string $username
 * @property string $mobile
 * @property string $gender
 * @property string $birthdate
 * @property string $auth_key
 * @property string $profile_photo
 * @property string $cover_photo
 * @property string $facebook_id
 * @property string $firebase_token
 * @property boolean $approved
 * @property boolean $blocked
 * @property boolean $private
 * @property string $created
 * @property string $updated
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    public $password_confirmation;

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
            [['name', 'email'], 'required'],
            [['password'], 'required', 'on' => 'create'],
            [['role', 'gender'], 'string'],
            [['birthdate', 'created', 'updated'], 'safe'],
            [['approved', 'blocked', 'private'], 'boolean'],
            [['name', 'password', 'email', 'username', 'profile_photo', 'cover_photo', 'facebook_id', 'firebase_token'], 'string', 'max' => 255],
            [['mobile'], 'string', 'max' => 20],
            [['email', 'mobile'], 'unique'],
            [['email'], 'email'],
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
            'username' => 'Username',
            'mobile' => 'Mobile',
            'gender' => 'Gender',
            'birthdate' => 'Birthdate',
            'auth_key' => 'Auth Key',
            'profile_photo' => 'Profile Photo',
            'cover_photo' => 'Cover Photo',
            'facebook_id' => 'Facebook ID',
            'firebase_token' => 'Firebase Token',
            'approved' => 'Approved',
            'blocked' => 'Blocked',
            'private' => 'Private',
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
        return $this->auth_key === $auth_key;
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
     * @param string $firebase_token
     * @return static|null
     */
    public static function login($email, $password, $firebase_token)
    {
        $user = static::findByEmail($email);

        if( isset($user) && $user->validatePassword($password) ){
            $user->auth_key = \Yii::$app->security->generateRandomString(16);
            if (!empty($firebase_token)) {
                // get list of users with the same token and reset the token for them
                $query = static::find()
                    ->where(['firebase_token' => $firebase_token])
                    ->andWhere(['!=', 'id', $user->id])
                    ->all();
                foreach ($query as $model) {
                    $model->firebase_token = '';
                    $model->save();
                }

                $user->firebase_token = $firebase_token;
            }
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
            return true;
        }
        return false;
    }

    public function getInterests()
    {
        return $this->hasMany(UserInterest::className(), ['user_id' => 'id']);
    }

    public function getInterestsList()
    {
        if( empty($this->id) ) return null;
        
        $user_interests = UserInterest::find()->where('user_id = '.$this->id)->all();
        $interests_list = '';
        $count = count($user_interests);
        for ($i=0; $i < $count; $i++) { 
            if (empty($user_interests[$i]->interest)) {
                continue;
            }
            
            $interests_list .= $user_interests[$i]->interest->name . ($i==$count-1?'':',');
        }
        
        return $interests_list;
    }
}
