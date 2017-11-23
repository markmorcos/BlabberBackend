<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VoteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Votes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="vote-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Vote', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            // ['class' => 'yii\grid\SerialColumn'],

            'id',
            array(
                'attribute' => 'user_id',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->user->name, ['user/view', 'id' => $data->user_id]);
                },
            ), 
            array(
                'attribute' => 'poll_id',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->poll->title, ['poll/view', 'id' => $data->poll_id]);
                },
            ), 
            'answer',
            // 'created',
            // 'updated',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
