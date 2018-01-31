<?php

namespace app\controllers;

use app\components\Translation;
use app\models\Area;
use app\models\Blog;
use app\models\Branch;
use app\models\Business;
use app\models\BusinessView;
use app\models\Category;
use app\models\Checkin;
use app\models\City;
use app\models\Comment;
use app\models\Country;
use app\models\Follow;
use app\models\Media;
use app\models\Notification;
use app\models\Poll;
use app\models\Option;
use app\models\Vote;
use app\models\Reaction;
use app\models\Review;
use app\models\SavedBusiness;
use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\UploadedFile;

class ApiBaseController extends Controller
{
    public $output = [
        'status' => 0,
        'errors' => null
    ];
    public $logged_user = [
        'id' => '',
    ];
    public $lang = '';
    public $pagination = [
        'page_no' => null,
        'no_per_page' => 10,
        'total_pages_no' => null,
        'total_records_no' => null
    ];
    public $adminEmail = 'info@myblabber.com';

    // TODO: remove on production
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['POST', 'GET'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => false,
            ],
        ];
        return $behaviors;
    }

    public function runAction($id, $params = [])
    {
        // Extract the params from the request and bind them to params
        $params = \yii\helpers\BaseArrayHelper::merge(Yii::$app->getRequest()->getBodyParams(), $params);

        return parent::runAction($id, $params);
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        $guest_actions = [
            'error', 'sign-up', 'sign-in-fb', 'sign-in', 'recover-password',
            'get-profile', 'get-categories', 'get-sub-categories', 'get-countries', 'get-cities', 'get-areas', 'get-location', 'get-flags',
            'get-interests', 'get-homescreen-businesses', 'get-businesses', 'get-branches', 'search-businesses',
            'search-businesses-by-type', 'get-business-data', 'get-branch-data', 'get-checkins', 'get-reviews', 'get-homescreen-reviews',
            'get-media', 'get-media-by-ids', 'get-homescreen-images', 'get-review', 'get-comments', 'get-reactions',
            'get-sponsors', 'get-blogs', 'get-blog', 'get-polls', 'migrate', 'get-business-recommendations'
        ];

        if (!$this->_verifyUserAndSetID() && !in_array($action->id, $guest_actions)) {
            throw new HttpException(200, 'authentication error, please login again');
        }

        $page = !empty(Yii::$app->request->get('page'))
        ? Yii::$app->request->get('page')
        : Yii::$app->request->post('page');
        if (!empty($page)) {
            $this->pagination['page_no'] = intval($page);
        }

        if (!empty(Yii::$app->request->post('lang')) && Yii::$app->request->post('lang') === 'Ar') {
            $this->lang = Yii::$app->request->post('lang');
        } else if (isset($this->logged_user['lang'])) {
            $this->lang = $this->logged_user['lang'];
        }

        return parent::beforeAction($action);
    }

    public function afterAction($action, $result)
    {
        if ($this->pagination['page_no'] !== null) {
            $this->output['pagination'] = [
                'page_no' => $this->pagination['page_no'],
                'total_pages_no' => $this->pagination['total_pages_no'],
                'total_records_no' => $this->pagination['total_records_no']
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

        if ($exception !== null) {
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
        foreach ($model->errors as $element) {
            foreach ($element as $error) {
                $errors .= $error . ', ';
            }
        }

        return $errors;
    }

    protected function _verifyUserAndSetID()
    {
        $request = Yii::$app->request;

        if (empty($request->post('user_id')) || empty($request->post('auth_key'))) {
            return false;
        }

        $user = User::findOne($request->post('user_id'));

        if (isset($user) && $user->validateAuthKey($request->post('auth_key'))) {
            $this->logged_user = $user->attributes;
            return true;
        } else {
            return false;
        }
    }

    protected function _login($email, $password, $device_IMEI, $firebase_token, $is_facebook)
    {
        $user = User::login($email, $password, $device_IMEI, $firebase_token, $is_facebook);

        if ($user === null) {
            throw new HttpException(200, 'login problem');
        }

        if ($user->approved === 0) {
            //false
            throw new HttpException(200, 'your account not approved yet, we will update you by email when it\'s done');
        }
        if ($user->blocked === 1) {
            //true
            throw new HttpException(200, 'your account is blocked, please contact the support');
        }

        $this->output['user_data'] = $this->_getUserData($user);
        $this->output['auth_key'] = $user->auth_key;
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
                'page' => $this->pagination['page_no'] - 1, // to make it zero based
            ],
        ]);

        $model = $provider->getModels();
        $this->pagination['total_pages_no'] = $provider->pagination->pageCount;
        $this->pagination['total_records_no'] = (int) $query->count();

        return $model;
    }

    protected function _getUserData($model)
    {
        if (empty($model)) {
            return null;
        }

        $last_sent_follow = $this->_isFollowing($this->logged_user['id'], $model->id);
        $last_received_follow = $this->_isFollowing($model->id, $this->logged_user['id']);

        $user['id'] = $model->id;
        $user['name'] = $model->name;
        $user['profile_photo'] = $this->_getUserPhotoUrl($model->profile_photo);
        $user['private'] = $model->private;
        $user['last_sent_follow'] = $last_sent_follow !== null ? $last_sent_follow->attributes : null;
        $user['last_received_follow'] = $last_received_follow !== null ? $last_received_follow->attributes : null;
        $user['is_adult_and_smoker'] = $model->is_adult_and_smoker;
        $user['lang'] = $model->lang === '' ? 'En' : $model->lang;

        if (!($model->private === 1 && 
            ($last_sent_follow === null || $last_sent_follow->status !== '1') && 
            ($last_received_follow === null || $last_received_follow->status !== '1'))) {
            $user['email'] = $model->email;
            $user['type'] = $model->role;
            $user['mobile'] = $model->mobile;
            $user['categories'] = $model['categoryList'.$this->lang];
        }

        return $user;
    }

    protected function _getUserMinimalData($model)
    {
        $user['id'] = $model->id;
        $user['name'] = $model->name;
        $user['profile_photo'] = $this->_getUserPhotoUrl($model->profile_photo);

        return $user;
    }

    protected function _getUserPhotoUrl($link)
    {
        if (strpos($link, 'upload') === 0) {
            return Url::base(true) . '/' . $link;
        } else {
            return $link;
        }
    }

    protected function _isFollowing($user_id, $receiver_id)
    {
        $model = Follow::find()
            ->where(['user_id' => $user_id, 'receiver_id' => $receiver_id])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        return $model;
    }

    protected function _getCategories($parent_id = null)
    {
        $query = Category::find()
            ->where(['parent_id' => $parent_id])
            ->orderBy(['order' => SORT_ASC]);
        $model = $this->_getModelWithPagination($query);

        $categories = [];
        foreach ($model as $key => $category) {
            $temp['id'] = $category['id'];
            $temp['name'] = $category['name'.$this->lang];
            $temp['description'] = $category['description'.$this->lang];
            $temp['main_image'] = Url::base(true) . '/' . $category['main_image'];
            $temp['icon'] = Url::base(true) . '/' . $category['icon'];
            $temp['badge'] = Url::base(true) . '/' . $category['badge'];
            $temp['color'] = $category['color'];
            $temp['business_count'] = (int) Business::find()->where(['category_id' => $category['id']])->count();
            $categories[] = $temp;
        }

        return $categories;
    }

    protected function _getAllCategoryTreeIds($category_id)
    {
        $categoryTree[] = $category_id;
        $categories = Category::find()
            ->where(['parent_id' => $category_id])
            ->all();
        foreach ($categories as $category) {
            $categoryTree = array_merge($categoryTree, $this->_getAllCategoryTreeIds($category->id));
        }

        return $categoryTree;
    }

    protected function _getBusinesses($conditions, $area_id = null, $order = null, $lat_lng = null, $andConditions = null)
    {
        $query = Business::find()
            ->innerJoin('branch', 'business_v2.id = branch.business_id')
            ->leftJoin('area', 'area.id = branch.area_id')
            ->leftJoin('city', 'city.id = branch.city_id')
            ->leftJoin('country', 'country.id = branch.country_id')
            ->where($conditions)
            ->groupBy('business_v2.id')
            ->with(['category', 'branches']);

        $areaQuery = 'branch.area_id is not null and branch.area_id = ' . $area_id;
        $area = Area::findOne($area_id);
        if ($area) {
            $areaQuery .= ' or branch.city_id is not null and branch.city_id = ' . $area->city_id;
            $city = City::findOne($area->city_id);
            if ($city) $areaQuery .= ' or branch.country_id is not null and branch.country_id = ' . $city->country_id;
        }
        $query->andWhere($areaQuery);

        if ($order !== null) {
            $order = ['featured' => SORT_DESC] + $order;
        } else {
            $order = ['featured' => SORT_DESC];
        }

        if (!empty($lat_lng)) {
            $lat = $lat_lng[0];
            $lng = $lat_lng[1];

            $query
                ->select(['business_v2.*', '( 6371 * acos( cos( radians(' . $lat . ') ) * cos( radians( branch.lat ) ) * cos( radians( branch.lng ) - radians(' . $lng . ') ) + sin( radians(' . $lat . ') ) * sin( radians( branch.lat ) ) ) ) AS distance']);
//                ->having('distance < 100');
            $order += ['distance' => SORT_ASC];
        }

        if (empty($andConditions)) {
            $andConditions[] = 'and';
        }
        $andConditions[] = ['business_v2.approved' => true];
        $query->andWhere($andConditions);

        $query->orderBy($order);
        $query->groupBy('business_v2.id');
        $model = $this->_getModelWithPagination($query);

        $businesses = [];
        foreach ($model as $key => $business) {
            $businesses[] = $this->_getBusinessData($business);
        }

        return $businesses;
    }

    protected function _getBusinessData($model)
    {
        $business['id'] = $model['id'];
        $business['name'] = $model['name'.$this->lang];
        $business['phone'] = $model['phone'];
        $business['main_image'] = Url::base(true) . '/' . $model['main_image'];
        $business['rating'] = $model['rating'];
        $business['price'] = $model['price'];
        $business['website'] = $model['website'];
        $business['fb_page'] = $model['fb_page'];
        $business['description'] = $model['description'.$this->lang];
        $business['featured'] = $model['featured'];
        $business['verified'] = $model['verified'];
        $business['show_in_home'] = $model['show_in_home'];
        $business['category_id'] = $model['category_id'];
        if (isset($model['category'])) {
            $business['category'] = $model['category']->attributes;
            $business['category']['name'] = $model['category']['name' . $this->lang];
            if (isset($model['category']->topParent)) {
                $business['top_category'] = $model['category']->topParent->attributes;
                $business['top_category']['name'] = $model['category']->topParent['name' . $this->lang];
            } else {
                $business['top_category'] = $model['category']->attributes;
                $business['top_category']['name'] = $model['category']['name' . $this->lang];
            }
        } else {
            $business['category'] = null;
            $business['top_category'] = null;
        }
        $business['admin_id'] = $model['admin_id'];
        $business['interests'] = $model['interestList'.$this->lang];
        $business['no_of_views'] = count($model['views']);
        $business['last_checkin'] = null;
        if (isset($model['checkins'][0])) {
            $last_checkin = $model['checkins'][0]->attributes;
            $last_checkin['user_data'] = $this->_getUserData($model['checkins'][0]->user);
            $business['last_checkin'] = $last_checkin;
        }
        $business['is_favorite'] = $this->_isSavedBusiness($this->logged_user['id'], $business['id']);
        $business['correct_votes_percentage'] = $this->_correctVotesPercentage($business['id']);
        $business['branch'] = empty($model['branches']) ? null : $model['branches'][0]->attributes;
        $business['created'] = $model['created'];
        $business['updated'] = $model['updated'];

        return $business;
    }

    protected function _getBusinessMinimalData($model)
    {
        $business['id'] = $model['id'];
        $business['name'] = $model['name'.$this->lang];
        $business['main_image'] = Url::base(true) . '/' . $model['main_image'];
        $business['rating'] = $model['rating'];
        $business['no_of_reviews'] = count($model['reviews']);

        return $business;
    }

    protected function _getBranches($conditions, $lat_lng = null)
    {
        $query = Branch::find()
            ->where($conditions);

        $order = ['name' => SORT_ASC];

        if (!empty($lat_lng)) {
            $lat = $lat_lng[0];
            $lng = $lat_lng[1];

            $query
                ->select(['*', '( 6371 * acos( cos( radians(' . $lat . ') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(' . $lng . ') ) + sin( radians(' . $lat . ') ) * sin( radians( lat ) ) ) ) AS distance']);
//                ->having('distance < 100');
            $order += ['distance' => SORT_ASC];
        }

        $query->orderBy($order);
        $model = $this->_getModelWithPagination($query);

        $branches = [];
        foreach ($model as $key => $branch) {
            $branches[] = $this->_getBranchData($branch);
        }

        return $branches;
    }

    protected function _getBranchData($model)
    {
        $branch['id'] = $model['id'];
        // $branch['business_id'] = $model['business_id'];
        $branch['name'] = $model['name'.$this->lang];
        $branch['address'] = $model['address'.$this->lang];
        $branch['country'] = empty($model['country']) ? null : $model['country']->attributes;
        $branch['city'] = empty($model['city']) ? null : $model['city']->attributes;
        $branch['area'] = empty($model['area']) ? null : $model['area']->attributes;
        $branch['phone'] = $model['phone'];
        $branch['operation_hours'] = $model['operation_hours'];
        $branch['lat'] = $model['lat'];
        $branch['lng'] = $model['lng'];
        $branch['approved'] = $model['approved'];
        $branch['is_reservable'] = $model['is_reservable'];
        $branch['flags'] = $model['flagList'.$this->lang];
        $branch['is_open'] = $model['isOpen'];
        $branch['created'] = $model['created'];
        $branch['updated'] = $model['updated'];
        return $branch;
    }

    protected function _getBranchMinimalData($model)
    {
        $branch['id'] = $model['id'];
        $branch['name'] = $model['name' . $this->lang];
        $branch['address'] = $model['address'.$this->lang];
        $branch['city'] = $model['city'];
        $branch['phone'] = $model['phone'];
        $branch['lat'] = $model['lat'];
        $branch['lng'] = $model['lng'];
        $branch['is_open'] = $model['isOpen'];

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

        if (!$businessView->save()) {
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
            $temp['created'] = $checkin['created'];
            $temp['updated'] = $checkin['updated'];

            $temp['user'] = $this->_getUserMinimalData($checkin->user);
            $temp['business'] = $this->_getBusinessMinimalData($checkin->business);

            $checkins[] = $temp;
        }

        return $checkins;
    }

    protected function _getReviews($conditions, $area_id = null)
    {
        $query = Review::find()
            ->where($conditions)
            ->orderBy(['id' => SORT_DESC])
            ->with('user');

        if ($area_id !== null) {
            $query
                ->joinWith('business')
                ->andWhere(['branch.area_id' => $area_id]);
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
            $temp['created'] = $review['created'];
            $temp['updated'] = $review['updated'];

            $temp['user'] = $this->_getUserMinimalData($review->user);
            $temp['business'] = $this->_getBusinessMinimalData($review->business);

            $temp['likes'] = count($review['likes']);
            $temp['dislikes'] = count($review['dislikes']);

            $temp['added_reaction'] = $this->_addedReaction($this->logged_user['id'], $review['id'], 'review');

            $reviews[] = $temp;
        }

        return $reviews;
    }

    protected function _getComments($conditions)
    {
        $query = Comment::find()
            ->where($conditions)
            ->orderBy(['id' => SORT_DESC]);

        $model = $this->_getModelWithPagination($query);

        $comments = [];
        foreach ($model as $key => $comment) {
            $temp['id'] = $comment['id'];
            $temp['text'] = $comment['text'];
            $temp['created'] = $comment['created'];
            $temp['updated'] = $comment['updated'];

            $temp['user'] = $this->_getUserMinimalData($comment->user);

            $temp['mentions'] = array();
            if (preg_match_all('/(?<!\w)@(\w+)/', $comment->text, $matches)) {
                $users = User::findAll($matches[1]);
                foreach ($users as $user) {
                    $temp['mentions'][] = $this->_getUserMinimalData($user);
                }
            }

            if (!empty($comment->business_identity)) {
                $model = Business::findOne($comment->business_identity);
                if (!empty($model)) {
                    $temp['business_data'] = $this->_getBusinessMinimalData($model);
                }
            }

            $temp['likes'] = count($comment['likes']);
            $temp['dislikes'] = count($comment['dislikes']);

            $temp['added_reaction'] = $this->_addedReaction($this->logged_user['id'], $comment['id'], 'comment');

            $comments[] = $temp;
        }

        return $comments;
    }

    protected function _getReactions($conditions)
    {
        $query = Reaction::find()
            ->where($conditions)
            ->orderBy(['id' => SORT_DESC]);

        $model = $this->_getModelWithPagination($query);

        $reactions = [];
        foreach ($model as $key => $reaction) {
            $temp['id'] = $reaction['id'];
            $temp['type'] = $reaction['type'];
            $temp['object_id'] = $reaction['object_id'];
            $temp['object_type'] = $reaction['object_type'];
            $temp['created'] = $reaction['created'];
            $temp['updated'] = $reaction['updated'];

            $temp['user'] = $this->_getUserMinimalData($reaction->user);

            if (!empty($reaction->business_identity)) {
                $model = Business::findOne($reaction->business_identity);
                if (!empty($model)) {
                    $temp['business_data'] = $this->_getBusinessMinimalData($model);
                }
            }

            $reactions[] = $temp;
        }

        return $reactions;
    }

    protected function _addedReaction($user_id, $object_id, $object_type)
    {
        $model = Reaction::find()
            ->select(['id','type'])
            ->where([
                'user_id' => $user_id,
                'object_id' => $object_id,
                'object_type' => $object_type,
            ])
            ->one();
        if (!empty($model)) {
            $result['id'] = $model->id;
            $result['type'] = $model->type;
        }
        return empty($model)? null : $result;
    }

    protected function _calcRating($business_id)
    {
        $checkins = Checkin::find()->where(['business_id' => $business_id]);
        $checkin_rating = $checkins->sum('rating');
        $checkin_no = count($checkins->all());

        $reviews = Review::find()->where(['business_id' => $business_id]);
        $review_rating = $reviews->sum('rating');
        $review_no = count($reviews->all());

        $medias = Media::find()->where(['object_type' => 'Business', 'object_id' => $business_id]);
        $media_rating = $medias->sum('rating');
        $media_no = count($medias->all());

        $total_rating = $checkin_rating + $review_rating + $media_rating;
        $total_no = $checkin_no + $review_no + $media_no;

        $total_no = ($total_no == 0) ? 1 : $total_no;

        return strval(round($total_rating / $total_no));
    }

    protected function _getMedia($conditions, $area_id = null)
    {
        $query = Media::find()
            ->where($conditions)
            ->orderBy(['id' => SORT_DESC])
            ->with('user');

        if ($area_id !== null) {
            $query
                ->leftJoin('business', '`business`.`id` = `media`.`object_id`')
                ->andWhere(['media.object_type' => 'Business'])
                ->andWhere(['branch.area_id' => $area_id]);
        }

        $model = $this->_getModelWithPagination($query);

        $media = [];
        foreach ($model as $key => $value) {
            if (empty($value->user)) {
                continue;
            }

            $temp['id'] = $value['id'];
            $temp['url'] = Url::base(true) . '/' . $value['url'];
            $temp['type'] = $value['type'];
            $temp['object_id'] = $value['object_id'];
            $temp['object_type'] = $value['object_type'];
            if ($value['type'] === 'product' || $value['type'] === 'menu') {
                $temp['section'] = $value['section'];
                $temp['sectionAr'] = $value['sectionAr'];
                $temp['title'] = $value['title'];
                $temp['titleAr'] = $value['titleAr'];
                $temp['caption'] = $value['caption'];
                $temp['captionAr'] = $value['captionAr'];
                $temp['currency'] = $value['currency'];
                $temp['currencyAr'] = $value['currencyAr'];
                $temp['price'] = $value['price'];
                $temp['discount'] = $value['discount'];
            }
            $temp['created'] = $value['created'];
            $temp['updated'] = $value['updated'];

            $temp['user'] = $this->_getUserMinimalData($value->user);

            if ($value['object_type'] === 'Business') {
                $business = Business::findOne($value['object_id']);
                if (empty($business)) {
                    continue;
                }

                $temp['business'] = $this->_getBusinessMinimalData($business);
            }

            $temp['likes'] = count($value['likes']);
            $temp['dislikes'] = count($value['dislikes']);

            $temp['added_reaction'] = $this->_addedReaction($this->logged_user['id'], $value['id'], 'media');

            $media[] = $temp;
        }

        return $media;
    }

    protected function _getBlogs()
    {
        $query = Blog::find()
            ->orderBy(['id' => SORT_DESC]);

        $model = $this->_getModelWithPagination($query);

        $blogs = [];
        foreach ($model as $key => $value) {
            $temp['id'] = $value['id'];
            $temp['title'] = $value['title'];
            $temp['image'] = $value['image'];
            $temp['content'] = htmlentities($value['content']);
            $temp['created'] = $value['created'];
            $temp['updated'] = $value['updated'];
            $blogs[] = $temp;
        }
        return $blogs;
    }

    protected function _getPolls($conditions)
    {
        $query = Poll::find()
            ->where($conditions)
            ->orderBy(['id' => SORT_ASC]);

        $model = $this->_getModelWithPagination($query);

        $polls = [];
        foreach ($model as $key => $value) {
            $temp['id'] = $value['id'];
            $temp['title'] = $value['title'.$this->lang];
            $temp['type'] = $value['type'];
            $temp['created'] = $value['created'];
            $temp['updated'] = $value['updated'];
            $options_model = Option::find()->where(['poll_id' => $value['id']])->orderBy(['option' => SORT_ASC])->all();
            $options = [];
            foreach ($options_model as $option) {
                $options[] = [
                    'id' => $option->id,
                    'option' => $option['option'.$this->lang],
                    'votes' => count(Vote::find()->where(['option_id' => $option->id, 'business_id' => $conditions['business_id']])->all()),
                    'added_vote' => $this->_addedVote($this->logged_user['id'], $option->id, $conditions['business_id'])
                ];
            }
            $temp['options'] = $options;

            $polls[] = $temp;
        }

        return $polls;
    }

    protected function _addedVote($user_id, $option_id, $business_id)
    {
        $model = Vote::find()
            ->where([
                'user_id' => $user_id,
                'option_id' => $option_id,
                'business_id' => $business_id,
            ])
            ->one();
        return !empty($model);
    }

    protected function _correctVotesPercentage($business_id) {
        $correct = (int) Option::find()
            ->select('*')
            ->innerJoin('poll', 'poll.id = poll_id')
            ->innerJoin('vote', 'option.id = option_id')
            ->andWhere(['correct' => true, 'vote.business_id' => $business_id])
            ->count();
        $total = (int) Option::find()
            ->select('*')
            ->innerJoin('poll', 'poll.id = poll_id')
            ->innerJoin('vote', 'option.id = option_id')
            ->andWhere(['vote.business_id' => $business_id])
            ->count();

        $total = $total == 0 ? 1 : $total;
        return round(100 * $correct / $total);
    }

    protected function _uploadFile($model_id, $object_type, $media_type, $model = null, $image_name = null, $user_id = null, $caption = null, $rating = null)
    {
        $media = new Media;
        $media->file = UploadedFile::getInstance($media, 'file');
        if (isset($media->file)) {
            $file_path = 'uploads/' . $media_type . '/' . $model_id . '.' . pathinfo($media->file->name, PATHINFO_EXTENSION);
            while (file_exists($file_path)) {
                $file_path = preg_replace('/(\.[^.]+)$/', sprintf('%s$1', '-'), $file_path);
            }

            $media->url = $file_path;
            $media->type = $media_type;
            $media->user_id = empty($user_id) ? $this->logged_user['id'] : $user_id;
            $media->object_id = $model_id;
            $media->object_type = $object_type;

            if (!empty($caption)) {
                $media->caption = $caption;
            }

            if (!empty($rating)) {
                $media->rating = $rating;
            }

            if (!$media->save()) {
                throw new HttpException(200, $this->_getErrors($media));
            }

            $media->file->saveAs($file_path);

            if (!empty($model) && !empty($image_name)) {
                $model->$image_name = $file_path;

                if (!$model->save()) {
                    throw new HttpException(200, $this->_getErrors($model));
                }
            }
            return $media;
        }
    }

    protected function _addNotification($user_id, $type, $title, $body, $data = null)
    {
        $notification = new Notification;
        $notification->user_id = $user_id;
        $notification->type = $type;
        $notification->title = $title;
        $notification->body = $body;
        $notification->data = json_encode($data);

        if (!$notification->save()) {
            return $this->_getErrors($notification); //saving problem
        }
    }

    protected function _sendNotification($user, $title, $body, $data = null)
    {
        $title = Translation::get($user->lang, $title);
        $body = Translation::get($user->lang, $body);
        foreach ($user->tokens as $token) {
            \app\components\Notification::sendNotification($token, $title, $body, $data);
        }
    }
}
