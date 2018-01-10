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
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use yii\helpers\Json;

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
        $searchModel = new BusinessSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Business model.
     * @param integer $id
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
            // add interests
            if( !empty(Yii::$app->request->post('Business')['interestsList']) ){
                $interests = Yii::$app->request->post('Business')['interestsList'];
                $this->_addInterests($interests, $model->id);
            }
            
            $this->uploadPhoto($model, 'Business', 'business_image', 'main_image');

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $countries_for_dropdown = ArrayHelper::map(Country::find()->all(), 'id', 'name');
            $cities_for_dropdown = ArrayHelper::map(City::find()->all(), 'id', 'name');
            $categories_for_dropdown = ArrayHelper::map(Category::find()->all(), 'id', 'name');
            $users_for_dropdown = ArrayHelper::map(User::find()->where(['or', 'role="admin"', 'role="business"'])->all(), 'id', 'name');
            return $this->render('create', [
                'model' => $model,
                'countries_for_dropdown' => $countries_for_dropdown,
                'cities_for_dropdown' => $cities_for_dropdown,
                'categories_for_dropdown' => $categories_for_dropdown,
                'users_for_dropdown' => $users_for_dropdown
            ]);
        }
    }

    /**
     * Updates an existing Business model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if( !empty(Yii::$app->request->post('Business')['interestsList']) ){
                // remove old interests
                BusinessInterest::deleteAll('business_id = '.$id);

                // add interests
                $interests = Yii::$app->request->post('Business')['interestsList'];
                $this->_addInterests($interests, $id);
            }

            $this->uploadPhoto($model, 'Business', 'business_image', 'main_image');

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $countries_for_dropdown = ArrayHelper::map(Country::find()->all(), 'id', 'name');
            $cities_for_dropdown = ArrayHelper::map(City::find()->all(), 'id', 'name');
            $categories_for_dropdown = ArrayHelper::map(Category::find()->all(), 'id', 'name');
            $users_for_dropdown = ArrayHelper::map(User::find()->where(['or', 'role="admin"', 'role="business"'])->all(), 'id', 'name');
            return $this->render('update', [
                'model' => $model,
                'countries_for_dropdown' => $countries_for_dropdown,
                'cities_for_dropdown' => $cities_for_dropdown,
                'categories_for_dropdown' => $categories_for_dropdown,
                'users_for_dropdown' => $users_for_dropdown
            ]);
        }
    }

    /**
     * Deletes an existing Business model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        $this->deletePhotos($id, 'Business');
        
        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['index']);
        }
    }

    /**
     * Finds the Business model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
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
            $media->file = UploadedFile::getInstance($media,'file'.$_GET['media_type']);
            if( isset($media->file) ){
                $media_type = $_GET['media_type'];
                $business_id = $_GET['business_id'];
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
                    return Json::encode(['files' => []]);
                }
            }
        }

        return '';
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

    public function actionGetImages($id, $type)
    {
        $model = Business::find()->where(['id' => $id])->with($type)->asArray()->one();
        return json_encode($model[$type]);
    }

    public function actionApprove()
    {
        if( empty($_POST['id']) ){
            echo 'no id input';
        }

        $model = $this->findModel($_POST['id']);
        $model->approved = 1;

        if ($model->save()) {
            //send email
            Yii::$app->mailer->compose()
                ->setFrom(['support@myblabber.com' => 'Blabber Support'])
                ->setTo($model->email)
                ->setSubject('Business Profile Approval')
                ->setTextBody(
                    "Hello " . $model->admin->name . ",\n\n"
                    . $model->name . " has been approved, you’re now ready to view it and start blabbing!\n\n"
                    . "If you have any inquiries kindly contact us on info@myblabber.com :)\n\n"
                    . "Best regards,\n"
                    . "Blabber Support"
                )
                ->setHtmlBody(
                    "Hello " . $model->admin->name . ",<br><br>"
                    . $model->name . " has been approved, you’re now ready to view it and start blabbing!<br><br>"
                    . "If you have any inquiries kindly contact us on info@myblabber.com :)<br><br>"
                    . file_get_contents("../mail/layouts/signature.php")
                )
                ->send();

            echo 'done';
        }else{
            echo 'failed!';
        }
    }

    public function actionDisapprove()
    {
        if( empty($_POST['id']) ){
            echo 'no id input';
        }

        $model = $this->findModel($_POST['id']);

        //send email
        Yii::$app->mailer->compose()
            ->setFrom(['support@myblabber.com' => 'Blabber Support'])
            ->setTo($model->email)
            ->setSubject('Business Profile Disapproval')
            ->setTextBody(
                "Hello " . $model->admin->name . ",\n\n"
                . "We're extremely sorry! " . $model->name . " has been disapproved.\n\n"
                . "if you feel you shouldn't receive a disapproval for your business profile kindly contact us on info@myblabber.com :)\n\n"
                . "Best regards,\n"
                . "Blabber Support"
            )
            ->setHtmlBody(
                "Hello " . $model->admin->name . ",<br><br>"
                . "We're extremely sorry! " . $model->name . " has been disapproved.<br><br>"
                . "if you feel you shouldn't receive a disapproval for your business profile kindly contact us on info@myblabber.com :)<br><br>"
                . file_get_contents("../mail/layouts/signature.php")
            )
            ->send();

        echo 'done';
    }
}
