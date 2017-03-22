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

class ApiBaseController extends Controller
{
    var $output = ['status' => 0, 'errors' => null];
    var $logged_user = ['id'=>''];
    var $pagination = [
        'page_no' => null,
        'no_per_page' => 20,
        'total_pages_no' => null        
    ];
    var $adminEmail = 'admin@myblabber.com';

    public function beforeAction($action)
    {
        // TODO check if this needed on live server
        $this->enableCsrfValidation = false;

        $guest_actions = ['error', 'is-unique-username', 'sign-up', 'sign-in-fb', 'sign-in', 'recover-password', 
            'get-profile', 'get-categories', 'get-sub-categories', 'get-countries', 'get-cities', 'get-flags', 'get-interests', 
            'get-homescreen-businesses', 'get-businesses', 'search-businesses', 'search-businesses-by-type', 'get-business-data', 
            'get-checkins', 'get-reviews', 'get-homescreen-reviews', 'get-media', 'get-homescreen-images', 'get-sponsors'
        ];

        if( !$this->_verifyUserAndSetID() && !in_array($action->id, $guest_actions) ){
            throw new HttpException(200, 'no valid user credentials input');
        }

        return parent::beforeAction($action);
    }

    public function runAction($id, $params = [])
    {
        // Extract the params from the request and bind them to params
        $params = \yii\helpers\BaseArrayHelper::merge(Yii::$app->getRequest()->getBodyParams(), $params);

        if( !empty($params['page']) ){
            $this->pagination['page_no'] = intval($params['page']);
        }
        
        return parent::runAction($id, $params);
    }

    public function afterAction($action, $result)
    {
        if( $this->pagination['page_no'] !== null ){
            $this->output['pagination'] = [
                'page_no' => $this->pagination['page_no'],
                'total_pages_no' => $this->pagination['total_pages_no'],
            ];
        }
        return parent::afterAction($action, json_encode($this->output));
    }

    /**
     * This is the action to handle external exceptions.
     */
    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;

        if ($exception !== null)
        {
            $this->output['status'] = 1;
            $this->output['errors'] = $exception->getMessage();
        }
    }
    
    /****************************************/
    /****************************************/
    /****************************************/

    protected function _addOutputs($variables)
    {
        foreach ($variables as $variable) {
            $this->output[$variable] = null;
        }
    }

    protected function _getErrors($model)
    {
        $errors = '';
        foreach ($model->errors as $key => $element) {
            foreach ($element as $key => $error) {
                $errors .= $error.', ';
            }
        }

        return $errors;
    }

    protected function _verifyUserAndSetID()
    {
        if( empty($_POST['user_id']) || empty($_POST['auth_key']) ){
            return false;
        }

        $user = User::findOne($_POST['user_id']);

        if( isset($user) && $user->auth_key == $_POST['auth_key'] ){
            $this->logged_user = $user;
            return true;
        }else{
            return false;
        }
    }

    protected function _login($email, $password, $firebase_token)
    {
        $user = User::login($email, $password, $firebase_token);
        if( $user !== null ){
            if( $user->approved === 0 ){ //false
                throw new HttpException(200, 'your account not approved yet, we will update you by email when it\'s done');
            }
            if( $user->blocked === 1 ){ //true
                throw new HttpException(200, 'your account is blocked, please contact the support');
            }

            $this->output['user_data'] = $this->_getUserData($user);
            $this->output['auth_key'] = $user->auth_key;
        }else{
            throw new HttpException(200, 'login problem');
        }
    }

    protected function _getModelWithPagination($query)
    {
        if ($this->pagination['page_no'] === null) {
            return $query->all();
        }

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this->pagination['no_per_page'],
                'page' => $this->pagination['page_no']-1 // to make it zero based
            ],
        ]);

        $model = $provider->getModels();
        $this->pagination['total_pages_no'] = $provider->pagination->pageCount;

        return $model;
    }

    protected function _validateUsername($username)
    {
        if( strpos($username, ' ') !== false ){
            throw new HttpException(200, 'username can\'t contains spaces');
        }

        $model = User::find()
                ->where(['username' => $username])
                ->one();
        if (!empty($model)) {
            throw new HttpException(200, 'this username already taken');
        }
    }

    protected function _getUserData($user)
    {
        if(empty($user)){
            return null;
        }
        
        $user_data['id'] = $user->id;
        $user_data['name'] = $user->name;
        $user_data['email'] = $user->email;
        $user_data['username'] = $user->username;
        $user_data['type'] = $user->role;
        $user_data['mobile'] = $user->mobile;
        $user_data['profile_photo'] = $this->_getUserPhotoUrl($user->profile_photo);
        $user_data['interests'] = $user->interestsList;

        $last_sent_friend_request = $this->_getLastFriendshipRequest($this->logged_user['id'], $user->id);
        $user_data['last_sent_friend_request'] = $last_sent_friend_request !== null ? $last_sent_friend_request->attributes : null ;

        $last_received_friend_request = $this->_getLastFriendshipRequest($user->id, $this->logged_user['id']);
        $user_data['last_received_friend_request'] = $last_received_friend_request !== null ? $last_received_friend_request->attributes : null ;

        return $user_data;
    }

    protected function _getUserPhotoUrl($link)
    {
        if(strpos($link, 'upload') === 0){
            return Url::base(true).'/'.$link;
        }else{
            return $link;
        }
    }

    protected function _getLastFriendshipRequest($user_id, $friend_id)
    {
        $model = Friendship::find()
                ->where(['user_id' => $user_id, 'friend_id' => $friend_id])
                ->orderBy(['id' => SORT_DESC])
                ->one();

        return $model;
    }

    protected function _getCategories($parent_id = null)
    {
        $query = Category::find()
                    ->where(['parent_id' => $parent_id]);
        $model = $this->_getModelWithPagination($query);

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

    protected function _getBusinesses($conditions, $country_id = null, $order = null, $lat_lng = null, $andConditions = null)
    {
        $query = Business::find()
                    ->where($conditions)
                    ->with('category');

        if ($country_id !== null) {
            $query->andWhere(['country_id' => $country_id]);
        }

        if ($order !== null) {
            $order = ['featured' => SORT_DESC] + $order;
        }else{
            $order = ['featured' => SORT_DESC];
        }

        if (!empty($lat_lng)) {
            $lat = $lat_lng[0];
            $lng = $lat_lng[1];

            $query
                ->select(['*', '( 6371 * acos( cos( radians('.$lat.') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians('.$lng.') ) + sin( radians('.$lat.') ) * sin( radians( lat ) ) ) ) AS distance'])
                ->having('distance < 100');
            $order += ['distance' => SORT_ASC];
        }

        if (!empty($andConditions)) {
            $query->andWhere($andConditions);
        }

        $query->orderBy($order);
        $model = $this->_getModelWithPagination($query);

        $businesses = [];
        foreach ($model as $key => $business) {
            $businesses[] = $this->_getBusinessesDataObject($business);
        }

        return $businesses;
    }

    protected function _getBusinessesDataObject($model)
    {
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
        $business['is_favorite'] = $this->_isSavedBusiness($this->logged_user['id'], $business['id']);
        $business['distance'] = $model['distance'];
        $business['created'] = $model['created'];
        $business['updated'] = $model['updated'];

        return $business;
    }

    protected function _isSavedBusiness($user_id, $business_id)
    {
        $model = SavedBusiness::find()
                    ->select('business_id')
                    ->where(['user_id' => $user_id, 'business_id' => $business_id])
                    ->one();
        return !empty($model);
    }

    protected function _addBusinessView($business_id, $user_id)
    {
        $businessView = new BusinessView;
        $businessView->user_id = $user_id;
        $businessView->business_id = $business_id;

        if(!$businessView->save()){
            return $this->_getErrors($businessView); //saving problem
        }

        return 'done';
    }

    protected function _getCheckins($conditions)
    {
        $query = Checkin::find()
                    ->where($conditions)
                    ->orderBy(['id' => SORT_DESC])
                    ->with('user')
                    ->with('business');
        $model = $this->_getModelWithPagination($query);

        $checkins = [];
        foreach ($model as $key => $checkin) {
            if (empty($checkin->user) || empty($checkin->business)) {
                continue;
            }

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

    protected function _getReviews($conditions, $country_id = null)
    {
        $query = Review::find()
                    ->where($conditions)
                    ->orderBy(['id' => SORT_DESC])
                    ->with('user');

        if ($country_id !== null) {
            $query
                ->joinWith('business')
                ->andWhere(['business.country_id' => $country_id]);
        }

        $model = $this->_getModelWithPagination($query);

        $reviews = [];
        foreach ($model as $key => $review) {
            if (empty($review->user) || empty($review->business)) {
                continue;
            }

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

    protected function _calcRating($business_id)
    {
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

    protected function _getMedia($conditions, $country_id = null)
    {
        $query = Media::find()
                    ->where($conditions)
                    ->orderBy(['id' => SORT_DESC])
                    ->with('user');

        if ($country_id !== null) {
            $query
                ->leftJoin('business', '`business`.`id` = `media`.`object_id`')
                ->andWhere(['media.object_type' => 'Business'])
                ->andWhere(['business.country_id' => $country_id]);
        }

        $model = $this->_getModelWithPagination($query);

        $media = [];
        foreach ($model as $key => $value) {
            if (empty($value->user)) {
                continue;
            }

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

    protected function _uploadPhoto($model_id, $object_type, $media_type, $model = null, $image_name = null, $user_id = null)
    {
        $media = new Media;
        $media->file = UploadedFile::getInstance($media, 'file');
        if( isset($media->file) ){
            $file_path = 'uploads/'.$media_type.'/'.$model_id.'.'.pathinfo($media->file->name, PATHINFO_EXTENSION);
            while( file_exists($file_path) ){
                $file_path = preg_replace('/(\.[^.]+)$/', sprintf('%s$1', '-'), $file_path);
            }

            $media->url = $file_path;
            $media->type = $media_type;
            $media->user_id = empty($user_id) ? $this->logged_user['id'] : $user_id;
            $media->object_id = $model_id;
            $media->object_type = $object_type;

            if($media->save()){
                $media->file->saveAs($file_path);

                if( !empty($model) && !empty($image_name) ){
                    $model->$image_name = $file_path;

                    if(!$model->save()){
                        throw new HttpException(200, $this->_getErrors($user));
                    }
                }
            }else{
                throw new HttpException(200, $this->_getErrors($media));
            }
        }
    }

    protected function _sendNotification($firebase_token, $title, $body, $data = null)
    {
        $server_key = 'AAAAqGzljtM:APA91bGRz5hiS-IyHW6HPnK-yrIJRFkzqP85PzByvWlI0YYCfLF_NH94Rybgg31bDs2d0EfxzD_zYmb4fNwSH1x6HOXFY_a-solzKgn7xiSi336sUYQjrXZuCWrk29ioaHBZLL7p0LfO';
        $postData = [
            'to' => $firebase_token,
            'priority' => 'high',
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
                'tag' => $data, // TODO: remove this one asap
            ],
            'data' => $data,
        ];

        $ch = curl_init('https://fcm.googleapis.com/fcm/send');
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Key='.$server_key,
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => json_encode($postData)
        ));

        $response = curl_exec($ch);
        if($response === FALSE){
            echo curl_error($ch);
        }
        
        // var_dump($firebase_token, $title, $body, $data, $response);
        return json_decode($response, TRUE);
    }
}
