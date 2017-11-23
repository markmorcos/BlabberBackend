<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Vote */

$this->title = 'Update Vote: ' . $model->answer;
$this->params['breadcrumbs'][] = ['label' => 'Votes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->answer, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="vote-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'users_for_dropdown' => $users_for_dropdown,
        'polls_for_dropdown' => $polls_for_dropdown,
    ]) ?>

</div>
