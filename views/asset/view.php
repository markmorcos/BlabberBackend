<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Asset */

$this->title = $model->caption ? $model->caption : $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Asset', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="asset-view">

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
            array(
                'attribute' => 'asset',
                'format' => 'raw',
                'value' =>  ($model->asset!=null)?Html::img('@web/'.$model->asset, ['style'=>'max-width: 300px;']):null
            ), 
            'caption',
            'created',
            'updated',
        ],
    ]) ?>

</div>
