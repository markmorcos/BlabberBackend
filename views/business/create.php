<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Business */

$this->title = 'Create Business';
$this->params['breadcrumbs'][] = ['label' => 'Businesses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="business-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'countries_for_dropdown' => $countries_for_dropdown,
    	'cities_for_dropdown' => $cities_for_dropdown,
        'categories_for_dropdown' => $categories_for_dropdown,
        'users_for_dropdown' => $users_for_dropdown,
        'flags' => $flags,
    ]) ?>

</div>
