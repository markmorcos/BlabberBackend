<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Poll */

$this->title = 'Update Poll: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Polls', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="poll-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'businesses_for_dropdown' => $businesses_for_dropdown,
        'types_for_dropdown' => $types_for_dropdown,
    ]) ?>

</div>
