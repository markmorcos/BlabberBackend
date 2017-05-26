<?php

namespace app\controllers;

use Yii;
use app\models\Category;
use app\models\CategorySearch;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends AdminController
{
    /**
     * Lists all Category models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Category model.
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
     * Creates a new Category model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Category();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->uploadPhoto($model, 'Category', 'category_image', 'main_image');
            $this->uploadPhoto($model, 'Category', 'category_icon', 'icon');
            $this->uploadPhoto($model, 'Category', 'category_badge', 'badge');

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $categories_for_dropdown = ArrayHelper::map(Category::find()->where(['IS','parent_id', null])->all(), 'id', 'name');
            return $this->render('create', [
                'model' => $model,
                'categories_for_dropdown' => $categories_for_dropdown,
            ]);
        }
    }

    /**
     * Updates an existing Category model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->uploadPhoto($model, 'Category', 'category_image', 'main_image');
            $this->uploadPhoto($model, 'Category', 'category_icon', 'icon');
            $this->uploadPhoto($model, 'Category', 'category_badge', 'badge');

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $categories_for_dropdown = ArrayHelper::map(Category::find()->where(['IS','parent_id', null])->where(['<>','id', $model->id])->all(), 'id', 'name');
            return $this->render('update', [
                'model' => $model,
                'categories_for_dropdown' => $categories_for_dropdown,
            ]);
        }
    }

    /**
     * Deletes an existing Category model.
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
     * Finds the Category model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Category the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Category::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
