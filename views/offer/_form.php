<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\Offer */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="offer-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'titleAr')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'body')->textArea(['maxlength' => true]) ?>

    <?= $form->field($model, 'bodyAr')->textArea(['maxlength' => true]) ?>

    <?= $form->field($model, 'business_id')->widget(Select2::classname(), [
        'data' => $businesses_for_dropdown,
        'options' => [
            'placeholder' => 'Select a business ...'
        ],
    ]) ?>

    <?= $form->field($model, 'image_url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'interest_id')->widget(Select2::classname(), [
        'data' => $interest_for_dropdown,
    ]) ?>

    <?= $form->field($model, 'push')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
