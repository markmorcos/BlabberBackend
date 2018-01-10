<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Business */

$this->title = 'Update Business: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Businesses', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="business-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'countries_for_dropdown' => $countries_for_dropdown,
    	'cities_for_dropdown' => $cities_for_dropdown,
        'categories_for_dropdown' => $categories_for_dropdown,
        'users_for_dropdown' => $users_for_dropdown
    ]) ?>

</div>
