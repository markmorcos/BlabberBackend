<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Asset */

$this->title = 'Update Asset: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Asset', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="asset-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
