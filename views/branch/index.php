<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\BranchSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Branches';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="branch-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Branch', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            array(
                'attribute' => 'business_id',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->business->name, ['business/view', 'id' => $data->business_id]);
                },
            ),
            array(
                'attribute' => 'country_id',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->country !== null ? Html::a($data->country->name, ['country/view', 'id' => $data->country_id]) : null;
                },
            ),
            array(
                'attribute' => 'city_id',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->city !== null ? Html::a($data->city->name, ['city/view', 'id' => $data->city_id]) : null;
                },
            ),
            array(
                'attribute' => 'area_id',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->area !== null ? Html::a($data->area->name, ['area/view', 'id' => $data->area_id]) : null;
                },
            ), 
            // 'address',
            // 'addressAr',
            // 'phone',
            // 'operation_hours',
            // 'lat',
            // 'lng',
            // 'is_reservable',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>