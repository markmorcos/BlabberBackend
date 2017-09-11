<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\widgets\Pjax;
use dosamigos\fileupload\FileUploadUI;

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

.fileupload-buttonbar {
    padding-top: 20px;
}
.template-download,
.fileupload-buttonbar .delete,
.fileupload-buttonbar input[type='checkbox'] {
    display: none;
}

.images > div {
    border: 2px dotted;
    float: left;
    width: 120px;
    padding: 5px 0;
    margin: 5px;
    text-align: center;
}
.images > div > div {
    height: 110px;
    width: 120px;
    display: table-cell;
    vertical-align: middle;
}
.images > div > div > img {
    margin-bottom: 10px;

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


    $all_images  = '';
    $all_images .= '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>';
    $all_images .= '<ul class="nav nav-tabs">';
    $all_images .= '    <li class="active"><a data-toggle="tab" href="#images">Image</a></li>';
//        $all_images .= '    <li><a data-toggle="tab" href="#videos">Video</a></li>';
    $all_images .= '    <li><a data-toggle="tab" href="#menus">Menu</a></li>';
    $all_images .= '    <li><a data-toggle="tab" href="#products">Product</a></li>';
    $all_images .= '    <li><a data-toggle="tab" href="#brochures">Brochure</a></li>';
    $all_images .= '</ul>';

    $all_images .= '<div class="tab-content">';
    $all_images .= '    <div id="images" class="tab-pane fade in active">';
    $all_images .=          mediaUploader($model->id, 'image');
    $all_images .= '        <div class="images">';
    foreach ($model->images as $media) {
        $all_images .= newMedia($media, 'image');
    }
    $all_images .= '        </div>';
    $all_images .= '    </div>';
//        $all_images .= '    <div id="video" class="tab-pane fade">videos';
//        $all_images .= '    </div>';
    $all_images .= '    <div id="menus" class="tab-pane fade">';
    $all_images .=          mediaUploader($model->id, 'menu');
    $all_images .= '        <div class="images">';
    foreach ($model->menus as $media) {
        $all_images .= newMedia($media, 'menu');
    }
    $all_images .= '        </div>';
    $all_images .= '    </div>';
    $all_images .= '    <div id="products" class="tab-pane fade">';
    $all_images .=          mediaUploader($model->id, 'product');
    $all_images .= '        <div class="images">';
    foreach ($model->products as $media) {
        $all_images .= newMedia($media, 'product');
    }
    $all_images .= '        </div>';
    $all_images .= '    </div>';
    $all_images .= '    <div id="brochures" class="tab-pane fade">';
    $all_images .=          mediaUploader($model->id, 'brochure');
    $all_images .= '        <div class="images">';
    foreach ($model->brochures as $media) {
        $all_images .= newMedia($media, 'brochure');
    }
    $all_images .= '        </div>';
    $all_images .= '    </div>';
    $all_images .= '</div>';

    function mediaUploader($id, $type){
        $uploader = FileUploadUI::widget([
            'model' => new \app\models\Media(),
            'attribute' => 'file'.$type,
            'url' => ['add-media', 'media_type' => $type, 'business_id' => $id],
            'gallery' => false,
            'fieldOptions' => [
                'accept' => $type === 'brochure' ? 'application/pdf' : 'image/*'
            ],
            'clientOptions' => [
                'maxFileSize' => 2000000
            ],
            'clientEvents' => [
                'fileuploaddone' => 'function(e, data) {
                    updateMedia("'.$id.'","'.$type.'s");
                }',
                'fileuploadfail' => 'function(e, data) {
                    alert("Error in uploading images");
                }',
            ],
        ]);

        return $uploader;
    }

    function newMedia($media, $type){
        $image = "<div>";
        if ($type === 'brochure') {
            $image .= '<div><a target="_blank" href="'.Url::base(true).'/'.$media->url.'">Open File</a></div>';
        } else {
            $image .= '<div><img src="'.Url::base(true).'/'.$media->url.'" style="max-height: 100px; max-width: 100px;"/></div>';
        }
        $image .= Html::a('Delete', '#', [
            'class' => 'btn btn-danger',
            'onclick' => "return deleteMedia('".$media->id."','".$media->object_id."','".$type."s');",
        ]);
        $image .= "</div>";

        return $image;
    }
    ?>

    <script>
    function deleteMedia(id, business_id, type){
        if (confirm('Are you sure you want to delete this item?')) {
            $.ajax('<?= Url::to(['media/delete']) ?>', {
                type: 'POST',
                data: {id: id},
            }).done(function(data) {
                updateMedia(business_id,type);
            });
        }
        return false;
    }
    function updateMedia(business_id, type){
        $.ajax({
            url: "<?= Url::to(['business/get-images']) ?>",
            data: {id: business_id, type: type},
            success: function(data) {
                $('#'+type+' .images').html('');
                var images = JSON.parse(data);
                for (var i = 0; i < images.length; i++) {
                    imageDiv  = "<div>";
                    if (type === 'brochures') {
                        imageDiv += "   <div><a target='_blank' href='<?= Url::base(true) ?>/" + images[i]['url'] + "'>Open File</a></div>";
                    } else {
                        imageDiv += "   <div><img src='<?= Url::base(true) ?>/" + images[i]['url'] + "' style='max-height: 100px; max-width: 100px;'></div>";
                    }
                    imageDiv += "   <a class='btn btn-danger' href='#' onclick='return deleteMedia(\"" + images[i]['id'] + "\",\"" + business_id + "\",\"" + type + "\");'>Delete</a>";
                    imageDiv += "</div>";
                    $('#'+type+' .images').append(imageDiv)
                }
            }
        });
    }
    </script>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'nameAr',
            'address',
            'addressAr',
            'email',
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
            'operation_hours',
            'rating',
            'price',
            'website',
            'fb_page',
            'description',
            'descriptionAr',
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
            'isOpen',
        ],
    ]) 
    ?>

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
</div>
