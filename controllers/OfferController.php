<?php

namespace app\controllers;

use Yii;
use app\models\Offer;
use app\models\OfferSearch;
use app\models\Business;
use app\models\Interest;
use app\models\User;
use app\models\UserInterest;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * OfferController implements the CRUD actions for Offer model.
 */
class OfferController extends AdminController
{
    /**
     * Lists all Offer models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OfferSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Offer model.
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
     * Creates a new Offer model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Offer();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->interest_id === '0') {
                $users = User::find()->select(['id', 'firebase_token'])->all();
            }else{
                $usersInterest = UserInterest::findAll(['interest_id' => $model->interest_id]);
                $users = ArrayHelper::getColumn($usersInterest, 'user');
            }
            
            $notifications = [];
            $data = [
                'business_id' => $model->business_id, 
                'business_name' => $model->business->name, 
                'business_main_image' => Url::base(true).'/'.$model->business->main_image,
                'business_id' => $model->business_id, 
                'image_url' => $model->image_url,
            ];
            foreach ($users as $user) {
                $notifications[] = [$user->id, $model->title, $model->body, json_encode($data)];

                if($model->push === '1'){
                    \app\components\Notification::sendNotification($user->getLastFirebaseToken(), $model->title, $model->body, $data);
                }
            }

            Yii::$app->db->createCommand()
                ->batchInsert('notification', ['user_id', 'title', 'body', 'data'], $notifications)
                ->execute();
            
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $businesses_for_dropdown = ArrayHelper::map(Business::find()->all(), 'id', 'name');
            $interest_for_dropdown = ['0' => 'all'] + ArrayHelper::map(Interest::find()->all(), 'id', 'name');
            return $this->render('create', [
                'model' => $model,
                'businesses_for_dropdown' => $businesses_for_dropdown,
                'interest_for_dropdown' => $interest_for_dropdown,
            ]);
        }
    }

    /**
     * Updates an existing Offer model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->interest_id === '0') {
                $users = User::find()->select(['id', 'firebase_token'])->all();
            }else{
                $usersInterest = UserInterest::findAll(['interest_id' => $model->interest_id]);
                $users = ArrayHelper::getColumn($usersInterest, 'user');
            }
            
            $notifications = [];
            $data = [
                'business_id' => $model->business_id, 
                'business_name' => $model->business->name, 
                'business_main_image' => Url::base(true).'/'.$model->business->main_image,
                'business_id' => $model->business_id, 
                'image_url' => $model->image_url,
            ];
            foreach ($users as $user) {
                $notifications[] = [$user->id, $model->title, $model->body, json_encode($data)];

                if($model->push === '1'){
                    \app\components\Notification::sendNotification($user->getLastFirebaseToken(), $model->title, $model->body, $data);
                }
            }

            Yii::$app->db->createCommand()
                ->batchInsert('notification', ['user_id', 'title', 'body', 'data'], $notifications)
                ->execute();
            
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $businesses_for_dropdown = ArrayHelper::map(Business::find()->all(), 'id', 'name');
            $interest_for_dropdown = ['0' => 'all'] + ArrayHelper::map(Interest::find()->all(), 'id', 'name');
            return $this->render('update', [
                'model' => $model,
                'businesses_for_dropdown' => $businesses_for_dropdown,
                'interest_for_dropdown' => $interest_for_dropdown,
            ]);
        }
    }

    /**
     * Deletes an existing Offer model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        
        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['index']);
        }
    }

    /**
     * Finds the Offer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Offer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Offer::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
