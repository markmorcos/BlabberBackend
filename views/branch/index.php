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
            'name',
            'nameAr',
            // 'address',
            // 'addressAr',
            // 'country_id',
            // 'city_id',
            // 'area_id',
            // 'phone',
            // 'operation_hours',
            // 'lat',
            // 'lng',
            // 'is_reservable',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>