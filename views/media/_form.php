<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Media */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="media-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->dropDownList([ 'business_image' => 'Business image', 'category_badge' => 'Category badge', 'category_icon' => 'Category icon', 'category_image' => 'Category image', 'flag_icon' => 'Flag icon', 'image' => 'Image', 'menu' => 'Menu', 'product' => 'Product', 'profile_photo' => 'Profile photo', 'sponsor_image' => 'Sponsor image', 'video' => 'Video', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'user_id')->textInput() ?>

    <?= $form->field($model, 'object_id')->textInput() ?>

    <?= $form->field($model, 'object_type')->dropDownList([ 'User' => 'User', 'Business' => 'Business', 'Category' => 'Category', 'Flag' => 'Flag', 'Sponsor' => 'Sponsor', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'caption')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'rating')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created')->textInput() ?>

    <?= $form->field($model, 'updated')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
