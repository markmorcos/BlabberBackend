<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use faryshta\widgets\JqueryTagsInput;

/* @var $this yii\web\View */
/* @var $model app\models\Business */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="business-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nameAr')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'rating')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'website')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fb_page')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'descriptionAr')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'featured')->checkbox(['maxlength' => true]) ?>

    <?= $form->field($model, 'verified')->checkbox(['maxlength' => true]) ?>

    <?= $form->field($model, 'show_in_home')->checkbox(['maxlength' => true]) ?>

    <?= $form->field($model, 'category_id')->dropDownList($categories_for_dropdown) ?>

    <?= $form->field($model, 'admin_id')->dropDownList($users_for_dropdown) ?>

    <label class="control-label">Interests</label>
    <div style="margin-bottom: 15px;">
        <?= JqueryTagsInput::widget([
            'model' => $model,
            'attribute' => 'interestsList',
            'clientOptions' => [
                'width' => '100%',
                'defaultText' => 'add more',
            ]
        ]) ?>
    </div>

    <?= $form->field($model, 'main_image')->fileInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
