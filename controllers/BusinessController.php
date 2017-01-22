<?php

namespace app\controllers;

use Yii;
use app\models\Business;
use app\models\BusinessSearch;
use app\models\Country;
use app\models\City;
use app\models\Category;
use app\models\User;
use app\models\Media;
use app\models\Interest;
use app\models\BusinessInterest;
use app\models\Flag;
use app\models\BusinessFlag;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * BusinessController implements the CRUD actions for Business model.
 */
class BusinessController extends AdminController
{
    /**
     * Lists all Business models.
     * @return mixed
     */
    public function actionIndex()
    {
        (new \yii\db\Query())
                        ->select(['id', '( 6371 * acos( cos( radians(37) ) * cos( radians( 28.625032392 ) ) * cos( radians( 31.585693 ) - radians(-122) ) + sin( radians(37) ) * sin( radians( 28.625032392 ) ) ) ) AS distance'])
                        ->from('business')
                        ->having('distance < 2000')
                        ->orderBy(['distance' => SORT_ASC])
                        ->limit(20)
                        ->all();
        $searchModel = new BusinessSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Business model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Business model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Business();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // add flags
            $flags = Yii::$app->request->post('Business')['flags'];
            $this->_addFlags($flags, $model->id);

            // add interests
            $interests = Yii::$app->request->post('Business')['interestsList'];
            $this->_addInterests($interests, $model->id);
            
            $this->uploadPhoto($model, 'Business', 'business_image', 'main_image');

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $countries_for_dropdown = ArrayHelper::map(Country::find()->all(), 'id', 'name');
            $cities_for_dropdown = ArrayHelper::map(City::find()->all(), 'id', 'name');
            $categories_for_dropdown = ArrayHelper::map(Category::find()->all(), 'id', 'name');
            $users_for_dropdown = ArrayHelper::map(User::find()->where(['or', 'role="admin"', 'role="business"'])->all(), 'id', 'name');
            $flags = ArrayHelper::map(Flag::find()->all(), 'id', 'name');
            return $this->render('create', [
                'model' => $model,
                'countries_for_dropdown' => $countries_for_dropdown,
                'cities_for_dropdown' => $cities_for_dropdown,
                'categories_for_dropdown' => $categories_for_dropdown,
                'users_for_dropdown' => $users_for_dropdown,
                'flags' => $flags,
            ]);
        }
    }

    /**
     * Updates an existing Business model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // remove old flags
            BusinessFlag::deleteAll('business_id = '.$id);            

            // add flags
            $flags = Yii::$app->request->post('Business')['flags'];
            $this->_addFlags($flags, $id);

            // remove old interests
            BusinessInterest::deleteAll('business_id = '.$id);

            // add interests
            $interests = Yii::$app->request->post('Business')['interestsList'];
            $this->_addInterests($interests, $id);

            $this->uploadPhoto($model, 'Business', 'business_image', 'main_image');

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $countries_for_dropdown = ArrayHelper::map(Country::find()->all(), 'id', 'name');
            $cities_for_dropdown = ArrayHelper::map(City::find()->all(), 'id', 'name');
            $categories_for_dropdown = ArrayHelper::map(Category::find()->all(), 'id', 'name');
            $users_for_dropdown = ArrayHelper::map(User::find()->where(['or', 'role="admin"', 'role="business"'])->all(), 'id', 'name');
            $flags = ArrayHelper::map(Flag::find()->all(), 'id', 'name');
            $selected_flags = ArrayHelper::map(BusinessFlag::find()->where('business_id = '.$model->id)->all(), 'flag_id', 'flag_id');
            return $this->render('update', [
                'model' => $model,
                'countries_for_dropdown' => $countries_for_dropdown,
                'cities_for_dropdown' => $cities_for_dropdown,
                'categories_for_dropdown' => $categories_for_dropdown,
                'users_for_dropdown' => $users_for_dropdown,
                'flags' => $flags,
                'selected_flags' => $selected_flags,
            ]);
        }
    }

    /**
     * Deletes an existing Business model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Business model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Business the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Business::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionAddMedia()
    {
        if( !empty($_FILES['Media']) ){
            $media = new Media;
            $media->file = UploadedFile::getInstance($media,'file');
            if( isset($media->file) ){
                $media_type = $_POST['media_type'];
                $business_id = $_POST['business_id'];
                $file_path = 'uploads/'.$media_type.'/'.$business_id.'.'.pathinfo($media->file->name, PATHINFO_EXTENSION);

                // get unique name to the file
                while (file_exists($file_path)) {
                    $path = pathinfo($file_path);
                    $file_path = $path['dirname'].'/'.$path['filename'].'-.'.$path['extension'];
                }

                $media->url = $file_path;
                $media->type = $media_type;
                $media->user_id = Yii::$app->user->identity->id;
                $media->object_id = $business_id;
                $media->object_type = 'Business';

                if($media->save()){
                    $media->file->saveAs($file_path);
                }else{
                    echo 'error';
                }
            }
        }else{
            echo 'no file input';
        }

        echo 'done';
    }

    public function actionDeleteMedia()
    {
        $model = Media::findOne($_POST['media_id']);

        if(!unlink($model->url) || !$model->delete()){
            echo 'error';
        }

        echo 'done';
    }

    private function _addInterests($interests_input, $business_id){
        $interests = explode(',', $interests_input);
        foreach ($interests as $interest) {
            $temp_interest = Interest::find()->where('name = :name', [':name' => $interest])->one();
            if( empty($temp_interest) ){
                $temp_interest = new Interest();
                $temp_interest->name = $interest;
                $temp_interest->save();
            }

            $business_interest = new BusinessInterest();
            $business_interest->business_id = $business_id;
            $business_interest->interest_id = $temp_interest->id;
            $business_interest->save();
        }
    }

    private function _addFlags($flags, $business_id){
        foreach ((array)$flags as $id => $flag) {
            $business_flag = new BusinessFlag();
            $business_flag->business_id = $business_id;
            $business_flag->flag_id = $id;
            $business_flag->save();
        }
    }
}