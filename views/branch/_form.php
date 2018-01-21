<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use faryshta\widgets\JqueryTagsInput;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\Branch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="branch-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'business_id')->widget(Select2::classname(), [
        'data' => $businesses_for_dropdown,
        'options' => [
            'placeholder' => 'Select a business...'
        ],
    ]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nameAr')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'addressAr')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'area_id')->dropDownList($areas_for_dropdown) ?>

    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'operation_hours')->textInput(['maxlength' => true, 'placeholder' => 'from 01:00 am to 12:30 pm']) ?>

    <?= $form->field($model, 'is_reservable')->checkbox(['maxlength' => true]) ?>

    <label class="control-label">Flags</label>
    <div style="margin-bottom: 15px;">
        <?php foreach ($flags as $id => $flag) { ?>
            <input id="flag-<?=$id?>" name="Branch[flags][<?=$id?>]" type="checkbox" <?= (isset($selected_flags) && isset($selected_flags[$id])?'checked':'') ?> /> <?=$flag?> <br />
        <?php } ?>
    </div>

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
                $('#branch-lat').val(selected_location.lat());
                $('#branch-lng').val(selected_location.lng());
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