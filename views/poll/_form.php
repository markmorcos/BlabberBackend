<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Poll */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="poll-form">

    <?php $form = ActiveForm::begin(); ?>
    
    <?= $form->field($model, 'business_id')->dropDownList($businesses_for_dropdown) ?>

    <?= $form->field($model, 'title')->textInput(); ?>

    <?= $form->field($model, 'titleAr')->textInput(); ?>

    <?= $form->field($model, 'type')->dropDownList($types_for_dropdown); ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
