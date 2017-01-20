<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">

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
            'name',
            // 'password',
            'role',
            'email:email',
            'mobile',
            'gender',
            'birthdate',
            // 'auth_key',
            array(
                'attribute' => 'profile_photo',
                'format' => 'raw',
                'value' =>  ($model->profile_photo!=null)?Html::img('@web/'.$model->profile_photo, ['style'=>'max-width: 300px;']):null
            ), 
            // 'cover_photo',
            // 'facebook_id',
            'created',
            'updated',
        ],
    ]) ?>

</div>
