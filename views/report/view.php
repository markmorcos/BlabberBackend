<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Report */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Reports', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="report-view">

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
            'user_id',
            'object_id',
            'object_type',
            array(
                'attribute' => 'preview',
                'format' => 'raw',
            ), 
            array(
                'attribute' => 'link',
                'format' => 'raw',
            ), 
            array(
                'label' => 'Action',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a('Delete Item', '#', [
                        'id' => 'item_delete_btn',
                        'class' => 'btn btn-danger',
                        'onclick' => "
                            if (confirm('Are you sure you want to delete this item?')) {
                                $.ajax('".Url::to([(($data->object_type === 'image')?'media':$data->object_type).'/delete'])."', {
                                    type: 'POST',
                                    data: {id: ".$data->object_id."},
                                }).done(function(data) {
                                    $('#item_delete_btn').fadeOut();
                                }).fail(function(xhr, status, error) {
                                    alert(error);
                                });
                            }
                            return false;
                        "
                    ]);
                },
            ), 
            'created',
            'updated',
        ],
    ]) ?>

</div>
