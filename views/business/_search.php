<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\BusinessSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="business-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'address') ?>

    <?php // echo $form->field($model, 'country_id') ?>

    <?php // echo $form->field($model, 'city_id') ?>

    <?= $form->field($model, 'phone') ?>

    <?php // echo $form->field($model, 'open_from') ?>

    <?php // echo $form->field($model, 'open_to') ?>

    <?= $form->field($model, 'lat') ?>

    <?= $form->field($model, 'lng') ?>

    <?php // echo $form->field($model, 'main_image') ?>

    <?php // echo $form->field($model, 'rating') ?>

    <?php // echo $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'website') ?>

    <?php // echo $form->field($model, 'fb_page') ?>

    <?php // echo $form->field($model, 'description') ?>

    <?php // echo $form->field($model, 'featured') ?>

    <?php // echo $form->field($model, 'verified') ?>

    <?php // echo $form->field($model, 'show_in_home') ?>
    
    <?php // echo $form->field($model, 'category_id') ?>

    <?php // echo $form->field($model, 'admin_id') ?>

    <?php // echo $form->field($model, 'created') ?>

    <?php // echo $form->field($model, 'updated') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
