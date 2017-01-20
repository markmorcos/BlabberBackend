<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\BusinessSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Businesses';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="business-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Business', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            // 'address',
            // 'country_id',
            // 'city_id',
            // 'phone',
            // 'open_from',
            // 'open_to',
            // 'lat',
            // 'lng',
            // 'main_image',
            'rating',
            'price',
            // 'website',
            // 'fb_page',
            // 'description',
            // 'featured',
            // 'verified',
            // 'show_in_home',
            array(
                'attribute' => 'category_id',
                'format' => 'raw',
                'value' => function ($data) {
                    return ($data->category_id!=null)?Html::a($data->category->name, ['category/view', 'id' => $data->category_id]):null;
                },
            ), 
            array(
                'attribute' => 'admin_id',
                'format' => 'raw',
                'value' => function ($data) {
                    return ($data->admin_id!=null)?Html::a($data->admin->name, ['user/view', 'id' => $data->admin_id]):null;
                },
            ), 
            // 'created',
            // 'updated',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
