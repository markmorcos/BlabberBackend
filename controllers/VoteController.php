<?php

namespace app\controllers;

use Yii;
use app\models\Vote;
use app\models\VoteSearch;
use app\models\User;
use app\models\Poll;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;

/**
 * VoteController implements the CRUD actions for Vote model.
 */
class VoteController extends AdminController
{
    /**
     * Lists all Category models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new VoteSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Vote model.
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
     * Creates a new Vote model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Vote();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $users_for_dropdown = ArrayHelper::map(User::find()->all(), 'id', 'name');
            $polls_for_dropdown = ArrayHelper::map(Poll::find()->all(), 'id', 'title');
            return $this->render('create', [
                'model' => $model,
                'users_for_dropdown' => $users_for_dropdown,
                'polls_for_dropdown' => $polls_for_dropdown,
            ]);
        }
    }

    /**
     * Updates an existing Vote model.
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
            $users_for_dropdown = ArrayHelper::map(User::find()->all(), 'id', 'name');
            $polls_for_dropdown = ArrayHelper::map(Poll::find()->all(), 'id', 'title');
            return $this->render('update', [
                'model' => $model,
                'users_for_dropdown' => $users_for_dropdown,
                'polls_for_dropdown' => $polls_for_dropdown,
            ]);
        }
    }

    /**
     * Deletes an existing Vote model.
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
     * Finds the Vote model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Vote the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Vote::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
