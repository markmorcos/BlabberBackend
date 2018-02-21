<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Category */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-view">

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
            'identifier',
            'name',
            'nameAr',
            'description',
            'descriptionAr',
            array(
                'attribute' => 'parent_id',
                'format' => 'raw',
                'value' =>  ($model->parent_id!=null)?Html::a($model->parent->name, ['category/view', 'id' => $model->parent_id]):null
            ), 
            'order',
            array(
                'attribute' => 'main_image',
                'format' => 'raw',
                'value' =>  ($model->main_image!=null)?Html::img('@web/'.$model->main_image, ['style'=>'max-width: 300px;']):null
            ), 
            array(
                'attribute' => 'icon',
                'format' => 'raw',
                'value' =>  ($model->icon!=null)?Html::img('@web/'.$model->icon, ['style'=>'max-width: 300px;']):null
            ), 
            array(
                'attribute' => 'badge',
                'format' => 'raw',
                'value' =>  ($model->badge!=null)?Html::img('@web/'.$model->badge, ['style'=>'max-width: 300px;']):null
            ), 
            'color',
            'created',
            'updated',
        ],
    ]) ?>

</div>
