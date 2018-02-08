<?php

namespace app\controllers;

use app\components\Translation;
use app\models\Area;
use app\models\Blog;
use app\models\Business;
use app\models\BusinessInterest;
use app\models\BusinessView;
use app\models\Category;
use app\models\Checkin;
use app\models\City;
use app\models\Comment;
use app\models\Country;
use app\models\Flag;
use app\models\Follow;
use app\models\Interest;
use app\models\Media;
use app\models\Notification;
use app\models\Poll;
use app\models\Option;
use app\models\Vote;
use app\models\Reaction;
use app\models\Report;
use app\models\Reservation;
use app\models\Review;
use app\models\SavedBusiness;
use app\models\Sponsor;
use app\models\User;
use app\models\UserCategory;
use app\models\UserToken;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\HttpException;

use app\models\Branch;
use app\models\BranchFlag;
use app\models\BusinessV2;

class ApiController extends ApiBaseController
{
    public function actionMigrate()
    {
        // set_time_limit(0);
        $businesses = Business::find()->all();
        foreach ($businesses as $business) {
            // $name = array_values(array_filter(preg_split('/[–#-] +/', $business->name)));
            // $business_name = $name[0] . (count($name) >= 3 ? ' #' . $name[2] : '');
            // $branch_name = count($name) >= 2 ? $name[1] : '';
            // $name_ar = array_values(array_filter(preg_split('/[–#-]+/', $business->nameAr)));
            $name = $business->name;
            $dash_index = strripos($business->name, '-');
            $hash_index = strripos($business->name, '#');
            $branch_number = substr($name, $hash_index ? $hash_index : strlen($name));
            $business_name = trim(substr($name, 0, $dash_index ? $dash_index : ($hash_index ? $hash_index : strlen($name)))) . ($branch_number ? ' ' . $branch_number : '');
            $branch_name = $dash_index ? trim(substr($name, $dash_index + 1, $branch_number ? -strlen($branch_number) : strlen($name))) : '';
            print_r($business_name . '___' . $branch_name); echo '<br>';

            $name_ar = $business->nameAr;
            $dash_index_ar = strripos($business->nameAr, '-');
            $hash_index_ar = strripos($business->nameAr, '#');
            $branch_number_ar = substr($name_ar, $hash_index_ar ? $hash_index_ar : strlen($name_ar));
            $business_name_ar = trim(substr($name_ar, 0, $dash_index_ar ? $dash_index_ar : ($hash_index_ar ? $hash_index_ar : strlen($name_ar)))) . ($branch_number_ar ? ' ' . $branch_number_ar : '');
            $branch_name_ar = $dash_index_ar ? trim(substr($name_ar, $dash_index_ar + 1, $branch_number_ar ? -strlen($branch_number_ar) : strlen($name_ar))) : '';
            print_r($business_name_ar . '___' . $branch_name_ar); echo '<br>';
            $business_v2 = BusinessV2::findOne(['name' => $business_name]);
            if (!$business_v2) {
                $business_v2 = new BusinessV2;
                $business_v2->name = $business_name;
                $business_v2->nameAr = $business_name_ar;
                $business_v2->phone = $business->phone;
                $business_v2->main_image = $business->main_image;
                $business_v2->rating = $business->rating;
                $business_v2->price = $business->price;
                $business_v2->website = $business->website;
                $business_v2->fb_page = $business->fb_page;
                $business_v2->description = $business->description;
                $business_v2->descriptionAr = $business->descriptionAr;
                $business_v2->featured = $business->featured;
                $business_v2->verified = $business->verified;
                $business_v2->show_in_home = $business->show_in_home;
                $business_v2->category_id = $business->category_id;
                $business_v2->admin_id = $business->admin_id;
                $business_v2->approved = $business->approved;
                $business_v2->created = $business->created;
                $business_v2->updated = $business->updated;
                if (!$business_v2->save()) {
                    echo '<pre>';
                    print_r($business_v2);
                    die();
                }
            }
            $branch = Branch::findOne($business->id);
            if (!$branch) {
                $branch = new Branch;
                $branch->id = $business->id;
                $branch->business_id = $business_v2->id;
                if ($branch_name) {
                    $area = Area::findOne(['name' => $branch_name]);
                    if (!$area) {
                        $area = new Area;
                        $area->name = $branch_name;
                        $area->nameAr = $branch_name_ar;
                        $area->city_id = $business->city_id;
                        $area->save();
                    }
                    $branch->area_id = $area->id;
                }
                $branch->city_id = $business->city_id;
                $branch->country_id = $business->country_id;
                $branch->address = $business->address;
                $branch->addressAr = $business->addressAr;
                $branch->phone = $business->phone;
                $branch->operation_hours = $business->operation_hours;
                $branch->lat = $business->lat;
                $branch->lng = $business->lng;
                $branch->admin_id = $business->admin_id;
                $branch->approved = $business->approved;
                $branch->created = $business->created;
                $branch->updated = $business->updated;
                if (!$branch->save()) {
                    echo '<pre>';
                    print_r($branch);
                    die();
                }
            }
            $media = Media::find()->where(['object_id' => $business->id, 'object_type' => 'Business', 'version' => 0])->all();
            foreach($media as $medium) {
                if (in_array($medium->type, ['menu', 'product', 'brochure', 'business_image'])) {
                    $medium->object_id = $business_v2->id;
                    $medium->version = 1;
                    $medium->save();
                } else if (in_array($medium->type, ['image'])) {
                    $medium->object_type = 'Branch';
                    $medium->version = 1;
                    $medium->save();
                }
            }
            $saved_businesses = SavedBusiness::find()->where(['business_id' => $business->id, 'version' => 0])->all();
            foreach($saved_businesses as $saved_business) {
                $saved_business->business_id = $business_v2->id;
                $saved_business->version = 1;
                $saved_business->save();
            }
            $business_interests = BusinessInterest::find()->where(['business_id' => $business->id, 'version' => 0])->all();
            foreach($business_interests as $business_interest) {
                $business_interest->business_id = $business_v2->id;
                $business_interest->version = 1;
                $business_interest->save();
            }
            $business_views = BusinessView::find()->where(['business_id' => $business->id, 'version' => 0])->all();
            foreach($business_views as $business_view) {
                $business_view->business_id = $business_v2->id;
                $business_view->version = 1;
                $business_view->save();
            }
        }
    }

    /***************************************/
    /**************** Users ****************/
    /***************************************/

    /**
     * @api {post} /api/sign-up Sign up new user
     * @apiName SignUp
     * @apiGroup User
     *
     * @apiParam {String} name User's full name.
     * @apiParam {String} email User's unique email.
     * @apiParam {String} password User's password.
     * @apiParam {String} device_IMEI User's device IMEI.
     * @apiParam {String} firebase_token User's firebase token (optional).
     * @apiParam {String} type User's type (user or business) (optional).
     * @apiParam {String} gender User's gender.
     * @apiParam {String} mobile User's unique mobile number (optional).
     * @apiParam {String} image User's new image url (optional).
     * @apiParam {File} Media[file] User's new image file (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} user_data user details.
     * @apiSuccess {String} auth_key user auth key to use for other api calls.
     */
    public function actionSignUp(
        $name,
        $email,
        $password,
        $device_IMEI,
        $firebase_token = null,
        $type = 'user',
        $gender = null,
        $mobile = null,
        $image = null
    ) {
        $this->_addOutputs(['user_data', 'auth_key']);

        if (!in_array($type, ['user', 'business'])) {
            throw new HttpException(200, 'invalid user type');
        }

        if (!empty($gender) && !in_array($gender, ['male', 'female'])) {
            throw new HttpException(200, 'invalid user type');
        }

        // sign up
        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->password = Yii::$app->security->generatePasswordHash($password);
        $user->role = $type;
        if (!empty($gender)) {
            $user->gender = $gender;
        }
        if (!empty($mobile)) {
            $user->mobile = $mobile;
        }
        if ($type === 'user') {
            $user->approved = 1; //true
        }

        if (!$user->save()) {
            throw new HttpException(200, $this->_getErrors($user));
        }

        // save url if image coming from external source like Facebook
        if (!empty($image)) {
            $user->profile_photo = $image;
            if (!$user->save()) {
                throw new HttpException(200, $this->_getErrors($user));
            }

            // upload image then save it
        } else if (!empty($_FILES['Media'])) {
            $this->_uploadFile($user->id, 'User', 'profile_photo', $user, 'profile_photo', $user->id);
        }

        if ($type === 'business') {
            $link = Url::to(['user/view', 'id' => $user->id], true);

            Yii::$app->mailer->compose()
                ->setFrom(['support@myblabber.com' => 'MyBlabber Support'])
                ->setTo($this->adminEmail)
                ->setSubject('New Buisness Account (' . $user->name . ')')
                ->setTextBody('New business account created through the mobile application, check it from here: ' . $link)
                ->setHtmlBody('New business account created through the mobile application, check it from here: <a href="' . $link . '">link</a>')
                ->send();
        }

        $this->_login($email, $password, $device_IMEI, $firebase_token, false);
    }

    /**
     * @api {post} /api/sign-in-fb Sign in using facebook
     * @apiName SignInFb
     * @apiGroup User
     *
     * @apiParam {String} facebook_token User's facebook token.
     * @apiParam {String} device_IMEI User's device IMEI.
     * @apiParam {String} firebase_token User's firebase token (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} user_data user details.
     * @apiSuccess {String} auth_key user auth key to use for other api calls.
     */
    public function actionSignInFb($facebook_token, $device_IMEI, $firebase_token)
    {
        $this->_addOutputs(['user_data', 'auth_key', 'is_new_user']);
        $this->output['is_new_user'] = false;

        // verify facebook token & facebook id
        $user_details = 'https://graph.facebook.com/me?fields=id,name,email,picture{url}&access_token=' . $facebook_token;
        $response = @file_get_contents($user_details);
        $response = @json_decode($response);
        if (!isset($response) || !isset($response->id)) {
            throw new HttpException(200, 'invalid facebook token');
        }

        $user = User::findByEmail($response->email);
        if ($user === null) {
            // sign up
            $this->output['is_new_user'] = true;
            $user = new User;
        }
        $user->email = $response->email;
        $user->facebook_id = $response->id;
        $user->approved = 1; //true
        $user->name = $response->name;

        if (empty($user->password)) {
            $user->password = uniqid();
        }
        if (empty($user->gender)) {
            $user->gender = 'male';
        }

        if (!empty($response->picture->data->url)) {
            $user->profile_photo = $response->picture->data->url;
        }

        if (!$user->save()) {
            throw new HttpException(200, $this->_getErrors($user));
        }

        $this->_login($user->email, '', $device_IMEI, $firebase_token, true);
    }

    /**
     * @api {post} /api/sign-in Sign in existing user
     * @apiName SignIn
     * @apiGroup User
     *
     * @apiParam {String} email User's unique email.
     * @apiParam {String} password User's password.
     * @apiParam {String} device_IMEI user device_IMEI.
     * @apiParam {String} firebase_token User's firebase token (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} user_data user details.
     * @apiSuccess {String} auth_key user auth key to use for other api calls.
     */
    public function actionSignIn($email, $password, $device_IMEI, $firebase_token = null)
    {
        $this->_addOutputs(['user_data', 'auth_key']);
        $this->_login($email, $password, $device_IMEI, $firebase_token, false);
    }

    /**
     * @api {post} /api/recover-password Recover user's password
     * @apiName RecoverPassword
     * @apiGroup User
     *
     * @apiParam {String} email User's unique email.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionRecoverPassword($email)
    {
        $new_password = substr(md5(uniqid(rand(), true)), 6, 6);

        $user = User::findByEmail($email);
        if ($user === null) {
            throw new HttpException(200, 'no user with this email');
        }

        $user->password = Yii::$app->security->generatePasswordHash($new_password);
        if (!$user->save()) {
            throw new HttpException(200, $this->_getErrors($user));
        }

        $result = Yii::$app->mailer->compose()
            ->setFrom(['support@myblabber.com' => 'MyBlabber Support'])
            ->setTo($email)
            ->setSubject('MyBlabber Password Recovery')
            ->setTextBody('your password changed to: ' . $new_password)
            ->send();
        if ($result === null) {
            throw new HttpException(200, 'Password changed but errors while sending email');
        }
    }

    /**
     * @api {post} /api/change-password Change user's password
     * @apiName ChangePassword
     * @apiGroup User
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} new_password User's new password.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionChangePassword($new_password)
    {
        $model = User::findOne($this->logged_user['id']);
        $model->password = Yii::$app->security->generatePasswordHash($new_password);
        if (!$model->save()) {
            throw new HttpException(200, $this->_getErrors($model));
        }
    }

    /**
     * @api {post} /api/change-profile-photo Change user's photo
     * @apiName ChangeProfilePhoto
     * @apiGroup User
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} image User's new image url (optional).
     * @apiParam {File} Media[file] User's new image file (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {String} new_photo the url of the new added image.
     */
    public function actionChangeProfilePhoto($image = null)
    {
        $user = User::findOne($this->logged_user['id']);

        // save url if image coming from external source like Facebook
        if (!empty($image)) {
            $user->profile_photo = $image;
            if (!$user->save()) {
                throw new HttpException(200, $this->_getErrors($user));
            }

            // upload image then save it
        } else if (!empty($_FILES['Media'])) {
            $this->_uploadFile($user->id, 'User', 'profile_photo', $user, 'profile_photo');
        } else {
            throw new HttpException(200, 'no url or file input');
        }

        $this->output['new_photo'] = $this->_getUserPhotoUrl($user->profile_photo);
    }

    /**
     * @api {post} /api/logout Logout for logged in user
     * @apiName Logout
     * @apiGroup User
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} device_IMEI User's device IMEI.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionLogout($device_IMEI)
    {
        if (UserToken::deleteAll(['user_id' => $this->logged_user['id'], 'device_IMEI' => $device_IMEI]) === 0) {
            throw new HttpException(200, 'logout problem');
        }
    }

    /**
     * @api {post} /api/get-profile Get user profile
     * @apiName GetProfile
     * @apiGroup User
     *
     * @apiParam {String} user_id User's id of User profile you want to get (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} user_data user details.
     */
    public function actionGetProfile($user_id = null)
    {
        $this->_addOutputs(['user_data']);

        if (!empty($user_id)) {
            $user = User::findOne($user_id);
        }
        if ($user === null) {
            throw new HttpException(200, 'no user with this id');
        }

        $this->output['user_data'] = $this->_getUserData($user);
    }

    /**
     * @api {post} /api/edit-profile Edit user profile
     * @apiName EditProfile
     * @apiGroup User
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} name user name (optional).
     * @apiParam {String} mobile user mobile (optional).
     * @apiParam {String} gender user gender (optional).
     * @apiParam {String} birthdate user birthdate (optional).
     * @apiParam {String} device_IMEI user device_IMEI (optional).
     * @apiParam {String} firebase_token user firebase_token (optional).
     * @apiParam {Boolean} private user private (0: false, 1: true) (optional).
     * @apiParam {Array} category_ids array of category ids to add to user, ex. 2,5,7 (optional).
     * @apiParam {String} is_adult_and_smoker whether the user is allowed to see cigarettes tab (1, 0, null (string)).
     * @apiParam {String} lang User language (En, Ar).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionEditProfile(
        $name = null,
        $mobile = null,
        $gender = null,
        $birthdate = null,
        $device_IMEI = null,
        $firebase_token = null,
        $private = null,
        $category_ids = null,
        $is_adult_and_smoker = null,
        $lang = null
    ) {
        $user = User::findOne($this->logged_user['id']);
        if ($user === null) {
            throw new HttpException(200, 'no user with this id');
        }

        if (!empty($name)) {
            $user->name = $name;
        }

        if (!empty($mobile)) {
            $user->mobile = $mobile;
        }

        if (!empty($gender)) {
            $user->gender = $gender;
        }

        if (!empty($birthdate)) {
            $user->birthdate = $birthdate;
        }

        if (!empty($device_IMEI)) {
            $user->device_IMEI = $device_IMEI;
        }

        if (!empty($firebase_token)) {
            $user->firebase_token = $firebase_token;
        }

        if (isset($private) && $private !== '') {
            $user->private = $private;
        }

        if (isset($is_adult_and_smoker)) {
            $user->is_adult_and_smoker = $is_adult_and_smoker == 'null' ? null : $is_adult_and_smoker;
        }

        if (isset($lang)) {
            $user->lang = $lang === 'Ar' ? 'Ar' : '';
        }

        if (!$user->save()) {
            throw new HttpException(200, $this->_getErrors($user));
        }

        if (!empty($category_ids)) {
            // remove old user categories
            UserCategory::deleteAll('user_id = ' . $user->id);

            $categories = explode(',', $category_ids);
            foreach ($categories as $category) {
                $user_category = new UserCategory();
                $user_category->user_id = $user->id;
                $user_category->category_id = $category;
                $user_category->save();
            }
        }
    }

    /***************************************/
    /*************** Follow ****************/
    /***************************************/

    /**
     * @api {post} /api/search-for-user Search for user by name
     * @apiName SearchForUser
     * @apiGroup Follow
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} name User's name of the user you want to find.
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} users the list of users
     */
    public function actionSearchForUser($name)
    {
        $this->_addOutputs(['users']);

        $query = User::find()
            ->where(['like', 'name', $name.'%', false])
            ->andWhere(['!=', 'id', $this->logged_user['id']])
            ->orderBy(['id' => SORT_DESC]);
        $model = $this->_getModelWithPagination($query);

        $users = array();
        foreach ($model as $key => $user) {
            $users[] = $this->_getUserData($user);
        }

        $this->output['users'] = $users;
    }

    /**
     * @api {post} /api/follow-user Follow user
     * @apiName FollowUser
     * @apiGroup Follow
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} receiver_id ID of user to be followed.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {String} request the added request object
     */
    public function actionFollowUser($receiver_id)
    {
        $this->_addOutputs(['follow']);

        $follow = $this->_isFollowing($this->logged_user['id'], $receiver_id);

        if ($follow !== null) {
            throw new HttpException(200, 'You are already following this user');
        }

        $model = new Follow;
        $model->user_id = $this->logged_user['id'];
        $model->receiver_id = $receiver_id;
        $model->status = '1';

        if (!$model->save()) {
            throw new HttpException(200, $this->_getErrors($model));
        }

        $this->output['follow'] = $model->attributes;

        // send notification
        $type = 'new_follow';
        $title = '{new_follow_title}';
        $body = $model->user->name . ' {new_follow_body}';
        $data = [
            'type' => $type,
            'payload' => [
                'request_id' => $model->id,
                'user_id' => $model->user_id,
            ]
        ];
        $this->_sendNotification($model->receiver, $title, $body, $data);
    }

    /**
     * @api {post} /api/get-following Get following
     * @apiName GetFollowing
     * @apiGroup Follow
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} requests requests details.
     */
    public function actionGetFollowing()
    {
        $this->_addOutputs(['users']);

        $query = Follow::find()
            ->where(['user_id' => $this->logged_user['id'], 'status' => '1']);
        $model = $this->_getModelWithPagination($query);

        $following = array();
        foreach ($model as $key => $follow) {
            $following[] = $this->_getUserMinimalData($follow->receiver);
        }

        $this->output['users'] = $following;
    }

    /**
     * @api {post} /api/unfollow-user Unfollow user
     * @apiName UnfollowUser
     * @apiGroup Follow
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} receiver_id the ID of the user to unfollow.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionUnfollowUser($receiver_id)
    {
        $follow = Follow::find()
            ->where(['user_id' => $this->logged_user['id'], 'receiver_id' => $receiver_id])
            ->one();

        if (empty($follow)) {
            throw new HttpException(200, "You are not following this user");
        }

        if (!$follow->delete()) {
            throw new HttpException(200, $this->_getErrors($follow));
        }
    }

    /**
     * @api {post} /api/get-followers Get followers
     * @apiName GetFollowers
     * @apiGroup Follow
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} users List of followers.
     */
    public function actionGetFollowers()
    {
        $this->_addOutputs(['users']);

        $query = Follow::find()
            ->where(['receiver_id' => $this->logged_user['id'], 'status' => '1']);
        $model = $this->_getModelWithPagination($query);

        $following = array();
        foreach ($model as $key => $follow) {
            $following[] = $this->_getUserMinimalData($follow->user);
        }

        $this->output['users'] = $following;
    }

    /**
     * @api {post} /api/following-feed Get feed of the following
     * @apiName GetFollowingFeed
     * @apiGroup Follow
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} users List of followers.
     */
    public function actionGetFollowingFeed()
    {
        // TODO
    }

    /***************************************/
    /************* Categories **************/
    /***************************************/

    /**
     * @api {post} /api/get-categories Get the categories
     * @apiName GetCategories
     * @apiGroup Category
     *
     * @apiParam {String} page Page number (optional).
     * @apiParam {String} category_id Parent category ID (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} categories categories details.
     */
    public function actionGetCategories($category_id = null)
    {
        $this->_addOutputs(['categories']);

        $this->output['categories'] = $this->_getCategories(empty($category_id) ? null : $category_id);
    }

    /***************************************/
    /************** Business ***************/
    /***************************************/

    /**
     * @api {post} /api/get-countries Get all countries
     * @apiName GetCountries
     * @apiGroup Business
     *
     * @apiParam {String} page Page number (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} countries List of Countries.
     */
    public function actionGetCountries()
    {
        $this->_addOutputs(['countries']);

        $query = Country::find();
        $model = $this->_getModelWithPagination($query);

        $countries = [];
        foreach ($model as $key => $country) {
            $temp['id'] = $country['id'];
            $temp['name'] = $country['name'.$this->lang];
            $temp['flag'] = $country['flag'.$this->lang];
            $countries[] = $temp;
        }

        $this->output['countries'] = $countries;
    }

    /**
     * @api {post} /api/get-cities Get all cities
     * @apiName GetCities
     * @apiGroup Business
     *
     * @apiParam {String} country_id Country's id to get cities inside.
     * @apiParam {String} page Page number (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} cities List of Cities.
     */
    public function actionGetCities($country_id)
    {
        $this->_addOutputs(['cities']);

        $query = City::find()
            ->where(['country_id' => $country_id]);
        $model = $this->_getModelWithPagination($query);

        $cities = [];
        foreach ($model as $key => $city) {
            $temp['id'] = $city['id'];
            $temp['name'] = $city['name'.$this->lang];
            $cities[] = $temp;
        }

        $this->output['cities'] = $cities;
    }

    /**
     * @api {post} /api/get-areas Get all areas
     * @apiName GetAreas
     * @apiGroup Business
     *
     * @apiParam {String} city_id City's id to get areas inside.
     * @apiParam {String} page Page number (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} areas List of get-areas.
     */
    public function actionGetAreas($city_id)
    {
        $this->_addOutputs(['areas']);

        $query = Area::find()
            ->where(['city_id' => $city_id]);
        $model = $this->_getModelWithPagination($query);

        $areas = [];
        foreach ($model as $key => $area) {
            $temp['id'] = $area['id'];
            $temp['name'] = $area['name'.$this->lang];
            $business_count = Branch::find()
                ->select('business_id')
                ->where(['area_id' => $area['id']])
                ->groupBy(['business_id'])
                ->count();
            $temp['business_count'] = (int) $business_count;
            $areas[] = $temp;
        }

        $this->output['areas'] = $areas;
    }

    /**
     * @api {post} /api/get-location Get area, city and country from lat and lng
     * @apiName GetLocation
     * @apiGroup Business
     *
     * @apiParam {String} lat Latitude.
     * @apiParam {String} lng Longitude.
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Object} area Area.
     * @apiSuccess {Object} city City.
     * @apiSuccess {Object} country Country.
     */
    public function actionGetLocation($lat, $lng)
    {
        $this->_addOutputs(['area', 'city', 'country']);

        $query = Area::find();

        $query->select(['*', '( 6371 * acos( cos( radians(' . $lat . ') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(' . $lng . ') ) + sin( radians(' . $lat . ') ) * sin( radians( lat ) ) ) ) AS distance']);
        $query->orderBy(['distance' => SORT_ASC]);
        $model = $this->_getModelWithPagination($query);

        $area = $model[0];

        $this->output['country'] = $area->city->country->attributes;
        $this->output['city'] = $area->city->attributes;
        $this->output['area'] = $area->attributes;
    }

    /**
     * @api {post} /api/get-flags Get all flags
     * @apiName GetFlags
     * @apiGroup Business
     *
     * @apiParam {String} page Page number (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} flags List of Flags.
     */
    public function actionGetFlags()
    {
        $this->_addOutputs(['flags']);

        $query = Flag::find();
        $model = $this->_getModelWithPagination($query);

        $flags = [];
        foreach ($model as $key => $flag) {
            $temp['id'] = $flag['id'];
            $temp['name'] = $flag['name'.$this->lang];
            $temp['icon'] = Url::base(true) . '/' . $flag['icon'];
            $flags[] = $temp;
        }

        $this->output['flags'] = $flags;
    }

    /**
     * @api {post} /api/add-flag Add new flag
     * @apiName AddFlag
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} name Flags's name.
     * @apiParam {String} nameAr Flags's Arabic name.
     * @apiParam {File} Media[file] Flag's icon file (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionAddFlag($name, $nameAr)
    {
        if ($this->logged_user['role'] !== "business") {
            throw new HttpException(200, 'you are not allowed to add new flag');
        }

        $flag = new Flag;
        $flag->name = $name;
        $flag->nameAr = $nameAr;

        if (!$flag->save()) {
            throw new HttpException(200, $this->_getErrors($flag));
        }

        if (!empty($_FILES['Media'])) {
            $this->_uploadFile($flag->id, 'Flag', 'flag_icon', $flag, 'icon');
        }
    }

    /**
     * @api {post} /api/get-interests Get all interests
     * @apiName GetInterests
     * @apiGroup Business
     *
     * @apiParam {String} page Page number (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} interests List of Interests.
     */
    public function actionGetInterests()
    {
        $this->_addOutputs(['interests']);

        $query = Interest::find();
        $model = $this->_getModelWithPagination($query);

        $interests = [];
        foreach ($model as $key => $interest) {
            $temp['id'] = $interest['id'];
            $temp['name'] = $interest['name'.$this->lang];
            $interests[] = $temp;
        }

        $this->output['interests'] = $interests;
    }

    /**
     * @api {post} /api/add-business Add new business
     * @apiName AddBusiness
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} name business name.
     * @apiParam {String} nameAr business arabic name.
     * @apiParam {String} phone business phone.
     * @apiParam {String} price average business price.
     * @apiParam {String} description business description.
     * @apiParam {String} descriptionAr business arabic description.
     * @apiParam {String} category_id Category's id to add business inside.
     * @apiParam {String} website business website. (optional)
     * @apiParam {String} fb_page business Facebook page. (optional)
     * @apiParam {Array} interests array of interests strings to add to business, ex. interest1,interest2,interest3 (optional).
     * @apiParam {Array} interestsAr Arabic array of interests strings to add to business, ex. interest1,interest2,interest3 (optional).
     * @apiParam {File} Media[file] Business's main image file (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {String} business_id the added business id
     */
    public function actionAddBusiness($name, $nameAr, $phone, $price, $description, $descriptionAr, $category_id, $website = null, $fb_page = null, $interests = null, $interestsAr = null)
    {
        $this->_addOutputs(['business_id']);

        if ($this->logged_user['role'] !== "business") {
            throw new HttpException(200, 'you are not allowed to add new business');
        }

        $business = new Business;
        $business->name = $name;
        $business->nameAr = $nameAr;
        $business->phone = $phone;
        $business->price = $price;
        $business->description = $description;
        $business->descriptionAr = $descriptionAr;
        $business->category_id = $category_id;
        $business->admin_id = $this->logged_user['id'];
        $business->approved = false;

        if (!empty($website)) {
            $business->website = $website;
        }
        if (!empty($fb_page)) {
            $business->fb_page = $fb_page;
        }

        if (!$business->save()) {
            throw new HttpException(200, $this->_getErrors($business));
        }

        if (!empty($interests)) {
            $interests = explode(',', $interests);
            $interestsAr = explode(',', $interestsAr);
            for ($i = 0; $i < sizeof($interests); ++$i) {
                $interest = $interests[$i];
                $temp_interest = Interest::find()->where('name = :name', [':name' => $interest])->one();
                if (empty($temp_interest)) {
                    $temp_interest = new Interest();
                    $temp_interest->name = $interest;
                    $temp_interest->nameAr = $interestsAr[$i];
                    $temp_interest->save();
                }

                $business_interest = new BusinessInterest();
                $business_interest->business_id = $business->id;
                $business_interest->interest_id = $temp_interest->id;
                $business_interest->save();
            }
        }

        if (!empty($_FILES['Media'])) {
            $this->_uploadFile($business->id, 'Business', 'business_image', $business, 'main_image');
        }

        if ($this->logged_user['role'] === 'business') {
            $link = Url::to(['business/view', 'id' => $business->id], true);

            Yii::$app->mailer->compose()
                ->setFrom(['support@myblabber.com' => 'MyBlabber Support'])
                ->setTo($this->adminEmail)
                ->setSubject('New Buisness Profile (' . $business->name . ')')
                ->setTextBody('New business profile created through the mobile application, check it from here: ' . $link)
                ->setHtmlBody('New business profile created through the mobile application, check it from here: <a href="' . $link . '">link</a>')
                ->send();
        }

        $this->output['business_id'] = $business->id;
    }

    /**
     * @api {post} /api/edit-business Edit business details
     * @apiName EditBusiness
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} business_id business's id to edit.
     * @apiParam {String} name business name. (optional)
     * @apiParam {String} nameAr business arabic name. (optional)
     * @apiParam {String} phone business phone. (optional)
     * @apiParam {String} price average business price. (optional)
     * @apiParam {String} description business description. (optional)
     * @apiParam {String} descriptionAr business arabic description. (optional)
     * @apiParam {String} category_id Category's id to add business inside. (optional)
     * @apiParam {String} website business website. (optional)
     * @apiParam {String} fb_page business Facebook page. (optional)
     * @apiParam {Array} interests array of interests strings to add to business, ex. interest1,interest2,interest3 (optional).
     * @apiParam {Array} interestsAr Arabic array of interests strings to add to business, ex. interest1,interest2,interest3 (optional).
     * @apiParam {File} Media[file] Business's main image file (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionEditBusiness($business_id, $name = null, $nameAr = null, $email = null, $phone = null, $price = null, $description = null, $descriptionAr = null, $category_id = null, $website = null, $fb_page = null, $interests = null, $interestsAr = null)
    {
        $business = Business::find()
            ->where(['id' => $business_id])
            ->one();

        if ($business === null) {
            throw new HttpException(200, 'no business with this id');
        }

        if ($business->admin_id != $this->logged_user['id']) {
            throw new HttpException(200, 'you are not allowed to edit this business');
        }

        if (!empty($name)) {
            $business->name = $name;
        }
        if (!empty($nameAr)) {
            $business->nameAr = $nameAr;
        }
        if (!empty($email)) {
            $business->email = $email;
        }
        if (!empty($phone)) {
            $business->phone = $phone;
        }
        if (!empty($price)) {
            $business->price = $price;
        }
        if (!empty($description)) {
            $business->description = $description;
        }
        if (!empty($descriptionAr)) {
            $business->descriptionAr = $descriptionAr;
        }
        if (!empty($category_id)) {
            $business->category_id = $category_id;
        }
        if (!empty($website)) {
            $business->website = $website;
        }
        if (!empty($fb_page)) {
            $business->fb_page = $fb_page;
        }

        if (!$business->save()) {
            throw new HttpException(200, $this->_getErrors($business));
        }

        if (!empty($interests)) {
            // remove old interests
            BusinessInterest::deleteAll('business_id = ' . $business->id);

            $interests = explode(',', $interests);
            $interestsAr = explode(',', $interestsAr);
            for ($i = 0; $i < sizeof($interests); ++$i) {
                $interest = $interests[$i];
                $temp_interest = Interest::find()->where('name = :name', [':name' => $interest])->one();
                if (empty($temp_interest)) {
                    $temp_interest = new Interest();
                    $temp_interest->name = $interest;
                    $temp_interest->nameAr = $interestsAr[$i];
                    $temp_interest->save();
                }

                $business_interest = new BusinessInterest();
                $business_interest->business_id = $business->id;
                $business_interest->interest_id = $temp_interest->id;
                $business_interest->save();
            }
        }

        if (!empty($_FILES['Media'])) {
            $this->_uploadFile($business->id, 'Business', 'business_image', $business, 'main_image');
        }
    }

    /**
     * @api {post} /api/delete-business Delete business
     * @apiName DeleteBusiness
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} business_id Business's id to delete.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionDeleteBusiness($business_id) {
        $business = Business::find()
            ->where(['id' => $business_id])
            ->one();

        if ($business === null) {
            throw new HttpException(200, 'no business with this id');
        }

        if ($business->admin_id != $this->logged_user['id']) {
            throw new HttpException(200, 'you are not allowed to edit this business');
        }

        $business->approved = false;
        if (!$business->save()) {
            throw new HttpException(200, $this->_getErrors($model));
        }
    }

    /**
     * @api {post} /api/get-businesses Get businesses by category or owner
     * @apiName GetBusinessesByOwner
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} area_id Area's id.
     * @apiParam {String} category_id Category's id to get businesses inside (optional).
     * @apiParam {Integer} admin_id Get only businesses this user manages (optional).
     * @apiParam {String} nearby the search coordinates for nearby business, value lat,lng, ex. 32.22,37.11 (optional).
     * @apiParam {String} page Page number (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} businesses businesses details.
     */
    public function actionGetBusinesses($area_id = null, $nearby = null, $category_id = null, $admin_id = null)
    {
        // TODO
        $this->_addOutputs(['businesses']);

        $conditions = [];

        if ($category_id) {
            $conditions['category_id'] = $this->_getAllCategoryTreeIds($category_id);
        }
        if ($admin_id) {
            $conditions['admin_id'] = $this->logged_user['id'];
        }
        $lat_lng = $nearby === null ? null : explode(',', $nearby);

        $this->output['businesses'] = $this->_getBusinesses($conditions, $area_id, ['name' => SORT_ASC], $lat_lng);
    }

    /**
     * @api {post} /api/search-businesses Get businesses by search
     * @apiName SearchBusinesses
     * @apiGroup Business
     *
     * @apiParam {String} area_id Area's id.
     * @apiParam {String} name the search keyword for business name (optional).
     * @apiParam {String} address the search keyword for business address (optional).
     * @apiParam {String} city the search keyword for business city (optional).
     * @apiParam {String} city_id the business city_id (optional).
     * @apiParam {String} category the search keyword for business category (optional).
     * @apiParam {String} category_id the business category_id (optional).
     * @apiParam {String} flag the search keyword for business flag (optional).
     * @apiParam {String} flag_id the business flag_id (optional).
     * @apiParam {String} interest the search keyword for business interest (optional).
     * @apiParam {String} interest_id the business interest_id (optional).
     * @apiParam {String} nearby the search coordinates for nearby business, value lat,lng, ex. 32.22,37.11 (optional).
     * @apiParam {String} page Page number (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} businesses businesses details.
     */
    public function actionSearchBusinesses($area_id, $name = null, $address = null, $city = null, $city_id = null, $category = null, $category_id = null, $flag = null, $flag_id = null, $interest = null, $interest_id = null, $nearby = null)
    {
        // TODO
        $this->_addOutputs(['businesses']);

        $conditions[] = 'or';
        $andConditions[] = 'and';

        if (!empty($name)) {
            $tokens = explode(' ', trim($name));
            $names = implode('%', $tokens);
            $conditions[] = "name like '%$names%'";
            $conditions[] = "nameAr like '%$names%'";
        }
        if (!empty($address)) {
            $andConditions[] = ['like', 'address', $address];
            $andConditions[] = ['like', 'addressAr', $address];
        }
        if (!empty($city)) {
            $model = City::find()->where(['like', 'name'.$this->lang, $city])->all();
            $search_keyword = ArrayHelper::getColumn($model, 'id');
            $andConditions[] = ['city_id' => $search_keyword];
        }
        if (!empty($city_id)) {
            $andConditions[] = ['city_id' => $city_id];
        }
        if (!empty($category)) {
            $model = Category::find()->where(['like', 'name'.$this->lang, $category])->all();
            $search_keyword = ArrayHelper::getColumn($model, 'id');
            $conditions[] = ['category_id' => $search_keyword];
        }
        if (!empty($category_id)) {
            $conditions[] = ['category_id' => $category_id];
        }
        if (!empty($flag)) {
            $flags = explode(' ', $flag);
            $flagsSubCondition[] = 'or';
            foreach ($flags as $flag) {
                $flagsSubCondition[] = ['like', 'name', $flag];
                $flagsSubCondition[] = ['like', 'nameAr', $flag];
            }
            $model = Flag::find()->where($flagsSubCondition)->all();
            $search_keyword = ArrayHelper::getColumn($model, 'id');
            $model = BusinessFlag::find()->where(['flag_id' => $search_keyword])->all();
            $ids = ArrayHelper::getColumn($model, 'business_id');
            $conditions[] = ['id' => $ids];
        }
        if (!empty($flag_id)) {
            $model = BusinessFlag::find()->where(['flag_id' => $flag_id])->all();
            $ids = ArrayHelper::getColumn($model, 'business_id');
            $conditions[] = ['id' => $ids];
        }
        if (!empty($interest)) {
            $interests = explode(' ', $interest);
            $interestsSubCondition[] = 'or';
            foreach ($interests as $interest) {
                $interestsSubCondition[] = ['like', 'name', $interest];
                $interestsSubCondition[] = ['like', 'nameAr', $interest];
            }
            $model = Interest::find()->where($interestsSubCondition)->all();
            $search_keyword = ArrayHelper::getColumn($model, 'id');
            $model = BusinessInterest::find()->where(['interest_id' => $search_keyword])->all();
            $ids = ArrayHelper::getColumn($model, 'business_id');
            $conditions[] = ['id' => $ids];
        }
        if (!empty($interest_id)) {
            $model = BusinessInterest::find()->where(['interest_id' => $interest_id])->all();
            $ids = ArrayHelper::getColumn($model, 'business_id');
            $conditions[] = ['id' => $ids];
        }

        $lat_lng = empty($nearby) ? null : explode(',', $nearby);
        $businesses = $this->_getBusinesses($conditions, $area_id, null, $lat_lng, $andConditions);
        if (empty($businesses)) {
            $tokens = explode('/\s+/', trim($name));
            $first = empty($tokens) ? '' : $tokens[0];
            $conditions[] = "name like '%$first%'";
            $conditions[] = "nameAr like '%$first%'";
            $businesses = $this->_getBusinesses($conditions, $area_id, null, $lat_lng, $andConditions);
        }
        $this->output['businesses'] = $businesses;
    }

    /**
     * @api {post} /api/search-businesses-by-type Get businesses by search type
     * @apiName SearchBusinessesByType
     * @apiGroup Business
     *
     * @apiParam {String} area_id Area's id.
     * @apiParam {String} type Search by (recently_added, recently_viewed, recently_visited).
     * @apiParam {String} page Page number (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} businesses businesses details.
     */
    public function actionSearchBusinessesByType($area_id, $type)
    {
        $this->_addOutputs(['businesses']);

        $search_type = $type;
        if ($search_type === 'recently_added') {
            $this->output['businesses'] = $this->_getBusinesses(null, $area_id, ['created' => SORT_DESC]);
        } else if ($search_type === 'recently_viewed') {
            $query = BusinessView::find()
                ->select(['business_id', 'business_view.id'])
                ->orderBy(['featured' => SORT_DESC, 'business_view.id' => SORT_DESC])
                ->joinWith('business')
                ->andWhere(['business.area_id' => $area_id]);
            $model = $this->_getModelWithPagination($query);

            $businesses = [];
            $ids_list = [];
            foreach ($model as $key => $business_view) {
                if (in_array($business_view->business_id, $ids_list)) {
                    continue;
                }
                $ids_list[] = $business_view->business_id;
                $businesses[] = $this->_getBusinessData($business_view->business);
            }
            $this->output['businesses'] = $businesses;
        } else if ($search_type === 'recently_viewed') {
            // TODO
        } else {
            throw new HttpException(200, 'not supported search type');
        }
    }

    /**
     * @api {post} /api/get-featured-businesses Get featured businesses
     * @apiName GetFeaturedBusinesses
     * @apiGroup Business
     *
     * @apiParam {String} area_id area_id Area ID.
     * @apiParam {String} page Page number (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} businesses businesses details.
     */
    public function actionGetFeaturedBusinesses($area_id)
    {
        $this->_addOutputs(['businesses']);

        $nameVar = 'name'.$this->lang;
        $businesses = $this->_getBusinesses(['featured' => 1], $area_id, ["$nameVar" => SORT_ASC], null, null);

        $this->output['businesses'] = $businesses;
    }

    /**
     * @api {post} /api/get-business-data Get business data
     * @apiName GetBusinessData
     * @apiGroup Business
     *
     * @apiParam {String} business_id business's id to get it's details.
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} business_data business details.
     */
    public function actionGetBusinessData($business_id)
    {
        $this->_addOutputs(['business_data']);

        $model = Business::find()
            ->where(['id' => $business_id])
            ->andWhere(['approved' => true])
            ->one();
        if ($model === null) {
            throw new HttpException(200, 'no business with this id');
        }

        $result = $this->_addBusinessView($business_id, $this->logged_user['id']);

        if ($result !== 'done') {
            throw new HttpException(200, $result);
        }

        $this->output['business_data'] = $this->_getBusinessData($model);
    }

    /**
     * @api {post} /api/add-branch Add new branch
     * @apiName AddBranch
     * @apiGroup Branch
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} business_id business ID.
     * @apiParam {String} name branch name.
     * @apiParam {String} nameAr branch arabic name.
     * @apiParam {String} address branch address.
     * @apiParam {String} addressAr branch arabic address.
     * @apiParam {String} country_id Country's id to add branch inside.
     * @apiParam {String} city_id City's id to add branch inside.
     * @apiParam {String} area_id Area's id to add branch inside.
     * @apiParam {String} phone branch phone.
     * @apiParam {String} operation_hours branch operation hours.
     * @apiParam {String} lat branch lat.
     * @apiParam {String} lng branch lng.
     * @apiParam {String} is_reservable whether branch allows reservations. (optional)
     * @apiParam {Array} flags array of flags IDs, ex. 1,2,3 (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {String} branch_id the added branch id
     */
    public function actionAddBranch($business_id, $name = '', $nameAr = '', $address, $addressAr, $country_id, $city_id, $area_id, $phone, $operation_hours, $lat, $lng, $is_reservable = null, $flags = null)
    {
        $this->_addOutputs(['branch_id']);

        if ($this->logged_user['role'] !== "business") {
            throw new HttpException(200, 'you are not allowed to add new branch');
        }

        $branch = new Branch;
        $branch->business_id = $business_id;
        $branch->name = $name;
        $branch->nameAr = $nameAr;
        $branch->phone = $phone;
        $branch->operation_hours = $operation_hours;
        $branch->address = $address;
        $branch->addressAr = $addressAr;
        $branch->country_id = $country_id;
        $branch->city_id = $city_id;
        $branch->area_id = $area_id;
        $branch->lat = $lat;
        $branch->lng = $lng;
        $branch->admin_id = $this->logged_user['id'];
        $branch->approved = false;
        $branch->is_reservable = $is_reservable ? $is_reservable : false;

        if (!empty($website)) {
            $branch->website = $website;
        }
        if (!empty($fb_page)) {
            $branch->fb_page = $fb_page;
        }

        if (!$branch->save()) {
            throw new HttpException(200, $this->_getErrors($branch));
        }

        if (!empty($flags)) {
            $flags = explode(',', $flags);
            foreach ($flags as $flag) {
                $branch_flag = new BranchFlag();
                $branch_flag->branch_id = $branch->id;
                $branch_flag->flag_id = $flag;
                $branch_flag->save();
            }
        }

        $this->output['branch_id'] = $branch->id;
    }

    /**
     * @api {post} /api/edit-branch Edit branch details
     * @apiName EditBranch
     * @apiGroup Branch
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} branch_id branch's id to edit.
     * @apiParam {String} name branch name (optional).
     * @apiParam {String} nameAr branch arabic name (optional).
     * @apiParam {String} address branch address (optional).
     * @apiParam {String} addressAr branch arabic address (optional).
     * @apiParam {String} city_id City's id to add branch inside (optional).
     * @apiParam {String} phone branch phone (optional).
     * @apiParam {String} operation_hours branch operation hours (optional).
     * @apiParam {String} lat branch lat (optional).
     * @apiParam {String} lng branch lng (optional).
     * @apiParam {String} is_reservable whether branch allows reservations (optional).
     * @apiParam {Array} flag_ids array of flags IDs, ex. 1,2,3 (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionEditBranch($branch_id, $name = null, $nameAr = null, $address = null, $addressAr = null, $city_id = null, $phone = null, $operation_hours = null, $lat = null, $lng = null, $is_reservable = null, $flag_ids = null)
    {
        $branch = Branch::find()
            ->where(['id' => $branch_id])
            ->one();
        
        if ($branch === null) {
            throw new HttpException(200, 'no branch with this id');
        }

        if ($branch->business->admin_id != $this->logged_user['id']) {
            throw new HttpException(200, 'you are not allowed to edit this branch');
        }

        if (!empty($business_id)) {
            $business = Business::find()->where(['id' => $business_id])->one();
            if (empty($business)) {
                throw new HttpException(200, 'no business with this id');
            }
            $branch->business_id = $business_id;
        }
        if (!empty($name)) {
            $branch->name = $name;
        }
        if (!empty($nameAr)) {
            $branch->nameAr = $nameAr;
        }
        if (!empty($address)) {
            $branch->address = $address;
        }
        if (!empty($addressAr)) {
            $branch->addressAr = $addressAr;
        }
        if (!empty($city_id)) {
            $branch->city_id = $city_id;
        }
        if (!empty($phone)) {
            $branch->phone = $phone;
        }
        if (!empty($operation_hours)) {
            $branch->operation_hours = $operation_hours;
        }
        if (!empty($lat)) {
            $branch->lat = $lat;
        }
        if (!empty($lng)) {
            $branch->lng = $lng;
        }
        if (!empty($is_reservable)) {
            $branch->is_reservable = $is_reservable;
        }

        if (!$branch->save()) {
            throw new HttpException(200, $this->_getErrors($branch));
        }

        if (!empty($flag_ids)) {
            BranchFlag::deleteAll('branch_id = ' . $branch->id);
            $flags = explode(',', $flag_ids);
            foreach ($flags as $flag) {
                $branch_flag = new BranchFlag();
                $branch_flag->branch_id = $branch->id;
                $branch_flag->flag_id = $flag;
                $branch_flag->save();
            }
        }
    }

    /**
     * @api {post} /api/delete-branch Delete branch
     * @apiName DeleteBranch
     * @apiGroup Branch
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} branch_id Branch's id to delete.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionDeleteBranch($branch_id) {
        $branch = Branch::find()
            ->where(['id' => $branch_id])
            ->one();

        if ($branch === null) {
            throw new HttpException(200, 'no branch with this id');
        }

        if ($branch->business->admin_id != $this->logged_user['id']) {
            throw new HttpException(200, 'you are not allowed to edit this branch');
        }

        $branch->approved = false;
        if (!$branch->save()) {
            throw new HttpException(200, $this->_getErrors($model));
        }
    }

    /**
     * @api {post} /api/get-branches Get branches for a specific business
     * @apiName GetBranches
     * @apiGroup Branch
     *
     * @apiParam {String} user_id User's id (optional).
     * @apiParam {String} auth_key User's auth key (optional).
     * @apiParam {Boolean} business_id Get only branches this user manages (optional).
     * @apiParam {String} nearby the search coordinates for nearby branches, value lat,lng, ex. 32.22,37.11 (optional).
     * @apiParam {String} page Page number (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} branches branches details.
     */
    public function actionGetBranches($business_id)
    {
        $this->_addOutputs(['branches']);

        $business = Business::findOne($business_id);
        if (empty($business)) {
            throw new HttpException(200, 'no business with this id');
        }

        $conditions['business_id'] = $business_id;
        $lat_lng = empty($nearby) ? null : explode(',', $nearby);

        $this->output['branches'] = $this->_getBranches($conditions, $lat_lng);
    }

    /**
     * @api {post} /api/get-branch-data Get branch data
     * @apiName GetBranchData
     * @apiGroup Branch
     *
     * @apiParam {String} branch_id branch's id to get it's details.
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} branch_data branch details.
     */
    public function actionGetBranchData($branch_id)
    {
        $this->_addOutputs(['branch_data']);

        $model = Branch::find()->where(['id' => $branch_id])->one();
        if ($model === null) {
            throw new HttpException(200, 'no branch with this id');
        }

        $this->output['branch_data'] = $this->_getBranchData($model);
    }

    /**
     * @api {post} /api/save-business Save business to user list
     * @apiName SaveBusiness
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} business_id business's id to save.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionSaveBusiness($business_id)
    {
        $model = Business::find()
            ->where(['id' => $business_id])
            ->one();
        if ($model === null) {
            throw new HttpException(200, 'no business with this id');
        }

        $savedBusiness = new SavedBusiness;
        $savedBusiness->user_id = $this->logged_user['id'];
        $savedBusiness->business_id = $business_id;

        if (!$savedBusiness->save()) {
            throw new HttpException(200, $this->_getErrors($savedBusiness));
        }

        $user = User::findOne($model->admin_id);
        if (!empty($user) && $user->role === 'business') {
            $type = 'favorite';
            $title = '{new_favorite_title}';
            $body = $savedBusiness->user->name . ' {new_favorite_body} ' . $savedBusiness->business->name;
            $data = [
                'type' => $type,
                'payload' => [
                    'saved_business_id' => $savedBusiness->id,
                    'user_id' => $savedBusiness->user_id,
                    'business_id' => $savedBusiness->business_id,
                ]
            ];
            $this->_addNotification($user->id, $type, $title, $body, $data);
            $this->_sendNotification($user, $title, $body, $data);
        }
    }

    /**
     * @api {post} /api/delete-saved-business Delete Saved Business
     * @apiName DeleteSavedBusiness
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} saved_business_id saved business's id to delete it.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionDeleteSavedBusiness($saved_business_id)
    {
        $model = SavedBusiness::findOne(['user_id' => $this->logged_user['id'], 'business_id' => $saved_business_id]);

        if (!$model->delete()) {
            throw new HttpException(200, $this->_getErrors($model));
        }
    }

    /**
     * @api {post} /api/get-saved-businesses Get all saved businesses for users or businesses
     * @apiName GetSavedBusinesses
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} business_id Business's id of Business you want to get the saved businesses for (optional).
     * @apiParam {String} user_id User's id of User you want to get the saved businesses for (optional).
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} businesses businesses details.
     */
    public function actionGetSavedBusinesses($business_id = null, $user_id = null)
    {
        if (!empty($business_id)) {
            $user = User::findOne($this->logged_user['id']);
            if ($user->role !== 'business') {
                throw new HttpException(200, 'you are not allowed to view this list');
            }

            $this->_addOutputs(['users']);

            $model = SavedBusiness::find()
                ->select('user_id')
                ->where(['business_id' => $business_id])
                ->all();
            $user_list = [];
            foreach ($model as $key => $business) {
                $user_data = User::findOne($business->user_id);
                $user_list[] = $this->_getUserMinimalData($user_data);
            }

            $this->output['users'] = $user_list;
        }

        if (!empty($user_id)) {
            $this->_addOutputs(['businesses']);

            $model = SavedBusiness::find()
                ->select('business_id')
                ->where(['user_id' => $user_id])
                ->all();
            $ids_list = [];
            foreach ($model as $key => $business) {
                $ids_list[] = $business->business_id;
            }

            $conditions = ['id' => $ids_list];
            $this->output['businesses'] = $this->_getBusinesses($conditions);
        }
    }

    /**
     * @api {post} /api/checkin Check-in business
     * @apiName CheckinBusiness
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} branch_id branch's id to checkin.
     * @apiParam {String} text User's review about the place (optional).
     * @apiParam {String} rating User's rating about the place (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {String} checkin_id the added checkin id
     */
    public function actionCheckin($branch_id, $text = null, $rating = null)
    {
        $this->_addOutputs(['checkin_id']);

        $branch = Branch::findOne($branch_id);
        if (empty($branch)) {
            throw new HttpException(200, 'no branch with this id');
        }

        $checkin = new Checkin;
        $checkin->user_id = $this->logged_user['id'];
        $checkin->branch_id = $branch_id;
        $checkin->text = $text;
        $checkin->rating = $rating;

        if (!$checkin->save()) {
            throw new HttpException(200, $this->_getErrors($checkin));
        }

        $business = $branch->business;
        $busines->rating = $this->_calcRating($business->id);
        if (!$business->save()) {
            throw new HttpException(200, $this->_getErrors($business));
        }

        $this->output['checkin_id'] = $checkin->id;

        $user = User::findOne($business->admin_id);
        if (!empty($user) && $user->role === 'business') {
            $type = 'checkin';
            $title = '{new_checkin_title}';
            $body = $checkin->user->name . ' {new_checkin_body} ' . $checkin->business->name;
            $data = [
                'type' => $type,
                'payload' => [
                    'checkin_id' => $checkin->id,
                    'user_id' => $checkin->user_id,
                    'business_id' => $checkin->branch->business_id,
                ]
            ];
            $this->_addNotification($user->id, $type, $title, $body, $data);
            $this->_sendNotification($user, $title, $body, $data);
        }
    }

    /**
     * @api {post} /api/delete-checkin Delete Checkin
     * @apiName DeleteCheckin
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} checkin_id checkin's id to delete it.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionDeleteCheckin($checkin_id)
    {
        $model = Checkin::findOne($checkin_id);

        if (!$model->delete()) {
            throw new HttpException(200, $this->_getErrors($model));
        }
    }

    /**
     * @api {post} /api/get-checkins Get all checkins for user or business
     * @apiName GetCheckins
     * @apiGroup Business
     *
     * @apiParam {String} branch_id Business's id (optional).
     * @apiParam {String} user_id User's id (optional).
     * @apiParam {String} page Page number (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} checkins checkins details.
     */
    public function actionGetCheckins($branch_id = null, $user_id = null)
    {
        $this->_addOutputs(['checkins']);

        $conditions = [];
        if (!empty($branch_id)) {
            $conditions['branch_id'] = $branch_id;
        }
        if (!empty($user_id)) {
            $conditions['user_id'] = $user_id;
        }
        $this->output['checkins'] = $this->_getCheckins($conditions);
    }

    /**
     * @api {post} /api/review Review business
     * @apiName ReviewBusiness
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} branch_id business's id to review.
     * @apiParam {String} text User's review about the place.
     * @apiParam {String} rating User's rating about the place.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {String} review_id the added review id
     */
    public function actionReview($branch_id, $text, $rating)
    {
        $this->_addOutputs(['review_id']);

        $branch = Business::find()
            ->where(['id' => $branch_id])
            ->one();
        if ($branch === null) {
            throw new HttpException(200, 'no branch with this id');
        }

        $review = new Review;
        $review->user_id = $this->logged_user['id'];
        $review->branch_id = $branch_id;
        $review->text = $text;
        $review->rating = $rating;

        if (!$review->save()) {
            throw new HttpException(200, $this->_getErrors($review));
        }

        $business = $branch->business;
        $business->rating = $this->_calcRating($business_id);

        if (!$business->save()) {
            throw new HttpException(200, $this->_getErrors($business));
        }

        $this->output['review_id'] = $review->id;

        // send notifications
        $user = User::findOne($business->admin_id);
        if (!empty($user) && $user->role === 'business') {
            $type = 'review';
            $title = '{new_review_title}';
            $body = $review->user->name . ' {new_review_body} ' . $business->name;
            $data = [
                'type' => $type,
                'payload' => [
                    'review_id' => $review->id,
                    'user_id' => $review->user_id,
                    'business_id' => $business->id,
                ]
            ];
            $this->_addNotification($user->id, $type, $title, $body, $data);
            $this->_sendNotification($user, $title, $body, $data);
        }

        if (preg_match_all('/(?<!\w)@(\w+)/', $review->text, $matches)) {
            $users = $matches[1];
            foreach ($users as $id) {
                $user = User::findOne(['id' => $id]);
                if (empty($user)) {
                    continue;
                }

                $type = 'review_tag';
                $title = '{new_review_tag_title}';
                $body = $review->user->name . ' {new_review_tag_body} ' . $business->name;
                $data = [
                    'type' => $type,
                    'payload' => [
                        'review_id' => $review->id,
                        'user_id' => $review->user_id,
                        'business_id' => $business->id,
                    ]
                ];
                $this->_addNotification($user->id, $type, $title, $body, $data);
                $this->_sendNotification($user, $title, $body, $data);
            }
        }
    }

    /**
     * @api {post} /api/edit-review Edit Review
     * @apiName EditReview
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} review_id review's id to edit.
     * @apiParam {String} text User's review about the place (optional).
     * @apiParam {String} rating User's rating about the place (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionEditReview($review_id, $text = null, $rating = null)
    {
        $review = Review::findOne($review_id);

        if ($review === null) {
            throw new HttpException(200, 'no review with this id');
        }

        if ($review->user_id != $this->logged_user['id']) {
            throw new HttpException(200, 'you are not allowed to edit this review');
        }

        $business = Business::find()
            ->where(['id' => $review->business_id])
            ->one();
        if ($business === null) {
            throw new HttpException(200, 'no business with this id');
        }

        if (!empty($text)) {
            $review->text = $text;
        }

        if (!empty($rating)) {
            $review->rating = $rating;
        }

        if (!$review->save()) {
            throw new HttpException(200, $this->_getErrors($review));
        }

        $business->rating = $this->_calcRating($review->business_id);

        if (!$business->save()) {
            throw new HttpException(200, $this->_getErrors($business));
        }

        // send notifications
        if (preg_match_all('/(?<!\w)@(\w+)/', $review->text, $matches)) {
            $users = $matches[1];
            foreach ($users as $id) {
                $user = User::findOne(['id' => $id]);
                if (empty($user)) {
                    continue;
                }

                $type = 'review_tag';
                $title = '{new_review_tag_title}';
                $body = $review->user->name . ' {new_review_tag_body} ' . $review->business->name;
                $data = [
                    'type' => $type,
                    'payload' => [
                        'review_id' => $review->id,
                        'user_id' => $review->user_id,
                        'business_id' => $review->business_id,
                    ]
                ];
                $this->_addNotification($user->id, $type, $title, $body, $data);
                $this->_sendNotification($user, $title, $body, $data);
            }
        }
    }

    /**
     * @api {post} /api/delete-review Delete Review
     * @apiName DeleteReview
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} review_id review's id to delete it.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionDeleteReview($review_id)
    {
        $review = Review::findOne($review_id);

        if ($review === null) {
            throw new HttpException(200, 'no review with this id');
        }

        if ($review->user_id != $this->logged_user['id']) {
            throw new HttpException(200, 'you are not allowed to delete this review');
        }

        if (!$review->delete()) {
            throw new HttpException(200, $this->_getErrors($review));
        }
    }

    /**
     * @api {post} /api/get-review Get all reviews for user or business
     * @apiName GetReview
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id (optional).
     * @apiParam {String} auth_key User's auth key (optional).
     * @apiParam {String} review_id Review's id.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} review review details.
     */
    public function actionGetReview($review_id)
    {
        $this->_addOutputs(['review']);
        $conditions = [];
        $conditions['id'] = $review_id;
        if (empty($review_id)) {
            throw new HttpException(200, 'Review id is required');
        }
        $reviews = $this->_getReviews($conditions);
        if (empty($reviews)) {
            throw new HttpException(200, 'Review not found');
        }
        $this->output['review'] = $reviews[0];
    }

    /**
     * @api {post} /api/get-reviews Get all reviews for user or business
     * @apiName GetReviews
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id (optional).
     * @apiParam {String} auth_key User's auth key (optional).
     * @apiParam {String} branch_id Business's id (optional).
     * @apiParam {String} user_id User's id (optional).
     * @apiParam {String} page Page number (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} reviews reviews details.
     */
    public function actionGetReviews($branch_id = null, $user_id = null)
    {
        $this->_addOutputs(['reviews']);

        $conditions = [];
        if (!empty($branch_id)) {
            $conditions['business_id'] = $branch_id;
        }
        if (!empty($user_id)) {
            $conditions['user_id'] = $user_id;
        }
        $this->output['reviews'] = $this->_getReviews($conditions);
    }

    /**
     * @api {post} /api/add-media Add new business media
     * @apiName AddMedia
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} business_id business's id to add media to.
     * @apiParam {String} type Media's type (image, menu, product or brochure).
     * @apiParam {File} Media[file] Business's new file (optional).
     * @apiParam {String} caption Media's caption (optional).
     * @apiParam {String} rating Media's rating (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionAddMedia($business_id, $type, $caption = null, $rating = null)
    {
        if (empty($_FILES['Media'])) {
            throw new HttpException(200, 'no file input');
        }

        $media = $this->_uploadFile($business_id, 'Business', $type, null, null, null, $caption, $rating);

        $business = Business::find()
            ->where(['id' => $business_id])
            ->one();
        if ($business === null) {
            throw new HttpException(200, 'no business with this id');
        }

        if (!empty($rating)) {
            $business->rating = $this->_calcRating($business_id);
            if (!$business->save()) {
                throw new HttpException(200, $this->_getErrors($business));
            }
        }

        // send notifications
        $user = User::findOne($business->admin_id);
        if (!empty($user) && $user->role === 'business') {
            $type = 'media';
            $title = '{new_' . $type . '_title}';
            $body = $media->user->name . ' {new_' . $type . '_body} ' . $business->name;
            $data = [
                'type' => $type,
                'payload' => [
                    'media_id' => $media->id,
                    'user_id' => $media->user_id,
                    'business_id' => $media->business_id,
                ]
            ];
            $this->_addNotification($user->id, $type, $title, $body, $data);
            $this->_sendNotification($user, $title, $body, $data);
        }
    }

    /**
     * @api {post} /api/delete-media Delete Media
     * @apiName DeleteMedia
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} media_id media's id to delete it.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionDeleteMedia($media_id)
    {
        $media = Media::findOne($media_id);

        if ($media === null) {
            throw new HttpException(200, 'no media with this id');
        }

        if ($media->user_id != $this->logged_user['id']) {
            throw new HttpException(200, 'you are not allowed to delete this media');
        }

        if (!unlink($media->url) || !$media->delete()) {
            throw new HttpException(200, $this->_getErrors($media));
        }
    }

    /**
     * @api {post} /api/get-media Get all media for user or business
     * @apiName GetMedia
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id (optional).
     * @apiParam {String} auth_key User's auth key (optional).
     * @apiParam {String} business_id_to_get Business's id (optional).
     * @apiParam {String} user_id_to_get User's id (optional).
     * @apiParam {String} page Page number (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} media media details.
     */
    public function actionGetMedia($business_id_to_get = null, $user_id_to_get = null)
    {
        $this->_addOutputs(['media']);

        $conditions = '';
        if (!empty($business_id_to_get)) {
            $conditions .= "object_id = '" . $business_id_to_get . "' AND ";
            $conditions .= "object_type = 'business' AND ";
            $conditions .= "type != 'business_image'";
        } else if (!empty($user_id_to_get)) {
            $conditions .= "user_id = '" . $user_id_to_get . "' AND ";
            $conditions .= "type != 'profile_photo'";
        }
        $this->output['media'] = $this->_getMedia($conditions);
    }

    /**
     * @api {post} /api/get-media-by-ids Get media by specific ids
     * @apiName GetMediaByIds
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id (optional).
     * @apiParam {String} auth_key User's auth key (optional).
     * @apiParam {String} ids Media's ids (ex. 3,7,8).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} media media details.
     */
    public function actionGetMediaByIds($ids)
    {
        $this->_addOutputs(['media']);

        $conditions['id'] = explode(',', $ids);
        $this->output['media'] = $this->_getMedia($conditions);
    }

    /**
     * @api {post} /api/comment Comment on review or media
     * @apiName Comment
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} text the comment.
     * @apiParam {String} object_id Object's id to comment about.
     * @apiParam {String} object_type Object's type to comment about (review, media or blog).
     * @apiParam {String} business_identity Business's id to link the comment (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {String} review_id the added review id
     */
    public function actionComment($text, $object_id, $object_type, $business_identity = null)
    {
        $this->_addOutputs(['comment_id']);

        if ($object_type === 'review') {
            $object = Review::findOne($object_id);
        } else if ($object_type === 'media') {
            $object = Media::findOne($object_id);
        } else if ($object_type === 'blog') {
            $object = Blog::findOne($object_id);
        } else {
            throw new HttpException(200, 'not supported type');
        }

        if (empty($object)) {
            throw new HttpException(200, 'no item with this id');
        }

        if (!empty($business_identity)) {
            $business = Business::findOne($business_identity);
            if (empty($business)) {
                throw new HttpException(200, 'no business with this id');
            }
            if ($business->admin_id !== $this->logged_user['id']) {
                throw new HttpException(200, 'you are not admin to this business');
            }
        }

        $comment = new Comment;
        $comment->user_id = $this->logged_user['id'];
        $comment->object_id = $object_id;
        $comment->object_type = $object_type;
        $comment->text = $text;
        $comment->business_identity = $business_identity;

        if (!$comment->save()) {
            throw new HttpException(200, $this->_getErrors($comment));
        }
        $this->output['comment_id'] = $comment->id;

        $commenter_name = $comment->user->name;
        if (!empty($business)) {
            $commenter_name = $business->name;
        }

        if ($object_type !== 'blog') {
            // send notification (if not the owner)
            if ($object->user_id != $this->logged_user['id']) {
                $type = 'comment';
                $title = '{new_comment_title}';
                $body = $commenter_name . ' {new_comment_body} ' . $object_type;
                $data = [
                    'type' => $type,
                    'payload' => [
                        'comment_id' => $comment->id,
                        'object_id' => $comment->object_id,
                        'object_type' => $comment->object_type,
                        'user_id' => $comment->user_id,
                    ]
                ];
                $this->_addNotification($object->user_id, $type, $title, $body, $data);
                $this->_sendNotification($object->user, $title, $body, $data);
            }

            // Send notification to admin
            $businessObject = $object_type === 'media'
            ? Business::findOne($object->object_id)
            : $object->business;
            if ($object->user_id != $businessObject->admin_id) {
                $type = 'comment';
                $title = '{new_comment_title}';
                $body = $commenter_name . ' {new_comment_body} ' . $object_type;
                $data = [
                    'type' => $type,
                    'payload' => [
                        'comment_id' => $comment->id,
                        'object_id' => $comment->object_id,
                        'object_type' => $comment->object_type,
                        'user_id' => $comment->user_id,
                    ]
                ];
                $this->_addNotification($businessObject->admin_id, $type, $title, $body, $data);
                $this->_sendNotification($businessObject->admin, $title, $body, $data);
            }
        }

        // send notifications
        if (preg_match_all('/(?<!\w)@(\w+)/', $comment->text, $matches)) {
            $users = $matches[1];
            foreach ($users as $id) {
                $user = User::findOne($id);
                if (empty($user)) {
                    continue;
                }

                $type = 'comment_tag';
                $title = '{new_comment_tag_title}';
                $body = $commenter_name . ' {new_comment_tag_body}';
                $data = [
                    'type' => $type,
                    'payload' => [
                        'comment_id' => $comment->id,
                        'object_id' => $comment->object_id,
                        'object_type' => $comment->object_type,
                        'user_id' => $comment->user_id,
                    ]
                ];
                $this->_addNotification($user->id, $type, $title, $body, $data);
                $this->_sendNotification($user, $title, $body, $data);
            }
        }
    }

    /**
     * @api {post} /api/edit-comment Edit Comment
     * @apiName EditComment
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} comment_id comment's id to edit.
     * @apiParam {String} text User's comment about the place (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionEditComment($comment_id, $text = null)
    {
        $comment = Comment::findOne($comment_id);

        if ($comment === null) {
            throw new HttpException(200, 'no comment with this id');
        }

        if ($comment->user_id != $this->logged_user['id']) {
            throw new HttpException(200, 'you are not allowed to edit this comment');
        }

        $old_text = $comment->text;
        preg_match_all('/(?<!\w)@(\w+)/', $comment->text, $old_matches);

        if (!empty($text)) {
            $comment->text = $text;
        }

        if (!$comment->save()) {
            throw new HttpException(200, $this->_getErrors($comment));
        }

        if (!empty($comment->business_identity)) {
            $business = Business::findOne($comment->business_identity);
            if (empty($business)) {
                throw new HttpException(200, 'no business with this id');
            }
            if ($business->admin_id !== $this->logged_user['id']) {
                throw new HttpException(200, 'you are not admin to this business');
            }
        }

        $commenter_name = $comment->user->name;
        if (!empty($business)) {
            $commenter_name = $business->name;
        }

        // send notifications
        if (preg_match_all('/(?<!\w)@(\w+)/', $comment->text, $matches)) {
            $users = $matches[1];
            foreach ($users as $id) {
                $user = User::findOne($id);
                if (empty($user) || in_array($id, $old_matches[1])) {
                    continue;
                }

                $type = 'comment_tag';
                $title = '{new_comment_tag_title}';
                $body = $commenter_name . ' {new_comment_tag_body}';
                $data = [
                    'type' => $type,
                    'payload' => [
                        'comment_id' => $comment->id,
                        'object_id' => $comment->object_id,
                        'object_type' => $comment->object_type,
                        'user_id' => $comment->user_id,
                    ]
                ];
                $this->_addNotification($user->id, $type, $title, $body, $data);
                $this->_sendNotification($user, $title, $body, $data);
            }
        }
    }

    /**
     * @api {post} /api/delete-comment Delete Comment
     * @apiName DeleteComment
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} comment_id comment's id to delete it.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionDeleteComment($comment_id)
    {
        $comment = Comment::findOne($comment_id);

        if ($comment === null) {
            throw new HttpException(200, 'no comment with this id');
        }

        if ($comment->user_id != $this->logged_user['id']) {
            throw new HttpException(200, 'you are not allowed to delete this comment');
        }

        if (!$comment->delete()) {
            throw new HttpException(200, $this->_getErrors($comment));
        }
    }

    /**
     * @api {post} /api/get-comments Get all comments for object
     * @apiName GetComments
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id (optional).
     * @apiParam {String} auth_key User's auth key (optional).
     * @apiParam {String} object_id Object's id to get comments related.
     * @apiParam {String} object_type Object's type to get comments related (review or media).
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} comments comments details.
     */
    public function actionGetComments($object_id, $object_type)
    {
        $this->_addOutputs(['comments']);

        if ($object_type !== 'review' && $object_type !== 'media' && $object_type !== 'blog') {
            throw new HttpException(200, 'not supported type');
        }

        $conditions = ['and'];
        $conditions[] = ['object_id' => $object_id];
        $conditions[] = ['object_type' => $object_type];
        $this->output['comments'] = $this->_getComments($conditions);
    }


    /**
     * @api {post} /api/add-reaction  Add reaction on review or media or comment
     * @apiName AddReaction
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} type Reaction type (like or dislike).
     * @apiParam {String} object_id Object's id to add reaction to.
     * @apiParam {String} object_type Object's type to add reaction to (review or media or comment).
     * @apiParam {String} business_identity Business's id to link the reaction (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {String} review_id the added review id
     */
    public function actionAddReaction($type, $object_id, $object_type, $business_identity = null)
    {
        $this->_addOutputs(['reaction_id']);

        $added_reaction = $this->_addedReaction($this->logged_user['id'], $object_id, $object_type);
        if (!empty($added_reaction)) {
            if ($added_reaction['type'] === $type) {
                throw new HttpException(200, 'already added reaction to this item before');
            } else {
                $this->actionDeleteReaction($added_reaction['id']);
            }
        }

        if ($object_type === 'review') {
            $object = Review::findOne($object_id);
        } else if ($object_type === 'media') {
            $object = Media::findOne($object_id);
        } else if ($object_type === 'comment') {
            $object = Comment::findOne($object_id);
        } else {
            throw new HttpException(200, 'not supported type');
        }

        if (empty($object)) {
            throw new HttpException(200, 'no item with this id');
        }

        if (!empty($business_identity)) {
            $business = Business::findOne($business_identity);
            if (empty($business)) {
                throw new HttpException(200, 'no business with this id');
            }
            if ($business->admin_id !== $this->logged_user['id']) {
                throw new HttpException(200, 'you are not admin to this business');
            }
        }

        $reaction = new Reaction;
        $reaction->user_id = $this->logged_user['id'];
        $reaction->object_id = $object_id;
        $reaction->object_type = $object_type;
        $reaction->type = $type;
        $reaction->business_identity = $business_identity;

        if (!$reaction->save()) {
            throw new HttpException(200, $this->_getErrors($reaction));
        }
        $this->output['reaction_id'] = $reaction->id;
    }

    /**
     * @api {post} /api/delete-reaction Delete Reaction
     * @apiName DeleteReaction
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} reaction_id reaction's id to delete it.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionDeleteReaction($reaction_id)
    {
        $reaction = Reaction::findOne($reaction_id);

        if ($reaction === null) {
            throw new HttpException(200, 'no reaction with this id');
        }

        if ($reaction->user_id != $this->logged_user['id']) {
            throw new HttpException(200, 'you are not allowed to delete this reaction');
        }

        if (!$reaction->delete()) {
            throw new HttpException(200, $this->_getErrors($reaction));
        }
    }

    /**
     * @api {post} /api/get-reactions Get all reactions for object
     * @apiName GetReactions
     * @apiGroup Business
     *
     * @apiParam {String} object_id Object's id to get reactions related.
     * @apiParam {String} object_type Object's type to get reactions related (review or media or comment).
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} reactions reactions details.
     */
    public function actionGetReactions($object_id, $object_type)
    {
        $this->_addOutputs(['reactions']);

        if ($object_type !== 'review' && $object_type !== 'media' && $object_type !== 'comment') {
            throw new HttpException(200, 'not supported type');
        }

        $conditions = ['and'];
        $conditions[] = ['object_id' => $object_id];
        $conditions[] = ['object_type' => $object_type];
        $this->output['reactions'] = $this->_getReactions($conditions);
    }

    /***************************************/
    /************ Reservations *************/
    /***************************************/

    /**
     * @api {post} /api/reserve-real-estate Reserve real estate
     * @apiName ReserveRealEstate
     * @apiGroup Reservations
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} business_id Business id
     * @apiParam {String} mobile User's mobile.
     * @apiParam {String} notes Reservation notes (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionReserveRealEstate($business_id, $mobile, $notes)
    {
        $user = User::findOne($this->logged_user['id']);
        if ($user === null) {
            throw new HttpException(200, 'no user with this id');
        }

        $business = Business::findOne($business_id);
        if (empty($business)) {
            throw new HttpException(200, 'no business with this id');
        }

        $business = $this->_getBusinessData($business);
        if ($business['top_category']['nameEn'] !== 'Real Estate') {
            throw new HttpException(200, 'only real estate businesses are allowed');
        }
        if (empty($business['email'])) {
            throw new HttpException(200, 'business has no email');
        }

        $reservation = new Reservation;
        $reservation->user_id = $user->id;
        $reservation->business_id = $business_id;
        $reservation->mobile = $mobile;
        $reservation->notes = $notes;
        if (!$reservation->save()) {
            throw new HttpException(200, $this->_getErrors($reservation));
        }

        $result1 = Yii::$app->mailer->compose()
            ->setFrom(['info@myblabber.com' => 'Blabber'])
            ->setTo($business['email'])
            ->setSubject('New Property Request')
            ->setTextBody(
                "Dear " . $business['name'] . ",\n\n"
                . "It's a new property request!\n\n"
                . "Contact details\n"
                . "Name: " . $user->name . "\n"
                . "Mobile: " . $mobile . "\n"
                . ($notes ? "Notes: " . $notes . "\n" : '')
                . "\nBest always,\n"
                . "Blabber"
            )
            ->send();
        $result2 = Yii::$app->mailer->compose()
            ->setFrom(['info@myblabber.com' => 'Blabber'])
            ->setTo('info@myblabber.com')
            ->setSubject('New Property Request')
            ->setTextBody(
                "Dear " . $business['name'] . ",\n\n"
                . "It's a new property request!\n\n"
                . "Contact details\n"
                . "Name: " . $user->name . "\n"
                . "Mobile: " . $mobile . "\n"
                . ($notes ? "Notes: " . $notes . "\n" : '')
                . "\nBest always,\n"
                . "Blabber"
            )
            ->send();
        if ($result1 === null) {
            throw new HttpException(200, 'Errors while sending email');
        }
    }

    /**
     * @api {post} /api/reserve-food Reserve food
     * @apiName ReserveFood
     * @apiGroup Reservations
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} business_id Business id
     * @apiParam {String} mobile User's mobile.
     * @apiParam {Integer} guests Number of guests.
     * @apiParam {String} date Reservation date.
     * @apiParam {String} time Reservation time.
     * @apiParam {String} notes Reservation notes (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionReserveFood($business_id, $mobile, $guests, $date, $time, $notes = '')
    {
        $user = User::findOne($this->logged_user['id']);
        if ($user === null) {
            throw new HttpException(200, 'no user with this id');
        }

        $business = Business::findOne($business_id);
        if (empty($business)) {
            throw new HttpException(200, 'no business with this id');
        }

        $business = $this->_getBusinessData($business);
        if ($business['top_category']['nameEn'] !== 'Food') {
            throw new HttpException(200, 'only food businesses are allowed');
        }
        if (empty($business['email'])) {
            throw new HttpException(200, 'business has no email');
        }

        $reservation = new Reservation;
        $reservation->user_id = $user->id;
        $reservation->business_id = $business_id;
        $reservation->mobile = $mobile;
        $reservation->guests = $guests;
        $reservation->date = $date;
        $reservation->time = $time;
        $reservation->notes = $notes;
        $reservation->status = 'requested';
        if (!$reservation->save()) {
            throw new HttpException(200, $this->_getErrors($reservation));
        }

        $result = Yii::$app->mailer->compose()
            ->setFrom(['info@myblabber.com' => 'Blabber'])
            ->setTo($business['email'])
            ->setSubject('New Table Reservation')
            ->setTextBody(
                "Dear " . $business['name'] . ",\n\n"
                . "It's a new table reservation!\n\n"
                . "Contact details\n"
                . "Name: " . $user->name . "\n"
                . "Mobile: " . $mobile . "\n"
                . "Guests: " . $guests . "\n"
                . "Date: " . $date . "\n"
                . "Time: " . $time . "\n"
                . ($notes ? "Notes: " . $notes . "\n" : '')
                . "\nBest always,\n"
                . "Blabber"
            )
            ->send();
        if ($result === null) {
            throw new HttpException(200, 'Errors while sending email');
        }
    }

    /***************************************/
    /************** Sponsors ***************/
    /***************************************/

    /**
     * @api {post} /api/get-sponsors Get all sponsors
     * @apiName GetSponsors
     * @apiGroup Sponsors
     *
     * @apiParam {String} page Page number (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} sponsors List of Sponsors.
     */
    public function actionGetSponsors()
    {
        $this->_addOutputs(['sponsors']);

        $query = Sponsor::find();
        $model = $this->_getModelWithPagination($query);

        $sponsors = [];
        foreach ($model as $key => $sponsor) {
            $temp['id'] = $sponsor['id'];
            $temp['name'] = $sponsor['name'.$this->lang];
            $temp['description'] = $sponsor['description'.$this->lang];
            $temp['main_image'] = Url::base(true) . '/' . $sponsor['main_image'];
            $temp['link'] = $sponsor['link'];
            $sponsors[] = $temp;
        }

        $this->output['sponsors'] = $sponsors;
    }

    /***************************************/
    /************ Notifications ************/
    /***************************************/

    /**
     * @api {post} /api/get-unseen-notification-count Get all user unseen notifications count
     * @apiName GetUnseenNotificationCount
     * @apiGroup Notifications
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Integer} notification_count unseen notifications count.
     */
    public function actionGetUnseenNotificationCount()
    {
        $this->_addOutputs(['notification_count']);

        $notification_count = Notification::find()
            ->where(['user_id' => $this->logged_user['id'], 'seen' => 0])
            ->count();

        $this->output['notification_count'] = (int) $notification_count;
    }

    /**
     * @api {post} /api/get-notifications Get all user notifications
     * @apiName GetNotifications
     * @apiGroup Notifications
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} format Output format (group or list)
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} notifications List of Notifications.
     */
    public function actionGetNotifications($format = 'group')
    {
        $this->_addOutputs(['notifications']);

        $notifications = [];

        $query = Notification::find()
            ->where(['user_id' => $this->logged_user['id']])
            ->orderBy(['id' => SORT_DESC]);
        $notifications_model = $this->_getModelWithPagination($query);

        foreach ($notifications_model as $key => $notification) {
            $temp['notification_id'] = $notification['id'];
            // $temp['title'] = Translation::get($this->lang, $notification['title']);
            // $temp['body'] = Translation::get($this->lang, $notification['body']);
            $temp['data'] = json_decode($notification['data']);
            if(!empty($temp['data']->payload->user_id)) {
                $temp->payload->user_data = $this->_getUserMinimalData(User::findOne($temp['data']->payload->user_id));
            }
            if(!empty($temp['data']->payload->business_id)) {
                $temp['data']->payload->business_data = $this->_getBusinessMinimalData(Business::findOne($temp['data']->payload->business_id));
            }
            $temp['seen'] = $notification['seen'];
            $temp['created'] = $notification['created'];

            $notifications[] = $temp;
        }

        $this->output['notifications'] = $notifications;
    }

    /***************************************/
    /************** Reporting **************/
    /***************************************/

    /**
     * @api {post} /api/report Report improper content
     * @apiName Report
     * @apiGroup Reporting
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} object_id Object's id to be reported.
     * @apiParam {String} object_type Object's type to be reported (review, comment, business, image).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionReport($object_id, $object_type)
    {
        $report = new Report;
        $report->user_id = $this->logged_user['id'];
        $report->object_id = $object_id;
        $report->object_type = $object_type;

        if (!$report->save()) {
            throw new HttpException(200, $this->_getErrors($report));
        }
    }

    /**
     * @api {post} /api/get-blogs Get blogs
     * @apiName GetBlogs
     * @apiGroup Blog
     *
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} blogs Blog posts.
     */
    public function actionGetBlogs()
    {
        $this->_addOutputs(['blogs']);
        $model = $this->_getBlogs();
        $this->output['blogs'] = $model;
    }

    /**
     * @api {post} /api/get-blog Get single blog
     * @apiName GetBlog
     * @apiGroup Blog
     *
     * @apiParam {String} blog_id Blog id.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Object} blog Blog data.
     */
    public function actionGetBlog($blog_id)
    {
        $this->_addOutputs(['blog']);

        $blog = Blog::findOne($blog_id);
        if (empty($blog)) {
            throw new HttpException(200, 'no blog with this id');
        }

        $this->output['blog'] = $blog->attributes;
    }

    /**
     * @api {post} /api/get-polls Get all polls for business
     * @apiName GetPolls
     * @apiGroup Poll
     *
     * @apiParam {String} user_id User's id (optional).
     * @apiParam {String} auth_key User's auth key (optional).
     * @apiParam {String} business_id Business's id.
     * @apiParam {String} page Page number (optional).
     * @apiParam {String} lang Text language ('En' for English (default), 'Ar' for arabic) (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} polls List of polls.
     */
    public function actionGetPolls($business_id)
    {
        $this->_addOutputs(['polls']);
        $conditions = ['business_id' => $business_id];
        $this->output['polls'] = $this->_getPolls($conditions);
    }

    /**
     * @api {post} /api/add-vote Add vote
     * @apiName AddVote
     * @apiGroup Poll
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} option_id Option's id.
     * @apiParam {String} business_id Business's id.
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {String} vote_id Vote id.
     */
     public function actionAddVote($option_id, $business_id)
     {
        $option = Option::findOne(['id' => $option_id]);
        if (empty($option)) {
            throw new HttpException(200, 'Option not found');
        }

        $this->_addOutputs(['vote_id']);

        $model = Vote::find()
            ->joinWith('option')
            ->andWhere(['user_id' => $this->logged_user['id'], 'poll_id' => $option->poll_id])
            ->one();

        if (empty($model)) {
            $model = new Vote;
            $model->user_id = $this->logged_user['id'];
        }

        $model->option_id = $option_id;
        $model->business_id = $business_id;
        if (!$model->save()) {
            throw new HttpException(200, $this->_getErrors($model));
        }

        $this->output['vote_id'] = $model->id;
     }

    /**
     * @api {post} /api/delete-vote Delete vote
     * @apiName DeleteVote
     * @apiGroup Poll
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} vote_id Vote's id.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
     public function actionDeleteVote($vote_id)
     {
        $vote = Vote::findOne(['id' => $vote_id]);
        if (empty($vote)) {
            throw new HttpException(200, 'Vote not found');
        }
        if ($vote->user_id !== $this->logged_user['id']) {
            throw new HttpException(200, 'You are not allowed to delete this vote');
        }
        if (!$model->delete()) {
            throw new HttpException(200, $this->_getErrors($vote));
        }
     }
}
