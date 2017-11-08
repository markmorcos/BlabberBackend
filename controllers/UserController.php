<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\UserSearch;
use yii\web\NotFoundHttpException;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends AdminController
{
    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
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
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();
        $model->scenario = 'create';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->uploadPhoto($model, 'User', 'profile_photo', 'profile_photo');

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';
        $model->password = "";

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->uploadPhoto($model, 'User', 'profile_photo', 'profile_photo');

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing User model.
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
                ->setSubject('Business account Approval')
                ->setTextBody(
                    "Hello " . $model->name . ",\n\n"
                    . "Your business account has been approved, you’re now ready to login and start blabbing!\n\n"
                    . "If you have any inquiries kindly contact us on info@myblabber.com :)\n\n"
                    . "Best regards,\n"
                    . "Blabber Support"
                )
                ->setHtmlBody(
                    "Hello " . $model->name . ",<br><br>"
                    . "Your business account has been approved, you’re now ready to login and start blabbing!<br><br>"
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
            ->setSubject('Buisness Account Disapproval')
            ->setTextBody(
                "Hello " . $model->name . ",\n\n"
                . "We're extremely sorry! Your business account has been disapproved.\n\n"
                . "if you feel you shouldn't receive a disapproval for you account kindly contact us on info@myblabber.com :)\n\n"
                . "Best regards,\n"
                . "Blabber Support"
            )
            ->setHtmlBody(
                "Hello " . $model->name . ",<br><br>"
                . "We're extremely sorry! Your business account has been disapproved.<br><br>"
                . "if you feel you shouldn't receive a disapproval for you account kindly contact us on info@myblabber.com :)<br><br>"
                . file_get_contents("../mail/layouts/signature.php")
            )
            ->send();

        echo 'done';
    }

    public function actionBlock()
    {
        if( empty($_POST['id']) ){
            echo 'no id input';
        }

        $model = $this->findModel($_POST['id']);
        $model->blocked = 1;
        if ($model->save()) {
            echo 'done';
        }else{
            echo 'failed!';
        }
    }

    public function actionUnblock()
    {
        if( empty($_POST['id']) ){
            echo 'no id input';
        }

        $model = $this->findModel($_POST['id']);
        $model->blocked = 0;
        if ($model->save()) {
            echo 'done';
        }else{
            echo 'failed!';
        }
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
