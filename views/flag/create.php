<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Flag */

$this->title = 'Create Flag';
$this->params['breadcrumbs'][] = ['label' => 'Flags', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="flag-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
