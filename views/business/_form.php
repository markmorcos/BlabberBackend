<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\time\TimePicker;
use faryshta\widgets\JqueryTagsInput;

/* @var $this yii\web\View */
/* @var $model app\models\Business */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="business-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nameAr')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'addressAr')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'country_id')->dropDownList($countries_for_dropdown) ?>

    <?= $form->field($model, 'city_id')->dropDownList($cities_for_dropdown) ?>

    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

    <label class="control-label">Opening Time</label>
    <div style="clear: both; height: 44px;">
        <p style="float: left; line-height: 34px; margin: 0 10px 0 0;">From:</p>
        <?= TimePicker::widget(['model' => $model, 'attribute' => 'open_from', 'containerOptions' => ['style' => 'width: 30%; float: left;']]) ?>
        <p style="float: left; line-height: 34px; margin: 0 10px 0 20px;">To:</p>
        <?= TimePicker::widget(['model' => $model, 'attribute' => 'open_to', 'containerOptions' => ['style' => 'width: 30%; float: left;']]) ?>
    </div>

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

    <label class="control-label">Flags</label>
    <div style="margin-bottom: 15px;">
        <?php foreach ($flags as $id => $flag) { ?>
            <input id="flag-<?=$id?>" name="Business[flags][<?=$id?>]" type="checkbox" <?= (isset($selected_flags) && isset($selected_flags[$id])?'checked':'') ?> /> <?=$flag?> <br />
        <?php } ?>
    </div>

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

    <label class="control-label">Location</label>
    <input id="pac-input" class="controls" type="text" placeholder="Search Box" style="margin: 10px; height: 30px; width: 300px; padding: 0 10px;" />
    <div id="map" style="width: 600px; height: 300px; margin-bottom: 10px;"></div>
    <script>
        var map, marker;
        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: {lat: 30.042, lng: 31.252},
                zoom: 6
            });
            google.maps.event.addListener(map, 'click', function(event) {
                selected_location = event.latLng;

                if ( marker ) {
                    marker.setPosition(selected_location);
                } else {
                    marker = new google.maps.Marker({position: selected_location, map: map});
                }
                $('#business-lat').val(selected_location.lat());
                $('#business-lng').val(selected_location.lng());
            });

            // Create the search box and link it to the UI element.
            var input = document.getElementById('pac-input');
            var searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

            // Bias the SearchBox results towards current map's viewport.
            map.addListener('bounds_changed', function() {
                searchBox.setBounds(map.getBounds());
            });

            searchBox.addListener('places_changed', function() {
                var places = searchBox.getPlaces();

                if (places.length == 0) return;

                var bounds = new google.maps.LatLngBounds();
                places.forEach(function(place) {
                    if (!place.geometry) return;

                    if (place.geometry.viewport) {
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });
                map.fitBounds(bounds);
            });
          }

    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAgwhJ9ZH_DO_bK_UuSc5l3irAug07zL_0&libraries=places&callback=initMap" async defer></script>
    <?= $form->field($model, 'lat')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'lng')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
