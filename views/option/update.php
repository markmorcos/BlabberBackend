<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Option */

$this->title = 'Update Option: ' . $model->option;
$this->params['breadcrumbs'][] = ['label' => 'Options', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->option, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="option-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'polls_for_dropdown' => $polls_for_dropdown,
    ]) ?>

</div>
