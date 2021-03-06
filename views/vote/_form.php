<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Vote */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="vote-form">

    <?php $form = ActiveForm::begin(); ?>
    
    <?= $form->field($model, 'user_id')->dropDownList($users_for_dropdown) ?>

    <?= $form->field($model, 'option_id')->dropDownList($options_for_dropdown) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
