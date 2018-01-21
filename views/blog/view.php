<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Blog */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Blogs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="blog-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'title',
            array(
                'attribute' => 'business_id',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->business->name, ['business/view', 'id' => $data->business_id]);
                },
            ),
            array(
                'attribute' => 'image',
                'format' => 'raw',
                'value' =>  ($model->image!=null)?Html::img('@web/'.$model->image, ['style'=>'max-width: 300px;']):null
            ), 
            array(
                'attribute' => 'content',
                'format' => 'html'
            ),
            'created',
            'updated',
        ],
    ]) ?>

</div>
