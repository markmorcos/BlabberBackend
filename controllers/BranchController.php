<?php

namespace app\controllers;

use Yii;
use app\models\Branch;
use app\models\BranchSearch;
use app\models\BranchFlag;
use app\models\Area;
use app\models\Business;
use app\models\Flag;
use app\models\Media;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use yii\helpers\Json;

/**
 * BranchController implements the CRUD actions for Branch model.
 */
class BranchController extends AdminController
{
    /**
     * Lists all Branch models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BranchSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Branch model.
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
     * Creates a new Branch model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Branch();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // add flags
            if( !empty(Yii::$app->request->post('Business')['flags']) ){
                $flags = Yii::$app->request->post('Business')['flags'];
                $this->_addFlags($flags, $model->id);
            }
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $businesses_for_dropdown = ArrayHelper::map(Business::find()->all(), 'id', 'name');
            $areas_for_dropdown = ArrayHelper::map(Area::find()->all(), 'id', 'name');
            $flags = ArrayHelper::map(Flag::find()->all(), 'id', 'name');
            return $this->render('create', [
                'model' => $model,
                'businesses_for_dropdown' => $businesses_for_dropdown,
                'areas_for_dropdown' => $areas_for_dropdown,
                'flags' => $flags,
            ]);
        }
    }

    /**
     * Updates an existing Branch model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if( !empty(Yii::$app->request->post('Business')['flags']) ){
                // remove old flags
                BranchFlag::deleteAll('business_id = '.$id);            

                // add flags
                $flags = Yii::$app->request->post('Business')['flags'];
                $this->_addFlags($flags, $id);
            }
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $businesses_for_dropdown = ArrayHelper::map(Business::find()->all(), 'id', 'name');
            $areas_for_dropdown = ArrayHelper::map(Area::find()->all(), 'id', 'name');
            $flags = ArrayHelper::map(Flag::find()->all(), 'id', 'name');
            $selected_flags = ArrayHelper::map(BranchFlag::find()->where('branch_id = '.$model->id)->all(), 'flag_id', 'flag_id');
            return $this->render('update', [
                'model' => $model,
                'businesses_for_dropdown' => $businesses_for_dropdown,
                'areas_for_dropdown' => $areas_for_dropdown,
                'flags' => $flags,
                'selected_flags' => $selected_flags,
            ]);
        }
    }

    /**
     * Deletes an existing Branch model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        $this->deletePhotos($id, 'Branch');
        
        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['index']);
        }
    }

    /**
     * Finds the Branch model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Branch the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Branch::findOne($id)) !== null) {
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
                $branch_id = $_GET['branch_id'];
                $file_path = 'uploads/'.$media_type.'/'.$branch_id.'.'.pathinfo($media->file->name, PATHINFO_EXTENSION);

                // get unique name to the file
                while (file_exists($file_path)) {
                    $path = pathinfo($file_path);
                    $file_path = $path['dirname'].'/'.$path['filename'].'-.'.$path['extension'];
                }

                $media->url = $file_path;
                $media->type = $media_type;
                $media->user_id = Yii::$app->user->identity->id;
                $media->object_id = $branch_id;
                $media->object_type = 'Branch';

                if($media->save()){
                    $media->file->saveAs($file_path);
                    return Json::encode(['files' => []]);
                }
            }
        }

        return '';
    }

    public function actionGetImages($id, $type)
    {
        $model = Branch::find()->where(['id' => $id])->with($type)->asArray()->one();
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
                ->setTo($model->business->admin->email)
                ->setSubject('Branch Approval')
                ->setTextBody(
                    "Hello " . $model->business->admin->name . ",\n\n"
                    . $model->name . " branch has been approved, you’re now ready to view it and start blabbing!\n\n"
                    . "If you have any inquiries kindly contact us on info@myblabber.com :)\n\n"
                    . "Best regards,\n"
                    . "Blabber Support"
                )
                ->setHtmlBody(
                    "Hello " . $model->business->admin->name . ",<br><br>"
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
            ->setTo($model->business->admin->email)
            ->setSubject('Branch Disapproval')
            ->setTextBody(
                "Hello " . $model->business->admin->name . ",\n\n"
                . "We're extremely sorry! " . $model->name . " has been disapproved.\n\n"
                . "if you feel you shouldn't receive a disapproval for your branch kindly contact us on info@myblabber.com :)\n\n"
                . "Best regards,\n"
                . "Blabber Support"
            )
            ->setHtmlBody(
                "Hello " . $model->business->admin->name . ",<br><br>"
                . "We're extremely sorry! " . $model->name . " has been disapproved.<br><br>"
                . "if you feel you shouldn't receive a disapproval for your branch kindly contact us on info@myblabber.com :)<br><br>"
                . file_get_contents("../mail/layouts/signature.php")
            )
            ->send();

        echo 'done';
    }
}
