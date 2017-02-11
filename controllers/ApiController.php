<?php

namespace app\controllers;

use Yii;
use yii\db\Query;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\HttpException;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\User;
use app\models\Friendship;
use app\models\Category;
use app\models\Business;
use app\models\Country;
use app\models\City;
use app\models\Flag;
use app\models\Interest;
use app\models\BusinessFlag;
use app\models\BusinessInterest;
use app\models\UserFlag;
use app\models\UserInterest;
use app\models\SavedBusiness;
use app\models\Checkin;
use app\models\Review;
use app\models\Media;
use app\models\BusinessView;
use app\models\Sponsor;
use yii\data\ActiveDataProvider;

class ApiController extends ApiBaseController
{
    /***************************************/
    /**************** Users ****************/
    /***************************************/

    /**
     * @api {post} /api/is-unique-username Check if username is unique
     * @apiName IsUniqueUsername
     * @apiGroup User
     *
     * @apiParam {String} username Username to check if uniqe.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionIsUniqueUsername($username)
    {
        $model = User::find()
                ->where(['username' => $username])
                ->one();

        if (!empty($model)) {
            throw new HttpException(200, 'this username already taken');
        }
    }

    /**
     * @api {post} /api/sign-up Sign up new user
     * @apiName SignUp
     * @apiGroup User
     *
     * @apiParam {String} name User's full name.
     * @apiParam {String} email User's unique email.
     * @apiParam {String} username User's unique username.
     * @apiParam {String} password User's password.
     * @apiParam {String} mobile User's unique mobile number (optional).
     * @apiParam {String} image User's new image url (optional).
     * @apiParam {File} Media[file] User's new image file (optional).
     * @apiParam {String} firebase_token User's firebase token (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} user_data user details.
     * @apiSuccess {String} auth_key user auth key to use for other api calls.
     */
    public function actionSignUp($name, $email, $username, $password, $mobile = null, $image = null, $firebase_token = null)
    {
        $this->_addOutputs(['user_data', 'auth_key']);

        // sign up
        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->username = $username;
        $user->password = Yii::$app->security->generatePasswordHash($password);
        if( !empty($mobile) ){
            $user->mobile = $mobile;
        }

        if(!$user->save()){
            throw new HttpException(200, $this->_getErrors($user));
        }

        // save url if image coming from external source like Facebook
        if( !empty($image) ){
            $user->profile_photo = $image;
            if(!$user->save()){
                throw new HttpException(200, $this->_getErrors($user));
            }

        // upload image then save it 
        }else if( !empty($_FILES['Media']) ){
            $this->_uploadPhoto($user->id, 'User', 'profile_photo', $user, 'profile_photo', $user->id);
        }

        $this->_login($email, $password, $firebase_token);       
    }

    /**
     * @api {post} /api/sign-in-fb Sign in using facebook
     * @apiName SignInFb
     * @apiGroup User
     *
     * @apiParam {String} facebook_id User's facebook id.
     * @apiParam {String} facebook_token User's facebook token.
     * @apiParam {String} name User's full name.
     * @apiParam {String} image User's new image url (optional).
     * @apiParam {String} firebase_token User's firebase token (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} user_data user details.
     * @apiSuccess {String} auth_key user auth key to use for other api calls.
     */
    public function actionSignInFb($facebook_id, $facebook_token, $name, $image = null, $firebase_token = null)
    {
        $this->_addOutputs(['user_data', 'auth_key']);

        // verify facebook token & facebook id
        $user_details = "https://graph.facebook.com/me?access_token=" .$facebook_token;
        $response = file_get_contents($user_details);
        $response = json_decode($response);
        if( !isset($response) || !isset($response->id)|| $response->id != $facebook_id ){
            throw new HttpException(200, 'invalid facebook token');
        }

        // check if user not saved before, to add it
        $email = $facebook_id.'@facebook.com';
        $password = md5($facebook_id);
        $user = User::findByEmail($email);
        if( $user == null ){
            // sign up
            $user = new User;
            $user->name = $name;
            $user->email = $email;
            $user->password = Yii::$app->security->generatePasswordHash($password);
            $user->facebook_id = $facebook_id;

            if(!$user->save()){
                throw new HttpException(200, $this->_getErrors($user));
            }

            // save url if image coming from external source like Facebook
            if( !empty($image) ){
                $user->profile_photo = $image;
                if(!$user->save()){
                    throw new HttpException(200, $this->_getErrors($user));
                }
            }
        }

        $this->_login($email, $password, $firebase_token);            
    }

    /**
     * @api {post} /api/sign-in Sign in existing user
     * @apiName SignIn
     * @apiGroup User
     *
     * @apiParam {String} email User's unique email.
     * @apiParam {String} password User's password.
     * @apiParam {String} firebase_token User's firebase token (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} user_data user details.
     * @apiSuccess {String} auth_key user auth key to use for other api calls.
     */
    public function actionSignIn($email, $password, $firebase_token = null)
    {
        $this->_addOutputs(['user_data', 'auth_key']);
        $this->_login($email, $password, $firebase_token);       
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
        if( $user !== null ){
            $user->password = Yii::$app->security->generatePasswordHash($new_password);
            if($user->save()){
                $result = Yii::$app->mailer->compose()
                    ->setFrom('recovery@blabber.com', 'Blabber support')
                    ->setTo($email)
                    ->setSubject('Blabber Password Recovery')
                    ->setTextBody('your password changed to: '.$new_password)
                    ->send();
                if (!$result) {
                    throw new HttpException(200, 'Password changed but errors while sending email: '.$mail->getError());
                }
            }else{
                throw new HttpException(200, $this->_getErrors($user));
            }
        }else{
            throw new HttpException(200, 'no user with this email');
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
        $model = User::findOne($this->logged_user_id);
        $model->password = Yii::$app->security->generatePasswordHash($new_password);
        if(!$model->save()){
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
     */
    public function actionChangeProfilePhoto($image = null)
    {
        $user = User::findOne($this->logged_user_id);

        // save url if image coming from external source like Facebook
        if( !empty($image) ){
            $user->profile_photo = $image;
            if(!$user->save()){
                throw new HttpException(200, $this->_getErrors($user));
            }

        // upload image then save it 
        }else if( !empty($_FILES['Media']) ){
            $this->_uploadPhoto($user->id, 'User', 'profile_photo', $user, 'profile_photo');
        }else{
            throw new HttpException(200, 'no url or file input');
        }  
    }

    /**
     * @api {post} /api/logout Logout for logged in user
     * @apiName Logout
     * @apiGroup User
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionLogout()
    {
        $user = User::findOne($this->logged_user_id);
        $user->auth_key = "";
        if( !$user->save() ){
            throw new HttpException(200, 'logout problem');
        }
    }

    /**
     * @api {post} /api/get-profile Get user profile
     * @apiName GetProfile
     * @apiGroup User
     *
     * @apiParam {String} user_id_to_get User's id of User profile you want to get (optional).
     * @apiParam {String} user_username_to_get User's usernam of User profile you want to get (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} user_data user details.
     */
    public function actionGetProfile($user_id_to_get = null, $user_username_to_get = null)
    {
        $this->_addOutputs(['user_data']);

        if (!empty($user_id_to_get)) {
            $user = User::findOne($user_id_to_get);
        }elseif (!empty($user_username_to_get)) {
            $user = User::findOne(['username' => $user_username_to_get]);
        }
        
        if( $user !== null ){
            $this->output['user_data'] = $this->_getUserData($user);
        }else{
            throw new HttpException(200, 'no user with this id or username');
        }
    }

    /**
     * @api {post} /api/edit-profile Edit user profile
     * @apiName EditProfile
     * @apiGroup User
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} name user name (optional).
     * @apiParam {String} username user username (optional).
     * @apiParam {String} mobile user mobile (optional).
     * @apiParam {String} gender user gender (optional).
     * @apiParam {String} birthdate user birthdate (optional).
     * @apiParam {String} firebase_token user firebase_token (optional).
     * @apiParam {Array} interests_ids array of interests ids to add to user, ex. 2,5,7 (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionEditProfile($name = null, $username = null, $mobile = null, $gender = null, $birthdate = null, $firebase_token = null, $interests_ids = null)
    {
        $user = User::findOne($this->logged_user_id);
        if( $user == null ){
            throw new HttpException(200, 'no user with this id');
        }

        if ( !empty($name) ) $user->name = $name;
        if ( !empty($username) ) $user->username = $username;
        if ( !empty($mobile) ) $user->mobile = $mobile;
        if ( !empty($gender) ) $user->gender = $gender;
        if ( !empty($birthdate) ) $user->birthdate = $birthdate;
        if ( !empty($firebase_token) ) $user->firebase_token = $firebase_token;

        if(!$user->save()){
            throw new HttpException(200, $this->_getErrors($user));
        }

        if( !empty($interests_ids) ){
            // remove old interests
            UserInterest::deleteAll('user_id = '.$user->id);

            $interests = explode(',', $interests_ids);
            foreach ($interests as $interest) {
                $user_interest = new UserInterest();
                $user_interest->user_id = $user->id;
                $user_interest->interest_id = $interest;
                $user_interest->save();
            }
        }
    }

    /***************************************/
    /************* Friendship **************/
    /***************************************/

    /**
     * @api {post} /api/search-for-user Search for user by name
     * @apiName SearchForUser
     * @apiGroup Friendship
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
                ->where(['like', 'name', $name])
                ->andWhere(['!=', 'id', $this->logged_user_id])
                ->orderBy(['id' => SORT_DESC]);
        $model = $this->_getModelWithPagination($query);

        $users = array();
        foreach ($model as $key => $user) {
            $users[] = $this->_getUserData($user);
        }

        $this->output['users'] = $users;
    }

    /**
     * @api {post} /api/add-friend Add friend by sending friend request
     * @apiName AddFriend
     * @apiGroup Friendship
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} friend_id User's id of the friend you want to add.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {String} request_id the added request id
     */
    public function actionAddFriend($friend_id)
    {
        $this->_addOutputs(['request']);

        $friendship = $this->_getLastFriendshipRequest($this->logged_user_id, $friend_id);

        //if there isn't friendship request or if sent old one and rejected (status:2) or cancelled (status:3) or removed (status:4)
        if ( $friendship == null || $friendship->status == 2 || $friendship->status == 3 || $friendship->status == 4 ){ 
            $model = new Friendship;
            $model->user_id = $this->logged_user_id;
            $model->friend_id = $friend_id;
            $model->status = 0;

            if($model->save()){
                $this->output['request'] = $model->attributes;

                // send notification
                $title = 'New Friend Request';
                $body = $model->user->name .' wants to add you as a friend';
                $data = [
                    'request_id' => $model->id,
                    'friend_id' => $model->user_id,
                    'type' => 1,
                ];
                $this->_sendNotification($model->friend->firebase_token, $title, $body, $data);
            }else{
                throw new HttpException(200, $this->_getErrors($model));
            }
        }else{
            throw new HttpException(200, 'you can\'t send new friend request');
        }
    }

    /**
     * @api {post} /api/get-friend-requests-sent Get all the friend requests user sent
     * @apiName GetFriendRequestsSent
     * @apiGroup Friendship
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} requests requests details.
     */
    public function actionGetFriendRequestsSent()
    {
        $this->_addOutputs(['requests']);

        $query = Friendship::find()
            ->where(['user_id' => $this->logged_user_id, 'status' => 0]);
        $model = $this->_getModelWithPagination($query);

        $requests = array();
        foreach ($model as $key => $request) {
            $requests[] = array('id' => $request->id, 'friend_data' => $this->_getUserData($request->friend));
        }

        $this->output['requests'] = $requests;
    }

    /**
     * @api {post} /api/cancel-friend-request Cancel friend request you sent before
     * @apiName CancelFriendRequest
     * @apiGroup Friendship
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} request_id the id of the friend request.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionCancelFriendRequest($request_id)
    {
        $model = Friendship::findOne($request_id);
        $model->status = 3;
        if(!$model->save()){
            throw new HttpException(200, $this->_getErrors($model));
        }
    }

    /**
     * @api {post} /api/get-friend-requests-received Get all the friend requests user received
     * @apiName GetFriendRequestsReceived
     * @apiGroup Friendship
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} requests requests details.
     */
    public function actionGetFriendRequestsReceived()
    {
        $this->_addOutputs(['requests']);

        $query = Friendship::find()
            ->where(['friend_id' => $this->logged_user_id, 'status' => 0]);
        $model = $this->_getModelWithPagination($query);

        $requests = array();
        foreach ($model as $key => $request) {
            $requests[] = array('id' => $request->id, 'user_data' => $this->_getUserData($request->user));
        }

        $this->output['requests'] = $requests;
    }

    /**
     * @api {post} /api/accept-friend-request Accept friend request
     * @apiName AcceptFriendRequest
     * @apiGroup Friendship
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} request_id the id of the friend request.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionAcceptFriendRequest($request_id)
    {
        // accept request
        $request = Friendship::findOne($request_id);
        $request->status = 1;
        if( !$request->save() ){
            throw new HttpException(200, $this->_getErrors($request));
        }

        // add as a friend in the other user list
        $friendship_model = new Friendship;
        $friendship_model->user_id = $request->friend_id;
        $friendship_model->friend_id = $request->user_id;
        $friendship_model->status = 1;
        if( !$friendship_model->save() ){
            throw new HttpException(200, $this->_getErrors($friendship_model));
        }

        // send notification
        $title = 'Friend Request Accepted';
        $body = $request->friend->name .' accepted your friend request';
        $data = [
            'request_id' => $request->id,
            'friend_id' => $request->friend_id,
            'type' => 2,
        ];
        $this->_sendNotification($request->user->firebase_token, $title, $body, $data);
    }

    /**
     * @api {post} /api/reject-friend-request Reject friend request
     * @apiName RejectFriendRequest
     * @apiGroup Friendship
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} request_id the id of the friend request.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionRejectFriendRequest($request_id)
    {
        $model = Friendship::findOne($request_id);
        $model->status = 2;
        if(!$model->save()){
            throw new HttpException(200, $this->_getErrors($model));
        }
    }

    /**
     * @api {post} /api/remove-friend Remove friend from friend list
     * @apiName RemoveFriend
     * @apiGroup Friendship
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} friend_id User's id of the friend you want to remove.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionRemoveFriend($friend_id)
    {
        $friendship1 = Friendship::find()
            ->where(['friend_id' => $this->logged_user_id, 'user_id' => $friend_id, 'status' => 1])
            ->one();
        $friendship2 = Friendship::find()
            ->where(['friend_id' => $friend_id, 'user_id' => $this->logged_user_id, 'status' => 1])
            ->one();

        if( isset($friendship1) && isset($friendship2) ){
            $friendship1->status = 4;
            $friendship2->status = 4;

            if( !$friendship1->save() || !$friendship2->save() ){
                throw new HttpException(200, $this->_getErrors($friendship1) + $this->_getErrors($friendship2));
            }
        }else{
            throw new HttpException(200, 'problem occured');
        }
    }

    /**
     * @api {post} /api/get-friends Get all the user friends
     * @apiName GetFriends
     * @apiGroup Friendship
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} friends friends details.
     */
    public function actionGetFriends()
    {
        $this->_addOutputs(['friends']);

        $query = Friendship::find()
            ->where(['user_id' => $this->logged_user_id, 'status' => 1]);
        $model = $this->_getModelWithPagination($query);

        $friends = array();
        foreach ($model as $key => $friendship) {
            $friends[] = $this->_getUserData($friendship->friend);
        }

        $this->output['friends'] = $friends;
    }

    /***************************************/
    /************* Categories **************/
    /***************************************/

    /**
     * @api {post} /api/get-categories Get the main categories
     * @apiName GetCategories
     * @apiGroup Category
     *
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} categories categories details.
     */
    public function actionGetCategories()
    {
        $this->_addOutputs(['categories']);

        $this->output['categories'] = $this->_getCategories();
    }

    /**
     * @api {post} /api/get-sub-categories Get the sub categories of one category
     * @apiName GetSubCategories
     * @apiGroup Category
     *
     * @apiParam {String} category_id parent category id.
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} categories categories details.
     */
    public function actionGetSubCategories($category_id)
    {
        $this->_addOutputs(['categories']);

        $this->output['categories'] = $this->_getCategories($category_id);
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
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} Countries List of Countries.
     */
    public function actionGetCountries()
    {
        $this->_addOutputs(['countries']);

        $query = Country::find();
        $model = $this->_getModelWithPagination($query);

        $countries = [];
        foreach ($model as $key => $country) {
            $temp['id'] = $country['id'];
            $temp['name'] = $country['name'];
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
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} Cities List of Cities.
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
            $temp['name'] = $city['name'];
            $cities[] = $temp;
        }

        $this->output['cities'] = $cities;
    }

    /**
     * @api {post} /api/get-flags Get all flags
     * @apiName GetFlags
     * @apiGroup Business
     *
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} Flags List of Flags.
     */
    public function actionGetFlags()
    {
        $this->_addOutputs(['flags']);

        $query = Flag::find();
        $model = $this->_getModelWithPagination($query);

        $flags = [];
        foreach ($model as $key => $flag) {
            $temp['id'] = $flag['id'];
            $temp['name'] = $flag['name'];
            $temp['icon'] = Url::base(true).'/'.$flag['icon'];
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
     * @apiParam {File} Media[file] Flag's icon file (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionAddFlag($name)
    {
        $flag = new Flag;
        $flag->name = $name;

        if(!$flag->save()){
            throw new HttpException(200, $this->_getErrors($flag));
        }

        if( !empty($_FILES['Media']) ){
            $this->_uploadPhoto($flag->id, 'Flag', 'flag_icon', $flag, 'icon');
        }
    }

    /**
     * @api {post} /api/get-interests Get all interests
     * @apiName GetInterests
     * @apiGroup Business
     *
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} Interests List of Interests.
     */
    public function actionGetInterests()
    {
        $this->_addOutputs(['interests']);

        $query = Interest::find();
        $model = $this->_getModelWithPagination($query);

        $interests = [];
        foreach ($model as $key => $interest) {
            $temp['id'] = $interest['id'];
            $temp['name'] = $interest['name'];
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
     * @apiParam {String} address business address.
     * @apiParam {String} country_id business country id.
     * @apiParam {String} city_id business city id.
     * @apiParam {String} phone business phone.
     * @apiParam {String} open_from business openning hour.
     * @apiParam {String} open_to business closing hour.
     * @apiParam {String} lat business latitude .
     * @apiParam {String} lng business longitude .
     * @apiParam {String} price average business price.
     * @apiParam {String} description business description.
     * @apiParam {String} category_id Category's id to add business inside.
     * @apiParam {String} website business website. (optional)
     * @apiParam {String} fb_page business Facebook page. (optional)
     * @apiParam {Array} flags_ids array of flags ids to add to business, ex. 10,13,5 (optional).
     * @apiParam {Array} interests array of interests strings to add to business, ex. interest1,interest2,interest3 (optional).
     * @apiParam {File} Media[file] Business's main image file (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} businesses businesses details.
     */
    public function actionAddBusiness($name, $address, $country_id, $city_id, $phone, $open_from, $open_to, $lat, $lng, $price, $description, $category_id, $website = null, $fb_page = null, $flags_ids = null, $interests = null)
    {
        $business = new Business;
        $business->name = $name;
        $business->address = $address;
        $business->country_id = $country_id;
        $business->city_id = $city_id;
        $business->phone = $phone;
        $business->open_from = $open_from;
        $business->open_to = $open_to;
        $business->lat = $lat;
        $business->lng = $lng;
        $business->price = $price;
        $business->description = $description;
        $business->category_id = $category_id;
        $business->admin_id = $this->logged_user_id;

        if ( !empty($website) ) {
            $business->website = $website;
        }
        if ( !empty($fb_page) ) {
            $business->fb_page = $fb_page;
        }

        if(!$business->save()){
            throw new HttpException(200, $this->_getErrors($business));
        }

        if( !empty($flags_ids) ){
            $flags = explode(',', $flags_ids);
            foreach ($flags as $flag) {
                $business_flag = new BusinessFlag();
                $business_flag->business_id = $business->id;
                $business_flag->flag_id = $flag;
                $business_flag->save();
            }
        }

        if( !empty($interests) ){
            $interests = explode(',', $interests);
            foreach ($interests as $interest) {
                $temp_interest = Interest::find()->where('name = :name', [':name' => $interest])->one();
                if( empty($temp_interest) ){
                    $temp_interest = new Interest();
                    $temp_interest->name = $interest;
                    $temp_interest->save();
                }

                $business_interest = new BusinessInterest();
                $business_interest->business_id = $business->id;
                $business_interest->interest_id = $temp_interest->id;
                $business_interest->save();
            }
        }

        if( !empty($_FILES['Media']) ){
            $this->_uploadPhoto($business->id, 'Business', 'business_image', $business, 'main_image');
        }
    }

    /**
     * @api {post} /api/edit-business Edit business details
     * @apiName EditBusiness
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} business_id business id.
     * @apiParam {String} name business name (optional).
     * @apiParam {String} address business address (optional).
     * @apiParam {String} country_id business country id (optional).
     * @apiParam {String} city_id business city id (optional).
     * @apiParam {String} phone business phone (optional).
     * @apiParam {String} open_from business openning hour (optional).
     * @apiParam {String} open_to business closing hour (optional).
     * @apiParam {String} lat business latitude  (optional).
     * @apiParam {String} lng business longitude  (optional).
     * @apiParam {String} price average business price (optional).
     * @apiParam {String} description business description (optional).
     * @apiParam {String} category_id Category's id to add business inside (optional).
     * @apiParam {String} website business website. (optional)
     * @apiParam {String} fb_page business Facebook page. (optional)
     * @apiParam {Array} flags_ids array of flags ids to add to business, ex. 10,13,5 (optional).
     * @apiParam {Array} interests array of interests strings to add to business, ex. interest1,interest2,interest3 (optional).
     * @apiParam {File} Media[file] Business's main image file (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} businesses businesses details.
     */
    public function actionEditBusiness($business_id, $name = null, $address = null, $country_id = null, $city_id = null, $phone = null, $open_from = null, $open_to = null, $lat = null, $lng = null, $price = null, $description = null, $category_id = null, $website = null, $fb_page = null, $flags_ids = null, $interests = null)
    {
        $business = Business::find()
                        ->where(['id' => $business_id])
                        ->one();
        if( $business == null ){
            throw new HttpException(200, 'no business with this id');
        }
        if( $business->admin_id != $this->logged_user_id ){
            throw new HttpException(200, 'you don\'t have permission to edit this business');
        }

        if ( !empty($name) ) $business->name = $name;
        if ( !empty($address) ) $business->address = $address;
        if ( !empty($country_id) ) $business->country_id = $country_id;
        if ( !empty($city_id) ) $business->city_id = $city_id;
        if ( !empty($phone) ) $business->phone = $phone;
        if ( !empty($open_from) ) $business->open_from = $open_from;
        if ( !empty($open_to) ) $business->open_to = $open_to;
        if ( !empty($lat) ) $business->lat = $lat;
        if ( !empty($lng) ) $business->lng = $lng;
        if ( !empty($price) ) $business->price = $price;
        if ( !empty($description) ) $business->description = $description;
        if ( !empty($category_id) ) $business->category_id = $category_id;
        if ( !empty($website) ) $business->website = $website;
        if ( !empty($fb_page) ) $business->fb_page = $fb_page;

        if(!$business->save()){
            throw new HttpException(200, $this->_getErrors($business));
        }

        if( !empty($flags_ids) ){
            // remove old flags
            BusinessFlag::deleteAll('business_id = '.$business->id);

            $flags = explode(',', $flags_ids);
            foreach ($flags as $flag) {
                $business_flag = new BusinessFlag();
                $business_flag->business_id = $business->id;
                $business_flag->flag_id = $flag;
                $business_flag->save();
            }
        }

        if( !empty($interests) ){
            // remove old interests
            BusinessInterest::deleteAll('business_id = '.$business->id);

            $interests = explode(',', $interests);
            foreach ($interests as $interest) {
                $temp_interest = Interest::find()->where('name = :name', [':name' => $interest])->one();
                if( empty($temp_interest) ){
                    $temp_interest = new Interest();
                    $temp_interest->name = $interest;
                    $temp_interest->save();
                }

                $business_interest = new BusinessInterest();
                $business_interest->business_id = $business->id;
                $business_interest->interest_id = $temp_interest->id;
                $business_interest->save();
            }
        }

        if( !empty($_FILES['Media']) ){
            $this->_uploadPhoto($business->id, 'Business', 'business_image', $business, 'main_image');
        }
    }

    /**
     * @api {post} /api/get-homescreen-businesses Get businesses for homescreen
     * @apiName GetHomescreenBusinesses
     * @apiGroup Business
     *
     * @apiParam {String} country_id Country's id.
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} businesses businesses details.
     */
    public function actionGetHomescreenBusinesses($country_id)
    {
        $this->_addOutputs(['businesses']);

        $conditions['show_in_home'] = true;
        $this->output['businesses'] = $this->_getBusinesses($conditions, $country_id);
    }

    /**
     * @api {post} /api/get-businesses Get businesses from category
     * @apiName GetBusinesses
     * @apiGroup Business
     *
     * @apiParam {String} country_id Country's id.
     * @apiParam {String} category_id Category's id to get businesses inside.
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} businesses businesses details.
     */
    public function actionGetBusinesses($country_id, $category_id)
    {
        $this->_addOutputs(['businesses']);

        $conditions['category_id'] = $category_id;
        $this->output['businesses'] = $this->_getBusinesses($conditions, $country_id);
    }

    /**
     * @api {post} /api/search-businesses Get businesses by search
     * @apiName SearchBusinesses
     * @apiGroup Business
     *
     * @apiParam {String} country_id Country's id.
     * @apiParam {String} name the search keyword for business name (optional).
     * @apiParam {String} city the search keyword for business city (optional).
     * @apiParam {String} city_id the business city_id (optional).
     * @apiParam {String} category the search keyword for business category (optional).
     * @apiParam {String} category_id the business category_id (optional).
     * @apiParam {String} flag the search keyword for business flag (optional).
     * @apiParam {String} flag_id the business flag_id (optional).
     * @apiParam {String} interest the search keyword for business interest (optional).
     * @apiParam {String} interest_id the business interest_id (optional).
     * @apiParam {String} nearby the search coordinates for nearby business, value lat-lng, ex. 32.22-37.11 (optional).
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} businesses businesses details.
     */
    public function actionSearchBusinesses($country_id, $name = null, $city = null, $city_id  = null, $category = null, $category_id = null, $flag = null, $flag_id = null, $interest = null, $interest_id = null, $nearby = null)
    {
        $this->_addOutputs(['businesses']);

        $conditions[] = 'or';
        $and_conditions[] = 'and';
        
        if( !empty($name) ){
            $conditions[] = ['like', 'name', $name];
        }
        if( !empty($city) ){
            $model = City::find()->where(['like', 'name', $city])->all();
            $search_keyword = ArrayHelper::getColumn($model, 'id');
            $conditions[] = ['city_id' => $search_keyword];
        }
        if( !empty($city_id) ){
            $conditions[] = ['city_id' => $city_id];
        }
        if( !empty($category) ){
            $model = Category::find()->where(['like', 'name', $category])->all();
            $search_keyword = ArrayHelper::getColumn($model, 'id');
            $conditions[] = ['category_id' => $search_keyword];
        }
        if( !empty($category_id) ){
            $conditions[] = ['category_id' => $category_id];
        }
        if( !empty($flag) ){
            $model = Flag::find()->where(['like', 'name', $flag])->all();
            $search_keyword = ArrayHelper::getColumn($model, 'id');
            $model = BusinessFlag::find()->where(['flag_id' => $search_keyword])->all();
            $ids = ArrayHelper::getColumn($model, 'business_id');
            $conditions[] = ['id' => $ids];
        }
        if( !empty($flag_id) ){
            $model = BusinessFlag::find()->where(['flag_id' => $flag_id])->all();
            $ids = ArrayHelper::getColumn($model, 'business_id');
            $conditions[] = ['id' => $ids];
        }
        if( !empty($interest) ){
            $model = Interest::find()->where(['like', 'name', $interest])->all();
            $search_keyword = ArrayHelper::getColumn($model, 'id');
            $model = BusinessInterest::find()->where(['interest_id' => $search_keyword])->all();
            $ids = ArrayHelper::getColumn($model, 'business_id');
            $conditions[] = ['id' => $ids];
        }
        if( !empty($interest_id) ){
            $model = BusinessInterest::find()->where(['interest_id' => $interest_id])->all();
            $ids = ArrayHelper::getColumn($model, 'business_id');
            $conditions[] = ['id' => $ids];
        }
            
        $lat_lng = empty($nearby) ? null : explode('-', $nearby);
        $this->output['businesses'] = $this->_getBusinesses($conditions, $country_id, null, $lat_lng);
    }

    /**
     * @api {post} /api/search-businesses-by-type Get businesses by search type
     * @apiName SearchBusinessesByType
     * @apiGroup Business
     *
     * @apiParam {String} country_id Country's id.
     * @apiParam {String} type Search by (recently_added, recently_viewed).
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} businesses businesses details.
     */
    public function actionSearchBusinessesByType($country_id, $type)
    {
        $this->_addOutputs(['businesses']);

        $search_type = $type;
        if( $search_type == 'recently_added' ){
            $this->output['businesses'] = $this->_getBusinesses(null, $country_id, ['created' => SORT_DESC]);
        }else if( $search_type == 'recently_viewed' ){
            $query = BusinessView::find()
                ->select(['business_id', 'business_view.id'])
                ->orderBy(['business_view.id' => SORT_DESC])
                ->joinWith('business')
                ->andWhere(['business.country_id' => $country_id]);
            $model = $this->_getModelWithPagination($query);

            $businesses = [];
            $ids_list = [];
            foreach ($model as $key => $business_view) {
                if( in_array($business_view->business_id, $ids_list) ){
                    continue;
                }
                $ids_list[] = $business_view->business_id;
                $businesses[] = $this->_getBusinessesDataObject($business_view->business);
            }
            $this->output['businesses'] = $businesses;
        }else{
            throw new HttpException(200, 'not supported search type');
        }
    }

    /**
     * @api {post} /api/get-business-data Get business data
     * @apiName GetBusinessData
     * @apiGroup Business
     *
     * @apiParam {String} business_id business's id to get it's details.
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
                        ->one();
        if( $model !== null ){
            $result = $this->_addBusinessView($business_id, $this->logged_user_id);

            if( $result == 'done' ){
                $this->output['business_data'] = $this->_getBusinessesDataObject($model);
            }else{
                throw new HttpException(200, $result);
            }
        }else{
            throw new HttpException(200, 'no business with this id');
        }
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
        if( $model !== null ){
            $savedBusiness = new SavedBusiness;
            $savedBusiness->user_id = $this->logged_user_id;
            $savedBusiness->business_id = $business_id;

            if(!$savedBusiness->save()){
                throw new HttpException(200, $this->_getErrors($savedBusiness));
            }
        }else{
            throw new HttpException(200, 'no business with this id');
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
        $model = SavedBusiness::findOne(['user_id' => $this->logged_user_id, 'business_id' => $saved_business_id]);

        if(!$model->delete()){
            throw new HttpException(200, $this->_getErrors($model));
        }
    }

    /**
     * @api {post} /api/get-saved-businesses Get all saved businesses
     * @apiName GetSavedBusinesses
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} user_to_get User's id of User you want to get the saved businesses for.
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} businesses businesses details.
     */
    public function actionGetSavedBusinesses($user_to_get)
    {
        $this->_addOutputs(['businesses']);

        $model = SavedBusiness::find()
                    ->select('business_id')
                    ->where(['user_id' => $user_to_get])
                    ->all();
        $ids_list = [];
        foreach ($model as $key => $business) {
            $ids_list[] = $business->business_id;
        }

        $conditions = ['id' => $ids_list];
        $this->output['businesses'] = $this->_getBusinesses($conditions);
    }

    /**
     * @api {post} /api/checkin Check-in business
     * @apiName CheckinBusiness
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} business_id business's id to checkin.
     * @apiParam {String} review User's review about the place.
     * @apiParam {String} rating User's rating about the place.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {String} checkin_id the added checkin id
     */
    public function actionCheckin($business_id, $review, $rating)
    {
        $this->_addOutputs(['checkin_id']);

        $model = Business::find()
                        ->where(['id' => $business_id])
                        ->one();
        if( $model !== null ){
            $checkin = new Checkin;
            $checkin->user_id = $this->logged_user_id;
            $checkin->business_id = $business_id;
            $checkin->text = $review;
            $checkin->rating = $rating;

            if(!$checkin->save()){
                throw new HttpException(200, $this->_getErrors($checkin));
            }else{
                $model->rating = $this->_calcRating($business_id);

                if(!$model->save()){
                    throw new HttpException(200, $this->_getErrors($model));
                }else{
                    $this->output['checkin_id'] = $checkin->id;
                }
            }
        }else{
            throw new HttpException(200, 'no business with this id');
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

        if(!$model->delete()){
            throw new HttpException(200, $this->_getErrors($model));
        }
    }

    /**
     * @api {post} /api/get-checkins Get all checkins for user or business
     * @apiName GetCheckins
     * @apiGroup Business
     *
     * @apiParam {String} business_id_to_get Business's id (optional).
     * @apiParam {String} user_id_to_get User's id (optional).
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} checkins checkins details.
     */
    public function actionGetCheckins($business_id_to_get = null, $user_id_to_get = null)
    {
        $this->_addOutputs(['checkins']);

        $conditions = [];
        if ( !empty($business_id_to_get) ) {
            $conditions['business_id'] = $business_id_to_get;
        }
        if ( !empty($user_id_to_get) ) {
            $conditions['user_id'] = $user_id_to_get;
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
     * @apiParam {String} business_id business's id to review.
     * @apiParam {String} review User's review about the place.
     * @apiParam {String} rating User's rating about the place.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {String} review_id the added review id
     */
    public function actionReview($business_id, $review, $rating)
    {
        $this->_addOutputs(['review_id']);

        $model = Business::find()
                        ->where(['id' => $business_id])
                        ->one();
        if( $model !== null ){
            $review = new Review;
            $review->user_id = $this->logged_user_id;
            $review->business_id = $business_id;
            $review->text = $review;
            $review->rating = $rating;

            if(!$review->save()){
                throw new HttpException(200, $this->_getErrors($review));
            }else{
                $model->rating = $this->_calcRating($business_id);

                if(!$model->save()){
                    throw new HttpException(200, $this->_getErrors($model));
                }else{
                    $this->output['review_id'] = $review->id;

                    // send notifications
                    if (preg_match_all('/(?<!\w)@(\w+)/', $review->text, $matches))
                    {
                        $users = $matches[1];
                        foreach ($users as $username)
                        {
                            $user = User::findOne(['username' => $username]);
                            if (empty($user)) {
                                continue;
                            }

                            $title = 'New Review Tag';
                            $body = $review->user->name .' has tagged you in review for '. $review->business->name;
                            $data = [
                                'review_id' => $review->id,
                                'business_id' => $review->business_id,
                                'type' => 3,
                            ];
                            $this->_sendNotification($user->firebase_token, $title, $body, $data);
                        }
                    }
                }
            }
        }else{
            throw new HttpException(200, 'no business with this id');
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
        $model = Review::findOne($review_id);

        if(!$model->delete()){
            throw new HttpException(200, $this->_getErrors($model));
        }
    }

    /**
     * @api {post} /api/get-reviews Get all reviews for user or business
     * @apiName GetReviews
     * @apiGroup Business
     *
     * @apiParam {String} business_id_to_get Business's id (optional).
     * @apiParam {String} user_id_to_get User's id (optional).
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} reviews reviews details.
     */
    public function actionGetReviews($business_id_to_get = null, $user_id_to_get = null)
    {
        $this->_addOutputs(['reviews']);

        $conditions = [];
        if ( !empty($business_id_to_get) ) {
            $conditions['business_id'] = $business_id_to_get;
        }
        if ( !empty($user_id_to_get) ) {
            $conditions['user_id'] = $user_id_to_get;
        }
        $this->output['reviews'] = $this->_getReviews($conditions);
    }

    /**
     * @api {post} /api/get-homescreen-reviews Get reviews for homescreen
     * @apiName GetHomescreenReviews
     * @apiGroup Business
     *
     * @apiParam {String} country_id Country's id to get reviews related to businesses inside.
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} reviews reviews details.
     */
    public function actionGetHomescreenReviews($country_id)
    {
        $this->_addOutputs(['reviews']);
        $this->output['reviews'] = $this->_getReviews($conditions, $country_id);
    }

    /**
     * @api {post} /api/add-media Add new business media
     * @apiName AddMedia
     * @apiGroup Business
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {String} business_id business's id to add media to.
     * @apiParam {String} type Media's type (image, video, menu or product).
     * @apiParam {File} Media[file] Business's new file (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionAddMedia($business_id, $type)
    {
        if( !empty($_FILES['Media']) ){
            $this->_uploadPhoto($business_id, 'Business', $type);
        }else{
            throw new HttpException(200, 'no file input');
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
        $model = Media::findOne($media_id);

        if(!unlink($model->url) || !$model->delete()){
            throw new HttpException(200, $this->_getErrors($model));
        }
    }

    /**
     * @api {post} /api/get-media Get all media for user or business
     * @apiName GetMedia
     * @apiGroup Business
     *
     * @apiParam {String} business_id_to_get Business's id (optional).
     * @apiParam {String} user_id_to_get User's id (optional).
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} media media details.
     */
    public function actionGetMedia($business_id_to_get = null, $user_id_to_get = null)
    {
        $this->_addOutputs(['media']);

        $conditions = '';
        if ( !empty($business_id_to_get) ) {
            $conditions .= "object_id = '".$business_id_to_get."' AND ";
            $conditions .= "object_type = 'business' AND ";
            $conditions .= "type != 'business_image'";
        }else if ( !empty($user_id_to_get) ) {
            $conditions .= "user_id = '".$user_id_to_get."' AND ";
            $conditions .= "type != 'profile_photo'";
        }
        $this->output['media'] = $this->_getMedia($conditions);
    }

    /**
     * @api {post} /api/get-homescreen-images Get images for homescreen
     * @apiName GetHomescreenImages
     * @apiGroup Business
     *
     * @apiParam {String} country_id Country's id to get images related to businesses inside.
     * @apiParam {String} page Page number (optional).
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} images images details.
     */
    public function actionGetHomescreenImages($country_id)
    {
        $this->_addOutputs(['images']);

        $conditions['type'] = 'image';
        $conditions['object_type'] = 'Business';
        $this->output['images'] = $this->_getMedia($conditions, $country_id);
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
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     * @apiSuccess {Array} Sponsors List of Sponsors.
     */
    public function actionGetSponsors()
    {
        $this->_addOutputs(['sponsors']);

        $query = Sponsor::find();
        $model = $this->_getModelWithPagination($query);

        $sponsors = [];
        foreach ($model as $key => $sponsor) {
            $temp['id'] = $sponsor['id'];
            $temp['name'] = $sponsor['name'];
            $temp['description'] = $sponsor['description'];
            $temp['main_image'] = Url::base(true).'/'.$sponsor['main_image'];
            $temp['link'] = $sponsor['link'];
            $sponsors[] = $temp;
        }

        $this->output['sponsors'] = $sponsors;
    }

    /***************************************/
    /************ Notifications ************/
    /***************************************/

    /**
     * @api {post} /api/send-notification Send Notification
     * @apiName SendNotification
     * @apiGroup Notifications
     *
     * @apiParam {String} user_id User's id.
     * @apiParam {String} auth_key User's auth key.
     * @apiParam {Array} users_ids Array list of users' id to send the notification to.
     * @apiParam {String} title Notification's title.
     * @apiParam {String} body Notification's body.
     *
     * @apiSuccess {String} status status code: 0 for OK, 1 for error.
     * @apiSuccess {String} errors errors details if status = 1.
     */
    public function actionSendNotification($users_ids, $title, $body)
    {
        $users_ids = explode(',', $users_ids);
        foreach ($users_ids as $users_id) {
            $user = User::findOne($users_id);
            if( !empty($user->firebase_token)){
                $data = [
                    'type' => 0,
                ];
                $this->_sendNotification($user->firebase_token, $title, $body, $data);
            }
        }
    }
}
