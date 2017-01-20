<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\InterestSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Interests';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="interest-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Interest', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'created',
            'updated',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
