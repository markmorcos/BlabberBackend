<?php

namespace app\controllers;

use Yii;
use app\models\Poll;
use app\models\PollSearch;
use app\models\Business;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;

/**
 * PollController implements the CRUD actions for Poll model.
 */
class PollController extends AdminController
{
    /**
     * Lists all Category models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PollSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Poll model.
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
     * Creates a new Poll model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Poll();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $businesses_for_dropdown = ArrayHelper::map(Business::find()->all(), 'id', 'name');
            $types_for_dropdown = ['cigarette' => 'cigarette'];
            return $this->render('create', [
                'model' => $model,
                'businesses_for_dropdown' => $businesses_for_dropdown,
                'types_for_dropdown' => $types_for_dropdown,
            ]);
        }
    }

    /**
     * Updates an existing Poll model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $businesses_for_dropdown = ArrayHelper::map(Business::find()->all(), 'id', 'name');
            $types_for_dropdown = ['cigarette' => 'cigarette'];
            return $this->render('update', [
                'model' => $model,
                'businesses_for_dropdown' => $businesses_for_dropdown,
                'types_for_dropdown' => $types_for_dropdown,
            ]);
        }
    }

    /**
     * Deletes an existing Poll model.
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
     * Finds the Poll model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Poll the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Poll::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
