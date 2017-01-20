<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\FlagSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Flags';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="flag-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Flag', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            // 'icon',
            'created',
            'updated',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
