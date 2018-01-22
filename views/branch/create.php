<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Branch */

$this->title = 'Create Branch';
$this->params['breadcrumbs'][] = ['label' => 'Branches', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="branch-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
	    	'businesses_for_dropdown' => $businesses_for_dropdown,
	    	'countries_for_dropdown' => $countries_for_dropdown,
	    	'cities_for_dropdown' => $cities_for_dropdown,
	    	'areas_for_dropdown' => $areas_for_dropdown,
        'flags' => $flags
    ]) ?>

</div>