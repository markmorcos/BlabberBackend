<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\Business */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Businesses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<style type="text/css">
span.flag {
    background: #9CC3E5 none repeat scroll 0 0;
    border: 1px solid #9CB5E2;
    border-radius: 2px;
    color: #406789;
    display: block;
    float: left;
    margin-right: 5px;
    padding: 5px;
    height: 35px;
}
span.flag img {
    max-width: 25px;
    max-height: 25px;
    margin-right: 10px;
}
span.interest {
    background: #cde69c none repeat scroll 0 0;
    border: 1px solid #a5d24a;
    border-radius: 2px;
    color: #638421;
    display: block;
    float: left;
    margin-right: 5px;
    padding: 5px;
    height: 35px;
}
</style>

<div class="business-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <?php 
        $styled_flags_list = '';
        foreach ($model->flags as $flag) {
            $styled_flags_list .= '<span class="flag"><img src="'.Url::base(true).'/'.$flag->flag->icon.'" />.'.$flag->flag->name.'</span>';
        }

        $styled_interests_list = '';
        foreach ($model->interests as $interest) {
            $styled_interests_list .= '<span class="interest">'.$interest->interest->name.'</span>';
        }

        $all_images  = '<form id="add-media-form" method="post" enctype="multipart/form-data">';
        $all_images .= '<input type="file" name="Media[file]" />';
        $all_images .= '<input type="radio" name="media_type" value="image" /> image ';
        $all_images .= '<input type="radio" name="media_type" value="video" /> video ';
        $all_images .= '<input type="radio" name="media_type" value="menu" /> menu ';
        $all_images .= '<input type="radio" name="media_type" value="product" /> product';
        $all_images .= '<input type="hidden" name="business_id" value="'.$model->id.'" />';
        $all_images .= '<br /><input type="submit" />';
        $all_images .= '</form>';

        $all_images .= '<br />Images:<br />';
        foreach ($model->images as $media) { 
            $all_images .= newImage($media);
        }

        $all_images .= '<br />Products:<br />';
        foreach ($model->products as $media) { 
            $all_images .= newImage($media);
        }

        function newImage($media){
            $image = "<div>";
            $image .= '<img src="'.Url::base(true).'/'.$media->url.'" style="max-height: 100px; max-width: 100px;"/>'; 
            $image .= Html::a('Delete', '#', [
                'class' => 'btn btn-danger',
                'onclick' => "
                    if (confirm('Are you sure you want to delete this item?')) {
                        $.ajax('".Url::to(['media/delete'])."', {
                            type: 'POST',
                            data: {id: ".$media->id."},
                        }).done(function(data) {
                            $.pjax.reload({container: '#pjax_widget'});
                        });
                    }
                    return false;
                ",
            ]);
            $image .= "</div>";

            return $image;
        }
    ?>

    <?php Pjax::begin(['id' => 'pjax_widget', 'timeout' => false]); ?>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'address',
            array(
                'attribute' => 'country_id',
                'format' => 'raw',
                'value' =>  ($model->country_id!=null)?Html::a($model->country->name, ['country/view', 'id' => $model->country_id]):null
            ), 
            array(
                'attribute' => 'city_id',
                'format' => 'raw',
                'value' =>  ($model->city_id!=null)?Html::a($model->city->name, ['city/view', 'id' => $model->city_id]):null
            ), 
            'phone',
            array(
                'label' => 'Opening Time',
                'value' =>  'From: '.$model->open_from.' - To: '.$model->open_to
            ), 
            'rating',
            'price',
            'website',
            'fb_page',
            'description',
            array(
                'attribute' => 'featured',
                'format' => 'raw',
                'value' =>  ($model->featured==0)?"No":"Yes"
            ), 
            array(
                'attribute' => 'verified',
                'format' => 'raw',
                'value' =>  ($model->verified==0)?"No":"Yes"
            ), 
            array(
                'attribute' => 'show_in_home',
                'format' => 'raw',
                'value' =>  ($model->show_in_home==0)?"No":"Yes"
            ), 
            array(
                'attribute' => 'category_id',
                'format' => 'raw',
                'value' =>  ($model->category_id!=null)?Html::a($model->category->name, ['category/view', 'id' => $model->category_id]):null
            ), 
            array(
                'attribute' => 'admin_id',
                'format' => 'raw',
                'value' =>  ($model->admin_id!=null)?Html::a($model->admin->name, ['user/view', 'id' => $model->admin_id]):null
            ), 
            array(
                'attribute' => 'flags',
                'format' => 'raw',
                'value' =>  $styled_flags_list
            ), 
            array(
                'attribute' => 'interests',
                'format' => 'raw',
                'value' =>  $styled_interests_list
            ), 
            array(
                'attribute' => 'main_image',
                'format' => 'raw',
                'value' =>  ($model->main_image!=null)?Html::img('@web/'.$model->main_image, ['style'=>'max-width: 300px;']):null
            ), 
            array(
                'label' => 'Location',
                'format' => 'raw',
                'value' =>  '<div id="map" style="width: 600px; height: 300px;"></div>'
            ), 
            'lat',
            'lng',
            array(
                'label' => 'Media',
                'format' => 'raw',
                'value' =>  $all_images
            ), 
            'created',
            'updated',
        ],
    ]) 
    ?>
    <?php Pjax::end(); ?>

    <script>
        var map, position;
        function initMap() {
            position = {lat: <?= $model->lat ?>, lng: <?= $model->lng ?>};
            map = new google.maps.Map(document.getElementById('map'), {
                center: position,
                zoom: 11
            });
            new google.maps.Marker({position: position, map: map});
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAgwhJ9ZH_DO_bK_UuSc5l3irAug07zL_0&callback=initMap" async defer></script>

    <script>
    $('#add-media-form').submit( function( e ) {
        $.ajax( {
            url: 'add-media',
            type: 'POST',
            data: new FormData( this ),
            processData: false,
            contentType: false,
            success: function(response) {
                if( response == 'done' ){
                    location.reload();
                }else{
                    alert(response);
                }
            },
            error: function(){
                alert('ERROR at PHP side!!');
            },
        });
        e.preventDefault();
    });
    </script>
</div>
