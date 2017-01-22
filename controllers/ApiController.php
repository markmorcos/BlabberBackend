<?php

namespace app\controllers;

use Yii;
use yii\db\Query;
use yii\web\Controller;
use yii\helpers\Url;
use yii\web\UploadedFile;
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
use yii\helpers\ArrayHelper;

class ApiController extends Controller
{
	var $no_per_page = 20;

	// TODO check if this needed on live server
	public function beforeAction($action) {
	    $this->enableCsrfValidation = false;
	    return parent::beforeAction($action);
	}

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
	public function actionIsUniqueUsername()
	{
		$parameters = array('username');
		$output = array('status' => null, 'errors' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) ){
			return;
		}

		$model = User::find()
			    ->where(['username' => $_POST['username']])
			    ->one();

		if (!empty($model)) {
			$output['status'] = 1;
			$output['errors'] = 'this username already taken';
		}else{
	    	$output['status'] = 0; //ok
		}

        echo json_encode($output);
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
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} user_data user details.
	 * @apiSuccess {String} auth_key user auth key to use for other api calls.
	 */
	public function actionSignUp()
	{
		$parameters = array('name', 'email', 'username', 'password');
		$output = array('status' => null, 'errors' => null, 'user_data' => null, 'auth_key' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) ){
			return;
		}

		// sign up
		$user = new User;
		$user->name = $_POST['name'];
		$user->email = $_POST['email'];
		$user->username = $_POST['username'];
		$user->password = Yii::$app->security->generatePasswordHash($_POST['password']);
		if( !empty($_POST['mobile']) ){
			$user->mobile = $_POST['mobile'];
		}

		if(!$user->save()){
			$output['status'] = 1;
			$output['errors'] = $this->_getErrors($user); //saving problem
			echo json_encode($output);
			return;
		}

		// save url if image coming from external source like Facebook
		if( !empty($_POST['image']) ){
			$user->profile_photo = $_POST['image'];
			if($user->save()){
				$output['status'] = 0; //ok
			}else{
				$output['status'] = 1;
				$output['errors'] = $this->_getErrors($user); //saving problem
				return;
			}

		// upload image then save it 
		}else if( !empty($_FILES['Media']) ){
			$media = new Media;
			$media->file = UploadedFile::getInstance($media,'file');
			if( isset($media->file) ){
				$media_type = 'profile_photo';
				$file_path = 'uploads/'.$media_type.'/'.$user->id.'.'.pathinfo($media->file->name, PATHINFO_EXTENSION);
				$media->url = $file_path;
				$media->type = $media_type;
				$media->user_id = $user->id;
				$media->object_id = $user->id;
				$media->object_type = 'User';

				if($media->save()){
					$media->file->saveAs($file_path);
					$user->profile_photo = $file_path;

					if($user->save()){
						$output['status'] = 0; //ok
					}else{
						$output['status'] = 1;
						$output['errors'] = $this->_getErrors($user); //saving problem
						return;
					}
				}else{
					$output['status'] = 1;
					$output['errors'] = $this->_getErrors($media); //saving problem
					return;
				}
			}
		}

		//login
		$user = User::login($_POST['email'], $_POST['password']);
		if( $user != null ){
			$output['user_data'] = $this->_getUserData($user);
			$output['auth_key'] = $user->auth_key;
			$output['status'] = 0; //ok
		}else{
			$output['status'] = 1;
			$output['errors'] = "login problem";
		}

        echo json_encode($output);
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
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} user_data user details.
	 * @apiSuccess {String} auth_key user auth key to use for other api calls.
	 */
	public function actionSignInFb()
	{
		$parameters = array('facebook_id', 'facebook_token', 'name');
		$output = array('status' => null, 'errors' => null, 'user_data' => null, 'auth_key' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) ){
			return;
		}

		// verify facebook token & facebook id
		$user_details = "https://graph.facebook.com/me?access_token=" .$_POST['facebook_token'];
		$response = file_get_contents($user_details);
		$response = json_decode($response);
		if( !isset($response) || !isset($response->id)|| $response->id != $_POST['facebook_id'] ){
			$output['status'] = 1;
			$output['errors'] = "invalid facebook token";
			return;
		}

		// check if user not saved before, to add it
		$email = $_POST['facebook_id'].'@facebook.com';
		$password = md5($_POST['facebook_id']);
		$user = User::findByEmail($email);
		if( $user == null ){
			// sign up
			$user = new User;
			$user->name = $_POST['name'];
			$user->email = $email;
			$user->password = Yii::$app->security->generatePasswordHash($password);
			$user->facebook_id = $_POST['facebook_id'];

			if(!$user->save()){
				$output['status'] = 1;
				$output['errors'] = $this->_getErrors($user); //saving problem
				echo json_encode($output);
				return;
			}

			// save url if image coming from external source like Facebook
			if( !empty($_POST['image']) ){
				$user->profile_photo = $_POST['image'];
				if($user->save()){
					$output['status'] = 0; //ok
				}else{
					$output['status'] = 1;
					$output['errors'] = $this->_getErrors($user); //saving problem
					return;
				}
			}
		}

		//login
		$user = User::login($email, $password);
		if( $user != null ){
			$output['user_data'] = $this->_getUserData($user);
			$output['auth_key'] = $user->auth_key;
			$output['status'] = 0; //ok
		}else{
			$output['status'] = 1;
			$output['errors'] = "login problem";
		}

        echo json_encode($output);
	}

	/**
	 * @api {post} /api/sign-in Sign in existing user
	 * @apiName SignIn
	 * @apiGroup User
	 *
	 * @apiParam {String} email User's unique email.
	 * @apiParam {String} password User's password.
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} user_data user details.
	 * @apiSuccess {String} auth_key user auth key to use for other api calls.
	 */
	public function actionSignIn()
	{
		$parameters = array('email', 'password');
		$output = array('status' => null, 'errors' => null, 'user_data' => null, 'auth_key' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) ){
			return;
		}

		$user = User::login($_POST['email'], $_POST['password']);
		if( $user != null ){
			$output['user_data'] = $this->_getUserData($user);
			$output['auth_key'] = $user->auth_key;
			$output['status'] = 0; //ok
		}else{
			$output['status'] = 1;
			$output['errors'] = "login problem";
		}

        echo json_encode($output);
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
	public function actionRecoverPassword()
	{
		$parameters = array('email');
		$output = array('status' => null, 'errors' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) ){
			return;
		}

		$new_password = substr(md5(uniqid(rand(), true)), 6, 6);

		$user = User::findByEmail($_POST['email']);
		if( $user != null ){
			$user->password = Yii::$app->security->generatePasswordHash($new_password);
			if($user->save()){
				$result = Yii::$app->mailer->compose()
				    ->setFrom('recovery@blabber.com', 'Blabber support')
				    ->setTo($_POST['email'])
				    ->setSubject('Blabber Password Recovery')
				    ->setTextBody('your password changed to: '.$new_password)
				    ->send();
				if ($result) {
				    $output['status'] = 0; //ok
				} else {
					$output['status'] = 1;
					$output['errors'] = 'Password changed but errors while sending email'; //sending email problem
					// $output['errors'] = 'Password changed but errors while sending email: '.$mail->getError(); //sending email problem
				}
			}else{
				$output['status'] = 1;
				$output['errors'] = $this->_getErrors($user); //saving problem
			}
		}else{
			$output['status'] = 1;
			$output['errors'] = "no user with this email";
		}

        echo json_encode($output);
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
	public function actionChangePassword()
	{
		$parameters = array('user_id', 'auth_key', 'new_password');
		$output = array('status' => null, 'errors' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$model = User::findOne($_POST['user_id']);
		$model->password = Yii::$app->security->generatePasswordHash($_POST['new_password']);
		if($model->save()){
			$output['status'] = 0; //ok
		}else{
			$output['status'] = 1;
			$output['errors'] = $this->_getErrors($model); //saving problem
		}

        echo json_encode($output);
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
	public function actionChangeProfilePhoto()
	{
		$parameters = array('user_id', 'auth_key');
		$output = array('status' => null, 'errors' => null);

		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$user = User::findOne($_POST['user_id']);

		// save url if image coming from external source like Facebook
		if( !empty($_POST['image']) ){
			$user->profile_photo = $_POST['image'];
			if($user->save()){
				$output['status'] = 0; //ok
			}else{
				$output['status'] = 1;
				$output['errors'] = $this->_getErrors($user); //saving problem
			}

		// upload image then save it 
		}else if( !empty($_FILES['Media']) ){
			$media = new Media;
			$media->file = UploadedFile::getInstance($media,'file');
			if( isset($media->file) ){
				$media_type = 'profile_photo';
				$file_path = 'uploads/'.$media_type.'/'.$user->id.'.'.pathinfo($media->file->name, PATHINFO_EXTENSION);
				$media->url = $file_path;
				$media->type = $media_type;
				$media->user_id = $user->id;
				$media->object_id = $user->id;
				$media->object_type = 'User';

				if($media->save()){
					$media->file->saveAs($file_path);
					$user->profile_photo = $file_path;

					if($user->save()){
						$output['status'] = 0; //ok
					}else{
						$output['status'] = 1;
						$output['errors'] = $this->_getErrors($user); //saving problem
					}
				}else{
					$output['status'] = 1;
					$output['errors'] = $this->_getErrors($media); //saving problem
				}
			}
		}else{
			$output['status'] = 1;
			$output['errors'] = 'no url or file input';
		}

    	echo json_encode($output);
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
		$parameters = array('user_id', 'auth_key');
		$output = array('status' => null, 'errors' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$user = User::findOne($_POST['user_id']);
    	$user->auth_key = "";
    	if( $user->save() ){
        	$output['status'] = 0; //ok
        }else{
			$output['status'] = 1;
			$output['errors'] = "logout problem";
        }

        echo json_encode($output);
	}

	/**
	 * @api {post} /api/get-profile Get user profile
	 * @apiName GetProfile
	 * @apiGroup User
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 * @apiParam {String} user_id_to_get User's id of User profile you want to get (optional).
	 * @apiParam {String} user_username_to_get User's usernam of User profile you want to get (optional).
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} user_data user details.
	 */
	public function actionGetProfile()
	{
		$parameters = array('user_id', 'auth_key');
		$output = array('status' => null, 'errors' => null, 'user_data' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		if (!empty($_POST['user_id_to_get'])) {
			$user = User::findOne($_POST['user_id_to_get']);
		}elseif (!empty($_POST['user_username_to_get'])) {
			$user = User::findOne(['username' => $_POST['user_username_to_get']]);
		}
		
		if( $user != null ){
			$output['user_data'] = $this->_getUserData($user);
			$output['status'] = 0; //ok
		}else{
			$output['status'] = 1;			
			$output['errors'] = "no user with this id or username";
		}

        echo json_encode($output);
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
	 * @apiParam {Array} interests_ids array of interests ids to add to user, ex. 2,5,7 (optional).
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 */
	public function actionEditProfile()
	{
		$parameters = array('user_id', 'auth_key');
		$output = array('status' => null, 'errors' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}
		
		$user = User::findOne($_POST['user_id']);
		if( $user == null ){
			$output['status'] = 1;			
			$output['errors'] = "no user with this id";
			return;
		}

		if ( !empty($_POST['name']) ) $user->name = $_POST['name'];
		if ( !empty($_POST['username']) ) $user->username = $_POST['username'];
		if ( !empty($_POST['mobile']) ) $user->mobile = $_POST['mobile'];
		if ( !empty($_POST['gender']) ) $user->gender = $_POST['gender'];
		if ( !empty($_POST['birthdate']) ) $user->birthdate = $_POST['birthdate'];

		if(!$user->save()){
			$output['status'] = 1;
			$output['errors'] = $this->_getErrors($user); //saving problem
			echo json_encode($output);
			return;
		}

		if( !empty($_POST['interests_ids']) ){
			// remove old interests
            UserInterest::deleteAll('user_id = '.$user->id);

	        $interests = explode(',', $_POST['interests_ids']);
	        foreach ($interests as $interest) {
	            $user_interest = new UserInterest();
	            $user_interest->user_id = $user->id;
	            $user_interest->interest_id = $interest;
	            $user_interest->save();
	        }
		}

		$output['status'] = 0; //ok
		echo json_encode($output);
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
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} users the list of users
	 */
	public function actionSearchForUser()
	{
		$parameters = array('user_id', 'auth_key', 'name');
		$output = array('status' => null, 'errors' => null, 'users' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$model = User::find()
			    ->where(['like', 'name', $_POST['name']])
			    ->where(['!=', 'id', $_POST['user_id']])
			    ->orderBy(['id' => SORT_DESC])
			    ->all();

		$users = array();
		foreach ($model as $key => $user) {
			$users[] = $this->_getUserData($user);
		}

    	$output['status'] = 0; //ok
    	$output['users'] = $users;

        echo json_encode($output);
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
	public function actionAddFriend()
	{
		$parameters = array('user_id', 'auth_key', 'friend_id');
		$output = array('status' => null, 'errors' => null, 'request' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$friendship = $this->_getLastFriendshipRequest($_POST['user_id'], $_POST['friend_id']);

		//if there isn't friendship request or if sent old one and rejected (status:2) or cancelled (status:3) or removed (status:4)
		if ( $friendship == null || $friendship->status == 2 || $friendship->status == 3 || $friendship->status == 4 ){ 
			$model = new Friendship;
			$model->user_id = $_POST['user_id'];
			$model->friend_id = $_POST['friend_id'];
			$model->status = 0;

			if($model->save()){
				$output['status'] = 0; //ok
				$output['request'] = $model->attributes;
			}else{
				$output['status'] = 1;
				$output['errors'] = $this->_getErrors($model); //saving problem
			}
		}else{
			$output['status'] = 1;
			$output['errors'] = "you can't send new friend request";
		}

        echo json_encode($output);
	}

	/**
	 * @api {post} /api/get-friend-requests-sent Get all the friend requests user sent
	 * @apiName GetFriendRequestsSent
	 * @apiGroup Friendship
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} requests requests details.
	 */
	public function actionGetFriendRequestsSent()
	{
		$parameters = array('user_id', 'auth_key');
		$output = array('status' => null, 'errors' => null, 'requests' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$model = Friendship::find()
		    ->where(['user_id' => $_POST['user_id'], 'status' => 0])
		    ->all();

		$requests = array();
		foreach ($model as $key => $request) {
			$requests[] = array('id' => $request->id, 'friend_data' => $this->_getUserData($request->friend));
		}

    	$output['status'] = 0; //ok
    	$output['requests'] = $requests;

        echo json_encode($output);
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
	public function actionCancelFriendRequest()
	{
		$parameters = array('user_id', 'auth_key', 'request_id');
		$output = array('status' => null, 'errors' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$model = Friendship::findOne($_POST['request_id']);
		$model->status = 3;
		if($model->save()){
			$output['status'] = 0; //ok
		}else{
			$output['status'] = 1;
			$output['errors'] = $this->_getErrors($model); //saving problem
		}

        echo json_encode($output);
	}

	/**
	 * @api {post} /api/get-friend-requests-received Get all the friend requests user received
	 * @apiName GetFriendRequestsReceived
	 * @apiGroup Friendship
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} requests requests details.
	 */
	public function actionGetFriendRequestsReceived()
	{
		$parameters = array('user_id', 'auth_key');
		$output = array('status' => null, 'errors' => null, 'requests' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$model = Friendship::find()
		    ->where(['friend_id' => $_POST['user_id'], 'status' => 0])
		    ->all();

		$requests = array();
		foreach ($model as $key => $request) {
			$requests[] = array('id' => $request->id, 'user_data' => $this->_getUserData($request->user));
		}

    	$output['status'] = 0; //ok
    	$output['requests'] = $requests;

        echo json_encode($output);
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
	public function actionAcceptFriendRequest()
	{
		$parameters = array('user_id', 'auth_key', 'request_id');
		$output = array('status' => null, 'errors' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		// accept request
		$request = Friendship::findOne($_POST['request_id']);
		$request->status = 1;
		if( !$request->save() ){
			$output['status'] = 1;
			$output['errors'] = $this->_getErrors($request);
		}

		// add as a friend in the other user list
		$friendship_model = new Friendship;
		$friendship_model->user_id = $request->friend_id;
		$friendship_model->friend_id = $request->user_id;
		$friendship_model->status = 1;
		if( !$friendship_model->save() ){
			$output['status'] = 1;
			$output['errors'] = $this->_getErrors($friendship_model);
		}

		if( $output['status'] == null ){
			$output['status'] = 0; //ok
		}

        echo json_encode($output);
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
	public function actionRejectFriendRequest()
	{
		$parameters = array('user_id', 'auth_key', 'request_id');
		$output = array('status' => null, 'errors' => null);

				// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$model = Friendship::findOne($_POST['request_id']);
		$model->status = 2;
		if($model->save()){
			$output['status'] = 0; //ok
		}else{
			$output['status'] = 1;
			$output['errors'] = $this->_getErrors($model); //saving problem
		}

        echo json_encode($output);
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
	public function actionRemoveFriend()
	{
		$parameters = array('user_id', 'auth_key', 'friend_id');
		$output = array('status' => null, 'errors' => null);

				// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$logged_user_id = $_POST['user_id'];
		$friend_id = $_POST['friend_id'];

		$friendship1 = Friendship::find()
		    ->where(['friend_id' => $logged_user_id, 'user_id' => $friend_id, 'status' => 1])
		    ->one();
		$friendship2 = Friendship::find()
		    ->where(['friend_id' => $friend_id, 'user_id' => $logged_user_id, 'status' => 1])
		    ->one();

	    if( isset($friendship1) && isset($friendship2) ){
	    	$friendship1->status = 4;
	    	$friendship2->status = 4;

		    if( $friendship1->save() && $friendship2->save() ){
				$output['status'] = 0; //ok
		    }else{
				$output['status'] = 1;
				$output['errors'] = $this->_getErrors($friendship1) + $this->_getErrors($friendship2); //saving problem
			}
		}else{
			$output['status'] = 1;
			$output['errors'] = "problem occured";
		}

        echo json_encode($output);
	}

	/**
	 * @api {post} /api/get-friends Get all the user friends
	 * @apiName GetFriends
	 * @apiGroup Friendship
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} friends friends details.
	 */
	public function actionGetFriends()
	{
		$parameters = array('user_id', 'auth_key');
		$output = array('status' => null, 'errors' => null, 'friends' => null);

				// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$model = Friendship::find()
		    ->where(['user_id' => $_POST['user_id'], 'status' => 1])
		    ->all();

		$friends = array();
		foreach ($model as $key => $friendship) {
			$friends[] = $this->_getUserData($friendship->friend);
		}

		$output['status'] = 0; //ok
		$output['friends'] = $friends;

        echo json_encode($output);
	}

	/***************************************/
	/************* Categories **************/
	/***************************************/

	/**
	 * @api {post} /api/get-categories Get the main categories
	 * @apiName GetCategories
	 * @apiGroup Category
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} categories categories details.
	 */
	public function actionGetCategories()
	{
		$parameters = array('user_id', 'auth_key');
		$output = array('status' => null, 'errors' => null, 'categories' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$output['categories'] = $this->_getCategories();
		$output['status'] = 0; //ok

        echo json_encode($output);
	}

	/**
	 * @api {post} /api/get-sub-categories Get the sub categories of one category
	 * @apiName GetSubCategories
	 * @apiGroup Category
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 * @apiParam {String} category_id parent category id.
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} categories categories details.
	 */
	public function actionGetSubCategories()
	{
		$parameters = array('user_id', 'auth_key', 'category_id');
		$output = array('status' => null, 'errors' => null, 'categories' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$output['categories'] = $this->_getCategories($_POST['category_id']);
		$output['status'] = 0; //ok

        echo json_encode($output);
	}

	/***************************************/
	/************** Business ***************/
	/***************************************/

	/**
	 * @api {post} /api/get-countries Get all countries
	 * @apiName GetCountries
	 * @apiGroup Business
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} Countries List of Countries.
	 */
	public function actionGetCountries()
	{
		$parameters = array('user_id', 'auth_key');
		$output = array('status' => null, 'errors' => null, 'countries' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$model = Country::find()->all();

	    $countries = [];
		foreach ($model as $key => $country) {
			$temp['id'] = $country['id'];
			$temp['name'] = $country['name'];
			$countries[] = $temp;
		}

		$output['countries'] = $countries;
		$output['status'] = 0; //ok

        echo json_encode($output);
	}

	/**
	 * @api {post} /api/get-cities Get all cities
	 * @apiName GetCities
	 * @apiGroup Business
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 * @apiParam {String} country_id Country's id to get cities inside.
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} Cities List of Cities.
	 */
	public function actionGetCities()
	{
		$parameters = array('user_id', 'auth_key', 'country_id');
		$output = array('status' => null, 'errors' => null, 'cities' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$model = City::find()
				    ->where(['country_id' => $_POST['country_id']])
				    ->all();

	    $cities = [];
		foreach ($model as $key => $city) {
			$temp['id'] = $city['id'];
			$temp['name'] = $city['name'];
			$cities[] = $temp;
		}

		$output['cities'] = $cities;
		$output['status'] = 0; //ok

        echo json_encode($output);
	}

	/**
	 * @api {post} /api/get-flags Get all flags
	 * @apiName GetFlags
	 * @apiGroup Business
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} Flags List of Flags.
	 */
	public function actionGetFlags()
	{
		$parameters = array('user_id', 'auth_key');
		$output = array('status' => null, 'errors' => null, 'flags' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$model = Flag::find()->all();

	    $flags = [];
		foreach ($model as $key => $flag) {
			$temp['id'] = $flag['id'];
			$temp['name'] = $flag['name'];
			$temp['icon'] = Url::base(true).'/'.$flag['icon'];
			$flags[] = $temp;
		}

		$output['flags'] = $flags;
		$output['status'] = 0; //ok

        echo json_encode($output);
	}

	/**
	 * @api {post} /api/get-interests Get all interests
	 * @apiName GetInterests
	 * @apiGroup Business
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} Interests List of Interests.
	 */
	public function actionGetInterests()
	{
		$parameters = array('user_id', 'auth_key');
		$output = array('status' => null, 'errors' => null, 'interests' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$model = Interest::find()->all();

	    $interests = [];
		foreach ($model as $key => $interest) {
			$temp['id'] = $interest['id'];
			$temp['name'] = $interest['name'];
			$interests[] = $temp;
		}

		$output['interests'] = $interests;
		$output['status'] = 0; //ok

        echo json_encode($output);
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
	 * @apiParam {String} website business website. (optional)
	 * @apiParam {String} fb_page business Facebook page. (optional)
	 * @apiParam {String} description business description.
	 * @apiParam {String} category_id Category's id to add business inside.
	 * @apiParam {Array} flags_ids array of flags ids to add to business, ex. 10,13,5 (optional).
	 * @apiParam {Array} interests array of interests strings to add to business, ex. interest1,interest2,interest3 (optional).
	 * @apiParam {File} Media[file] Business's main image file (optional).
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} businesses businesses details.
	 */
	public function actionAddBusiness()
	{
		$parameters = array('user_id', 'auth_key', 'name', 'address', 'country_id', 'city_id', 'phone', 'open_from', 'open_to', 'lat', 'lng', 'price', 'description', 'category_id');
		$output = array('status' => null, 'errors' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$business = new Business;
		$business->name = $_POST['name'];
		$business->address = $_POST['address'];
		$business->country_id = $_POST['country_id'];
		$business->city_id = $_POST['city_id'];
		$business->phone = $_POST['phone'];
		$business->open_from = $_POST['open_from'];
		$business->open_to = $_POST['open_to'];
		$business->lat = $_POST['lat'];
		$business->lng = $_POST['lng'];
		$business->price = $_POST['price'];
		if ( !empty($_POST['website']) ) {
			$business->website = $_POST['website'];
		}
		if ( !empty($_POST['fb_page']) ) {
			$business->fb_page = $_POST['fb_page'];
		}
		$business->description = $_POST['description'];
		$business->category_id = $_POST['category_id'];
		$business->admin_id = $_POST['user_id'];

		if(!$business->save()){
			$output['status'] = 1;
			$output['errors'] = $this->_getErrors($business); //saving problem
			echo json_encode($output);
			return;
		}

		if( !empty($_POST['flags_ids']) ){
	        $flags = explode(',', $_POST['flags_ids']);
	        foreach ($flags as $flag) {
	            $business_flag = new BusinessFlag();
	            $business_flag->business_id = $business->id;
	            $business_flag->flag_id = $flag;
	            $business_flag->save();
	        }
		}

		if( !empty($_POST['interests']) ){
	        $interests = explode(',', $_POST['interests']);
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
			$media = new Media;
			$media->file = UploadedFile::getInstance($media,'file');
			if( isset($media->file) ){
				$media_type = 'business_image';
				$file_path = 'uploads/'.$media_type.'/'.$business->id.'.'.pathinfo($media->file->name, PATHINFO_EXTENSION);
				$media->url = $file_path;
				$media->type = $media_type;
				$media->user_id = $user->id;
				$media->object_id = $business->id;
				$media->object_type = 'Business';

				if($media->save()){
					$media->file->saveAs($file_path);
					$business->main_image = $file_path;

					if(!$business->save()){
						$output['status'] = 1;
						$output['errors'] = $this->_getErrors($business); //saving problem
						return;
					}
				}else{
					$output['status'] = 1;
					$output['errors'] = $this->_getErrors($media); //saving problem
					return;
				}
			}
		}

		$output['status'] = 0; //ok
        echo json_encode($output);
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
	 * @apiParam {String} website business website. (optional)
	 * @apiParam {String} fb_page business Facebook page. (optional)
	 * @apiParam {String} description business description (optional).
	 * @apiParam {String} category_id Category's id to add business inside (optional).
	 * @apiParam {Array} flags_ids array of flags ids to add to business, ex. 10,13,5 (optional).
	 * @apiParam {Array} interests array of interests strings to add to business, ex. interest1,interest2,interest3 (optional).
	 * @apiParam {File} Media[file] Business's main image file (optional).
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} businesses businesses details.
	 */
	public function actionEditBusiness()
	{
		$parameters = array('user_id', 'auth_key', 'business_id');
		$output = array('status' => null, 'errors' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$business = Business::find()
					    ->where(['id' => $_POST['business_id']])
					    ->one();
	    if( $business == null ){
			$output['status'] = 1;			
			$output['errors'] = "no business with this id";
			return;
		}

		if ( !empty($_POST['name']) ) $business->name = $_POST['name'];
		if ( !empty($_POST['address']) ) $business->address = $_POST['address'];
		if ( !empty($_POST['country_id']) ) $business->country_id = $_POST['country_id'];
		if ( !empty($_POST['city_id']) ) $business->city_id = $_POST['city_id'];
		if ( !empty($_POST['phone']) ) $business->phone = $_POST['phone'];
		if ( !empty($_POST['open_from']) ) $business->open_from = $_POST['open_from'];
		if ( !empty($_POST['open_to']) ) $business->open_to = $_POST['open_to'];
		if ( !empty($_POST['lat']) ) $business->lat = $_POST['lat'];
		if ( !empty($_POST['lng']) ) $business->lng = $_POST['lng'];
		if ( !empty($_POST['price']) ) $business->price = $_POST['price'];
		if ( !empty($_POST['website']) ) $business->website = $_POST['website'];
		if ( !empty($_POST['fb_page']) ) $business->fb_page = $_POST['fb_page'];
		if ( !empty($_POST['description']) ) $business->description = $_POST['description'];
		if ( !empty($_POST['category_id']) ) $business->category_id = $_POST['category_id'];
		// $business->admin_id = $_POST['user_id']; //TODO check permissions

		if(!$business->save()){
			$output['status'] = 1;
			$output['errors'] = $this->_getErrors($business); //saving problem
			echo json_encode($output);
			return;
		}

		if( !empty($_POST['flags_ids']) ){
			// remove old flags
            BusinessFlag::deleteAll('business_id = '.$business->id);

	        $flags = explode(',', $_POST['flags_ids']);
	        foreach ($flags as $flag) {
	            $business_flag = new BusinessFlag();
	            $business_flag->business_id = $business->id;
	            $business_flag->flag_id = $flag;
	            $business_flag->save();
	        }
		}

		if( !empty($_POST['interests']) ){
			// remove old interests
            BusinessInterest::deleteAll('business_id = '.$business->id);

	        $interests = explode(',', $_POST['interests']);
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
			$media = new Media;
			$media->file = UploadedFile::getInstance($media,'file');
			if( isset($media->file) ){
				$media_type = 'business_image';
				$file_path = 'uploads/'.$media_type.'/'.$business->id.'.'.pathinfo($media->file->name, PATHINFO_EXTENSION);
				$media->url = $file_path;
				$media->type = $media_type;
				$media->user_id = $user->id;
				$media->object_id = $business->id;
				$media->object_type = 'Business';

				if($media->save()){
					$media->file->saveAs($file_path);
					$business->main_image = $file_path;

					if(!$business->save()){
						$output['status'] = 1;
						$output['errors'] = $this->_getErrors($business); //saving problem
						return;
					}
				}else{
					$output['status'] = 1;
					$output['errors'] = $this->_getErrors($media); //saving problem
					return;
				}
			}
		}

		$output['status'] = 0; //ok
		echo json_encode($output);
	}

	/**
	 * @api {post} /api/get-homescreen-businesses Get businesses for homescreen
	 * @apiName GetHomescreenBusinesses
	 * @apiGroup Business
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} businesses businesses details.
	 */
	public function actionGetHomescreenBusinesses()
	{
		$parameters = array('user_id', 'auth_key');
		$output = array('status' => null, 'errors' => null, 'businesses' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		// $conditions = ['show_in_home' => true]; //TODO uncomment this later
		$conditions = [];
		$output['businesses'] = $this->_getBusinesses($conditions);
		$output['status'] = 0; //ok

        echo json_encode($output);
	}

	/**
	 * @api {post} /api/get-businesses Get businesses from category
	 * @apiName GetBusinesses
	 * @apiGroup Business
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 * @apiParam {String} category_id Category's id to get businesses inside.
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} businesses businesses details.
	 */
	public function actionGetBusinesses()
	{
		$parameters = array('user_id', 'auth_key', 'category_id');
		$output = array('status' => null, 'errors' => null, 'businesses' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$conditions = ['category_id' => $_POST['category_id']];
		$output['businesses'] = $this->_getBusinesses($conditions);
		$output['status'] = 0; //ok

        echo json_encode($output);
	}

	/**
	 * @api {post} /api/search-businesses Get businesses by search
	 * @apiName SearchBusinesses
	 * @apiGroup Business
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 * @apiParam {String} name the search keyword for business name (optional).
	 * @apiParam {String} country the search keyword for business country (optional).
	 * @apiParam {String} country_id the business country_id (optional).
	 * @apiParam {String} city the search keyword for business city (optional).
	 * @apiParam {String} city_id the business city_id (optional).
	 * @apiParam {String} category the search keyword for business category (optional).
	 * @apiParam {String} category_id the business category_id (optional).
	 * @apiParam {String} flag the search keyword for business flag (optional).
	 * @apiParam {String} flag_id the business flag_id (optional).
	 * @apiParam {String} interest the search keyword for business interest (optional).
	 * @apiParam {String} interest_id the business interest_id (optional).
	 * @apiParam {String} nearby the search coordinates for nearby business, value lat-lng, ex. 32.22-37.11 (optional).
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} businesses businesses details.
	 */
	public function actionSearchBusinesses()
	{
		$parameters = array('user_id', 'auth_key');
		$output = array('status' => null, 'errors' => null, 'businesses' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$conditions[] = 'or';
		$and_conditions[] = 'and';
		
		if( !empty($_POST['name']) ){
			$conditions[] = ['like', 'name', $_POST['name']];
		}
		if( !empty($_POST['country']) ){
			$model = Country::find()->where(['like', 'name', $_POST['country']])->all();
			$search_keyword = ArrayHelper::getColumn($model, 'id');
			$conditions[] = ['country_id' => $search_keyword];
		}
		if( !empty($_POST['country_id']) ){
			$conditions[] = ['country_id' => $_POST['country_id']];
		}
		if( !empty($_POST['city']) ){
			$model = City::find()->where(['like', 'name', $_POST['city']])->all();
			$search_keyword = ArrayHelper::getColumn($model, 'id');
			$conditions[] = ['city_id' => $search_keyword];
		}
		if( !empty($_POST['city_id']) ){
			$conditions[] = ['city_id' => $_POST['city_id']];
		}
		if( !empty($_POST['category']) ){
			$model = Category::find()->where(['like', 'name', $_POST['category']])->all();
			$search_keyword = ArrayHelper::getColumn($model, 'id');
			$conditions[] = ['category_id' => $search_keyword];
		}
		if( !empty($_POST['category_id']) ){
			$conditions[] = ['category_id' => $_POST['category_id']];
		}
		if( !empty($_POST['flag']) ){
			$model = Flag::find()->where(['like', 'name', $_POST['flag']])->all();
			$search_keyword = ArrayHelper::getColumn($model, 'id');
			$model = BusinessFlag::find()->where(['flag_id' => $search_keyword])->all();
			$ids = ArrayHelper::getColumn($model, 'business_id');
			$conditions[] = ['id' => $ids];
		}
		if( !empty($_POST['flag_id']) ){
			$model = BusinessFlag::find()->where(['flag_id' => $_POST['flag_id']])->all();
			$ids = ArrayHelper::getColumn($model, 'business_id');
			$conditions[] = ['id' => $ids];
		}
		if( !empty($_POST['interest']) ){
			$model = Interest::find()->where(['like', 'name', $_POST['interest']])->all();
			$search_keyword = ArrayHelper::getColumn($model, 'id');
			$model = BusinessInterest::find()->where(['interest_id' => $search_keyword])->all();
			$ids = ArrayHelper::getColumn($model, 'business_id');
			$conditions[] = ['id' => $ids];
		}
		if( !empty($_POST['interest_id']) ){
			$model = BusinessInterest::find()->where(['interest_id' => $_POST['interest_id']])->all();
			$ids = ArrayHelper::getColumn($model, 'business_id');
			$conditions[] = ['id' => $ids];
		}

		if( !empty($_POST['nearby']) ){
			$lat_lng = explode('-', $_POST['nearby']);
			$lat = $lat_lng[0];
			$lng = $lat_lng[1];

			$model = (new Query())
			    ->select(['id', '( 6371 * acos( cos( radians('.$lat.') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians('.$lng.') ) + sin( radians('.$lat.') ) * sin( radians( lat ) ) ) ) AS distance'])
			    ->from('business')
			    ->having('distance < 5')
			    ->orderBy(['distance' => SORT_ASC])
			    ->limit($this->no_per_page)
			    ->all();
			$ids = ArrayHelper::getColumn($model, 'id');
			$and_conditions[] = ['id' => $ids];
		}
			
		$output['businesses'] = $this->_getBusinesses($conditions, null, null, $and_conditions);
		$output['status'] = 0; //ok
		
        echo json_encode($output);
	}

	/**
	 * @api {post} /api/search-businesses-by-type Get businesses by search type
	 * @apiName SearchBusinessesByType
	 * @apiGroup Business
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 * @apiParam {String} type Search by (recently_added, recently_viewed).
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} businesses businesses details.
	 */
	public function actionSearchBusinessesByType()
	{
		$parameters = array('user_id', 'auth_key', 'type');
		$output = array('status' => null, 'errors' => null, 'businesses' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$search_type = $_POST['type'];
		if( $search_type == 'recently_added' ){
			$output['businesses'] = $this->_getBusinesses(null, $this->no_per_page, ['created' => SORT_DESC]);
			$output['status'] = 0; //ok
		}else if( $search_type == 'recently_viewed' ){
			$model = BusinessView::find()
				->select(['business_id', 'created'])
			    ->limit($this->no_per_page)
			    ->orderBy(['created' => SORT_DESC])
			    ->distinct()
			    ->all();
		    $ids_list = [];
		    foreach ($model as $key => $business) {
		    	$ids_list[] = $business->business_id;
		    }
			$conditions = ['id' => $ids_list];
			$output['businesses'] = $this->_getBusinesses($conditions);
			$output['status'] = 0; //ok
		}else{
			$output['status'] = 1;			
			$output['errors'] = "not supported search type or keyword is empty";
		}
		
        echo json_encode($output);
	}

	/**
	 * @api {post} /api/get-business-data Get business data
	 * @apiName GetBusinessData
	 * @apiGroup Business
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 * @apiParam {String} business_id business's id to get it's details.
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} business_data business details.
	 */
	public function actionGetBusinessData()
	{
		$parameters = array('user_id', 'auth_key', 'business_id');
		$output = array('status' => null, 'errors' => null, 'business_data' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$model = Business::find()
					    ->where(['id' => $_POST['business_id']])
					    ->one();
	    if( $model != null ){
	    	$result = $this->_addBusinessView($_POST['business_id'], $_POST['user_id']);

	    	if( $result == 'done' ){
				$output['business_data'] = $this->_getBusinessesDataObject($model);
				$output['status'] = 0; //ok
			}else{
				$output['status'] = 1;			
				$output['errors'] = $result;
			}
	    }else{
			$output['status'] = 1;			
			$output['errors'] = "no business with this id";
		}

        echo json_encode($output);
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
	public function actionSaveBusiness()
	{
		$parameters = array('user_id', 'auth_key', 'business_id');
		$output = array('status' => null, 'errors' => null);

		$model = Business::find()
					    ->where(['id' => $_POST['business_id']])
					    ->one();
	    if( $model != null ){
			$savedBusiness = new SavedBusiness;
			$savedBusiness->user_id = $_POST['user_id'];
			$savedBusiness->business_id = $_POST['business_id'];

			if(!$savedBusiness->save()){
				$output['status'] = 1;
				$output['errors'] = $this->_getErrors($savedBusiness); //saving problem
			}else{
				$output['status'] = 0; //ok
			}
	    }else{
			$output['status'] = 1;			
			$output['errors'] = "no business with this id";
		}

        echo json_encode($output);
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
	public function actionDeleteSavedBusiness()
	{
		$parameters = array('user_id', 'auth_key', 'saved_business_id');
		$output = array('status' => null, 'errors' => null);

		$model = SavedBusiness::findOne(['user_id' => $_POST['user_id'], 'business_id' => $_POST['saved_business_id']]);

		if($model->delete()){
			$output['status'] = 0; //ok
		}else{
			$output['status'] = 1;
			$output['errors'] = $this->_getErrors($model); //saving problem
		}

        echo json_encode($output);
	}

	/**
	 * @api {post} /api/get-saved-businesses Get all saved businesses
	 * @apiName GetSavedBusinesses
	 * @apiGroup Business
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 * @apiParam {String} user_to_get User's id of User you want to get the saved businesses for.
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} businesses businesses details.
	 */
	public function actionGetSavedBusinesses()
	{
		$parameters = array('user_id', 'auth_key', 'user_to_get');
		$output = array('status' => null, 'errors' => null, 'businesses' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$model = SavedBusiness::find()
					->select('business_id')
				    ->where(['user_id' => $_POST['user_to_get']])
				    ->all();
	    $ids_list = [];
	    foreach ($model as $key => $business) {
	    	$ids_list[] = $business->business_id;
	    }

		$conditions = ['id' => $ids_list];
		$output['businesses'] = $this->_getBusinesses($conditions);
		$output['status'] = 0; //ok

        echo json_encode($output);
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
	public function actionCheckin()
	{
		$parameters = array('user_id', 'auth_key', 'business_id', 'review', 'rating');
		$output = array('status' => null, 'errors' => null, 'checkin_id' => null);

		$model = Business::find()
					    ->where(['id' => $_POST['business_id']])
					    ->one();
	    if( $model != null ){
			$checkin = new Checkin;
			$checkin->user_id = $_POST['user_id'];
			$checkin->business_id = $_POST['business_id'];
			$checkin->text = $_POST['review'];
			$checkin->rating = $_POST['rating'];

			if(!$checkin->save()){
				$output['status'] = 1;
				$output['errors'] = $this->_getErrors($checkin); //saving problem
			}else{
				$model->rating = $this->_calcRating($_POST['business_id']);

				if(!$model->save()){
					$output['status'] = 1;
					$output['errors'] = $this->_getErrors($model); //saving problem
				}else{
					$output['status'] = 0; //ok
					$output['checkin_id'] = $checkin->id;
				}
			}
	    }else{
			$output['status'] = 1;			
			$output['errors'] = "no business with this id";
		}

        echo json_encode($output);
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
	public function actionDeleteCheckin()
	{
		$parameters = array('user_id', 'auth_key', 'checkin_id');
		$output = array('status' => null, 'errors' => null);

		$model = Checkin::findOne($_POST['checkin_id']);

		if($model->delete()){
			$output['status'] = 0; //ok
		}else{
			$output['status'] = 1;
			$output['errors'] = $this->_getErrors($model); //saving problem
		}

        echo json_encode($output);
	}

	/**
	 * @api {post} /api/get-checkins Get all checkins for user or business
	 * @apiName GetCheckins
	 * @apiGroup Business
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 * @apiParam {String} business_id_to_get Business's id (optional).
	 * @apiParam {String} user_id_to_get User's id (optional).
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} checkins checkins details.
	 */
	public function actionGetCheckins()
	{
		$parameters = array('user_id', 'auth_key');
		$output = array('status' => null, 'errors' => null, 'checkins' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$conditions = [];
		if ( !empty($_POST['business_id_to_get']) ) {
			$conditions['business_id'] = $_POST['business_id_to_get'];
		}
		if ( !empty($_POST['user_id_to_get']) ) {
			$conditions['user_id'] = $_POST['user_id_to_get'];
		}
		$output['checkins'] = $this->_getCheckins($conditions);
		$output['status'] = 0; //ok

        echo json_encode($output);
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
	public function actionReview()
	{
		$parameters = array('user_id', 'auth_key', 'business_id', 'review', 'rating');
		$output = array('status' => null, 'errors' => null, 'review_id' => null);

		$model = Business::find()
					    ->where(['id' => $_POST['business_id']])
					    ->one();
	    if( $model != null ){
			$review = new Review;
			$review->user_id = $_POST['user_id'];
			$review->business_id = $_POST['business_id'];
			$review->text = $_POST['review'];
			$review->rating = $_POST['rating'];

			if(!$review->save()){
				$output['status'] = 1;
				$output['errors'] = $this->_getErrors($review); //saving problem
			}else{
				$model->rating = $this->_calcRating($_POST['business_id']);

				if(!$model->save()){
					$output['status'] = 1;
					$output['errors'] = $this->_getErrors($model); //saving problem
				}else{
					$output['status'] = 0; //ok
					$output['review_id'] = $review->id;
				}
			}
	    }else{
			$output['status'] = 1;			
			$output['errors'] = "no business with this id";
		}

        echo json_encode($output);
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
	public function actionDeleteReview()
	{
		$parameters = array('user_id', 'auth_key', 'review_id');
		$output = array('status' => null, 'errors' => null);

		$model = Review::findOne($_POST['review_id']);

		if($model->delete()){
			$output['status'] = 0; //ok
		}else{
			$output['status'] = 1;
			$output['errors'] = $this->_getErrors($model); //saving problem
		}

        echo json_encode($output);
	}

	/**
	 * @api {post} /api/get-reviews Get all reviews for user or business
	 * @apiName GetReviews
	 * @apiGroup Business
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 * @apiParam {String} business_id_to_get Business's id (optional).
	 * @apiParam {String} user_id_to_get User's id (optional).
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} reviews reviews details.
	 */
	public function actionGetReviews()
	{
		$parameters = array('user_id', 'auth_key');
		$output = array('status' => null, 'errors' => null, 'reviews' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$conditions = [];
		if ( !empty($_POST['business_id_to_get']) ) {
			$conditions['business_id'] = $_POST['business_id_to_get'];
		}
		if ( !empty($_POST['user_id_to_get']) ) {
			$conditions['user_id'] = $_POST['user_id_to_get'];
		}
		$output['reviews'] = $this->_getReviews($conditions);
		$output['status'] = 0; //ok

        echo json_encode($output);
	}

	/**
	 * @api {post} /api/get-homescreen-reviews Get reviews for homescreen
	 * @apiName GetHomescreenReviews
	 * @apiGroup Business
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} reviews reviews details.
	 */
	public function actionGetHomescreenReviews()
	{
		$parameters = array('user_id', 'auth_key');
		$output = array('status' => null, 'errors' => null, 'reviews' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		// TODO set condition for this one
		$conditions = [];
		$output['reviews'] = $this->_getReviews($conditions);
		$output['status'] = 0; //ok

        echo json_encode($output);
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
	public function actionAddMedia()
	{
		$parameters = array('user_id', 'auth_key', 'business_id', 'type');
		$output = array('status' => null, 'errors' => null);

		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		if( !empty($_FILES['Media']) ){
			$media = new Media;
			$media->file = UploadedFile::getInstance($media,'file');
			if( isset($media->file) ){
				$media_type = $_POST['type'];
				$business_id = $_POST['business_id'];
				$file_path = 'uploads/'.$media_type.'/'.$business_id.'.'.pathinfo($media->file->name, PATHINFO_EXTENSION);

				// get unique name to the file
				while (file_exists($file_path)) {
					$path = pathinfo($file_path);
					$file_path = $path['dirname'].'/'.$path['filename'].'-.'.$path['extension'];
				}

				$media->url = $file_path;
				$media->type = $media_type;
				$media->user_id = $_POST['user_id'];
				$media->object_id = $business_id;
				$media->object_type = 'Business';

				if($media->save()){
					$media->file->saveAs($file_path);
					$output['status'] = 0; //ok
				}else{
					$output['status'] = 1;
					$output['errors'] = $this->_getErrors($media); //saving problem
				}
			}
		}else{
			$output['status'] = 1;
			$output['errors'] = 'no file input';
		}

    	echo json_encode($output);
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
	public function actionDeleteMedia()
	{
		$parameters = array('user_id', 'auth_key', 'media_id');
		$output = array('status' => null, 'errors' => null);

		$model = Media::findOne($_POST['media_id']);

		if(unlink($model->url) && $model->delete()){
			$output['status'] = 0; //ok
		}else{
			$output['status'] = 1;
			$output['errors'] = $this->_getErrors($model); //saving problem
		}

        echo json_encode($output);
	}

	/**
	 * @api {post} /api/get-media Get all media for user or business
	 * @apiName GetMedia
	 * @apiGroup Business
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 * @apiParam {String} business_id_to_get Business's id (optional).
	 * @apiParam {String} user_id_to_get User's id (optional).
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} media media details.
	 */
	public function actionGetMedia()
	{
		$parameters = array('user_id', 'auth_key');
		$output = array('status' => null, 'errors' => null, 'media' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$conditions = '';
		if ( !empty($_POST['business_id_to_get']) ) {
			$conditions .= "object_id = '".$_POST['business_id_to_get']."' AND ";
			$conditions .= "object_type = 'business' AND ";
			$conditions .= "type != 'business_image'";
		}else if ( !empty($_POST['user_id_to_get']) ) {
			$conditions .= "user_id = '".$_POST['user_id_to_get']."' AND ";
			$conditions .= "type != 'profile_photo'";
		}
		$output['media'] = $this->_getMedia($conditions);
		$output['status'] = 0; //ok

        echo json_encode($output);
	}

	/**
	 * @api {post} /api/get-homescreen-images Get images for homescreen
	 * @apiName GetHomescreenImages
	 * @apiGroup Business
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} images images details.
	 */
	public function actionGetHomescreenImages()
	{
		$parameters = array('user_id', 'auth_key');
		$output = array('status' => null, 'errors' => null, 'images' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$conditions['type'] = 'image';
		$conditions['object_type'] = 'business';
		$output['images'] = $this->_getMedia($conditions);
		$output['status'] = 0; //ok

        echo json_encode($output);
	}

	/***************************************/
	/************** Sponsors ***************/
	/***************************************/

	/**
	 * @api {post} /api/get-sponsors Get all sponsors
	 * @apiName GetSponsors
	 * @apiGroup Sponsors
	 *
	 * @apiParam {String} user_id User's id.
	 * @apiParam {String} auth_key User's auth key.
	 *
	 * @apiSuccess {String} status status code: 0 for OK, 1 for error.
	 * @apiSuccess {String} errors errors details if status = 1.
	 * @apiSuccess {Array} Sponsors List of Sponsors.
	 */
	public function actionGetSponsors()
	{
		$parameters = array('user_id', 'auth_key');
		$output = array('status' => null, 'errors' => null, 'countries' => null);

		// collect user input data
		if( !$this->_checkParameters($parameters) || !$this->_verifyUser() ){
			return;
		}

		$model = Sponsor::find()->all();

	    $sponsors = [];
		foreach ($model as $key => $sponsor) {
			$temp['id'] = $sponsor['id'];
			$temp['name'] = $sponsor['name'];
			$temp['description'] = $sponsor['description'];
			$temp['main_image'] = Url::base(true).'/'.$sponsor['main_image'];
			$temp['link'] = $sponsor['link'];
			$sponsors[] = $temp;
		}

		$output['sponsors'] = $sponsors;
		$output['status'] = 0; //ok

        echo json_encode($output);
	}

	/****************************************/
	/****************************************/
	/****************************************/

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError(){
		$output = array('status' => null, 'errors' => null);

	    $exception = Yii::$app->errorHandler->exception;

    	if ($exception !== null)
		{
			$output['status'] = $exception->statusCode;
	        $output['errors'] = $exception->getName() .' - '. $exception->getMessage();
		}

		echo json_encode($output);
	}

	private function _checkParameters($parameters){
		foreach ($parameters as $key => $parameter) {
			if(!isset($_POST[$parameter]) || $_POST[$parameter] == ""){
				echo json_encode(['status' => 1, 'errors' => 'inputs problem']);
				return false;
			}
		}

		return true;
	}

	private function _getErrors($model){
		$errors = '';
		foreach ($model->errors as $key => $element) {
			foreach ($element as $key => $error) {
				$errors .= $error.', ';
			}
		}

		return $errors;
	}

	private function _verifyUser(){
		$user = User::findOne($_POST['user_id']);

        if( isset($user) && $user->auth_key == $_POST['auth_key'] ){
			return true;
		}else{
			echo json_encode(['status' => 1, 'errors' => 'user not verified']);
			return false;
		}
	}

	private function _getUserData($user){
		$user_data['id'] = $user->id;
		$user_data['name'] = $user->name;
		$user_data['email'] = $user->email;
		$user_data['username'] = $user->username;
		$user_data['mobile'] = $user->mobile;
		$user_data['profile_photo'] = $this->_getUserPhotoUrl($user->profile_photo);
		$user_data['interests'] = $user->interestsList;

		$last_sent_friend_request = $this->_getLastFriendshipRequest($_POST['user_id'], $user->id);
		$user_data['last_sent_friend_request'] = $last_sent_friend_request != null ? $last_sent_friend_request->attributes : null ;

		$last_received_friend_request = $this->_getLastFriendshipRequest($user->id, $_POST['user_id']);
		$user_data['last_received_friend_request'] = $last_received_friend_request != null ? $last_received_friend_request->attributes : null ;

        return $user_data;
	}

	private function _getUserPhotoUrl($link){
		if(strpos($link, 'upload') === 0){
			return Url::base(true).'/'.$link;
		}else{
			return $link;
		}
	}

	private function _getLastFriendshipRequest($user_id, $friend_id){
		$model = Friendship::find()
			    ->where(['user_id' => $user_id, 'friend_id' => $friend_id])
			    ->orderBy(['id' => SORT_DESC])
			    ->one();

		return $model;
	}

	private function _getCategories($parent_id = null){
		$model = Category::find()
				    ->where(['parent_id' => $parent_id])
				    ->all();

	    $categories = [];
		foreach ($model as $key => $category) {
			$temp['id'] = $category['id'];
			$temp['name'] = $category['name'];
			$temp['description'] = $category['description'];
			$temp['main_image'] = Url::base(true).'/'.$category['main_image'];
			$temp['icon'] = Url::base(true).'/'.$category['icon'];
			$temp['badge'] = Url::base(true).'/'.$category['badge'];
			$categories[] = $temp;
		}

		return $categories;
	}

	private function _getBusinesses($conditions, $limit = null, $order = null, $and_conditions = null){
		$query = Business::find()
					->where($conditions)
				    ->with('category');

		if ($limit != null) {
			$query->limit($limit);
		}
		if ($order != null) {
			$query->orderBy($order);
		}

	    if (!empty($and_conditions)) {
		    $query->andWhere($and_conditions);
	    }

		$model = $query->all();

	    $businesses = [];
		foreach ($model as $key => $business) {
			$businesses[] = $this->_getBusinessesDataObject($business);
		}

		return $businesses;
	}

	private function _getBusinessesDataObject($model){
		$business['id'] = $model['id'];
		$business['name'] = $model['name'];
		$business['address'] = $model['address'];
		$business['country_id'] = $model['country_id'];
		$business['country'] = $model['country']->name;
		$business['city_id'] = $model['city_id'];
		$business['city'] = $model['city']->name;
		$business['phone'] = $model['phone'];
		$business['open_from'] = $model['open_from'];
		$business['open_to'] = $model['open_to'];
		$business['lat'] = $model['lat'];
		$business['lng'] = $model['lng'];
		$business['main_image'] = Url::base(true).'/'.$model['main_image'];
		$business['rating'] = $model['rating'];
		$business['price'] = $model['price'];
		$business['website'] = $model['website'];
		$business['fb_page'] = $model['fb_page'];
		$business['description'] = $model['description'];
		$business['featured'] = $model['featured'];
		$business['verified'] = $model['verified'];
		$business['show_in_home'] = $model['show_in_home'];
		$business['category_id'] = $model['category_id'];
		$business['category'] = $model['category']->attributes;
		$business['admin_id'] = $model['admin_id'];
		$business['flags'] = $model['flagsList'];
		$business['interests'] = $model['interestsList'];
		$business['no_of_views'] = count($model['views']);
		$business['no_of_checkins'] = count($model['checkins']);
		$business['no_of_reviews'] = count($model['reviews']);
		$business['last_checkin'] = null;
		if (isset($model['checkins'][0])){
			$last_checkin = $model['checkins'][0]->attributes;
			$last_checkin['user_data'] = $this->_getUserData($model['checkins'][0]->user);
			$business['last_checkin'] = $last_checkin;
		}
		$business['is_favorite'] = $this->_isSavedBusiness($_POST['user_id'], $business['id']);
		$business['created'] = $model['created'];
		$business['updated'] = $model['updated'];

		return $business;
	}

	private function _isSavedBusiness($user_id, $business_id){
		$model = SavedBusiness::find()
					->select('business_id')
				    ->where(['user_id' => $user_id, 'business_id' => $business_id])
				    ->one();
	    return !empty($model);
	}

	private function _addBusinessView($business_id, $user_id){
		$businessView = new BusinessView;
		$businessView->user_id = $_POST['user_id'];
		$businessView->business_id = $_POST['business_id'];

		if(!$businessView->save()){
			return $this->_getErrors($businessView); //saving problem
		}

		return 'done';
	}

	private function _getCheckins($conditions){
		$model = Checkin::find()
				    ->where($conditions)
			    	->orderBy(['id' => SORT_DESC])
				    ->with('user')
				    ->with('business')
					->all();

	    $checkins = [];
		foreach ($model as $key => $checkin) {
			$temp['id'] = $checkin['id'];
			$temp['text'] = $checkin['text'];
			$temp['rating'] = $checkin['rating'];
			$temp['user_id'] = $checkin['user_id'];
			$temp['user_name'] = $checkin->user->name;
			$temp['user_photo'] = $this->_getUserPhotoUrl($checkin->user->profile_photo);
			$temp['business_id'] = $checkin['business_id'];
			$temp['business_name'] = $checkin->business->name;
			$temp['business_photo'] = $this->_getUserPhotoUrl($checkin->business->main_image);
			$temp['created'] = $checkin['created'];
			$temp['updated'] = $checkin['updated'];

			$checkins[] = $temp;
		}

		return $checkins;
	}

	private function _getReviews($conditions){
		$model = Review::find()
				    ->where($conditions)
			    	->orderBy(['id' => SORT_DESC])
				    ->with('user')
					->all();

	    $reviews = [];
		foreach ($model as $key => $review) {
			$temp['id'] = $review['id'];
			$temp['text'] = $review['text'];
			$temp['rating'] = $review['rating'];
			$temp['user_id'] = $review['user_id'];
			$temp['user_name'] = $review->user->name;
			$temp['user_photo'] = $this->_getUserPhotoUrl($review->user->profile_photo);
			$temp['business_id'] = $review['business_id'];
			$temp['business_name'] = $review->business->name;
			$temp['business_photo'] = $this->_getUserPhotoUrl($review->business->main_image);
			$temp['created'] = $review['created'];
			$temp['updated'] = $review['updated'];
			$reviews[] = $temp;
		}

		return $reviews;
	}

	private function _calcRating($business_id){
		$checkins = Checkin::find()->where(['business_id' => $business_id]);
		$checkin_rating = $checkins->sum('rating');
		$checkin_no = count($checkins->all());

		$reviews = Review::find()->where(['business_id' => $business_id]);
		$review_rating = $reviews->sum('rating');
		$review_no = count($reviews->all());

		$total_rating = $checkin_rating+$review_rating;
		$total_no = $checkin_no+$review_no;
		$total_no = ($total_no==0)?1:$total_no;

		return strval(round($total_rating / $total_no));
	}

	private function _getMedia($conditions){
		$model = Media::find()
				    ->where($conditions)
			    	->orderBy(['id' => SORT_DESC])
				    ->with('user')
					->all();

	    $media = [];
		foreach ($model as $key => $value) {
			$temp['id'] = $value['id'];
			$temp['url'] = Url::base(true).'/'.$value['url'];
			$temp['type'] = $value['type'];
			$temp['object_id'] = $value['object_id'];
			$temp['object_type'] = $value['object_type'];
			$temp['created'] = $value['created'];
			$temp['updated'] = $value['updated'];
			$temp['user_id'] = $value['user_id'];
			$temp['user_name'] = $value->user->name;
			$temp['user_photo'] = $this->_getUserPhotoUrl($value->user->profile_photo);
			$media[] = $temp;
		}

		return $media;
	}
}
