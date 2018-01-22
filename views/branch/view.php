<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\widgets\Pjax;
use dosamigos\fileupload\FileUploadUI;

/* @var $this yii\web\View */
/* @var $model app\models\Branch */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Branches', 'url' => ['index']];
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

<div class="branch-view">

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


    $all_images  = '';
    $all_images .= '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>';
    $all_images .= '<ul class="nav nav-tabs">';
    $all_images .= '    <li class="active"><a data-toggle="tab" href="#images">Image</a></li>';
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
    $all_images .= '</div>';

    function mediaUploader($id, $type){
        $uploader = FileUploadUI::widget([
            'model' => new \app\models\Media(),
            'attribute' => 'file'.$type,
            'url' => ['add-media', 'media_type' => $type, 'branch_id' => $id],
            'gallery' => false,
            'fieldOptions' => [
                'accept' => 'image/*'
            ],
            'clientOptions' => [
                'maxFileSize' => 2 * 1024 * 1024
            ],
            'clientEvents' => [
                'fileuploaddone' => 'function(e, data) {
                    updateMedia("'.$id.'","'.$type.'s");
                }',
                'fileuploadfail' => 'function(e, data) {
                    alert("Error in uploading files");
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
    function editMedia(id, branch_id, type){
        $.ajax('<?= Url::to(['media/edit']) ?>', {
            type: 'POST',
            data: { id: id, caption: $('#caption-' + id)[0].value, price: $('#price-' + id)[0].value },
        }).done(function(data) {
            if (data === 'done') updateMedia(branch_id,type);
            else alert('Unable to update details');
        });
        return false;
    }
    function deleteMedia(id, branch_id, type){
        if (confirm('Are you sure you want to delete this item?')) {
            $.ajax('<?= Url::to(['media/delete']) ?>', {
                type: 'POST',
                data: {id: id},
            }).done(function(data) {
                updateMedia(branch_id,type);
            });
        }
        return false;
    }
    function updateMedia(branch_id, type){
        $.ajax({
            url: "<?= Url::to(['branch/get-images']) ?>",
            data: {id: branch_id, type: type},
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
                    imageDiv += "   <a class='btn btn-danger' href='#' onclick='return deleteMedia(\"" + images[i]['id'] + "\",\"" + branch_id + "\",\"" + type + "\");'>Delete</a>";
                    imageDiv += "</div>";
                    $('#'+type+' .images').append(imageDiv)
                }
            }
        });
    }
    </script>

    <?php Pjax::begin(['id' => 'pjax_widget', 'timeout' => false]); ?>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            array(
                'attribute' => 'business_id',
                'format' => 'raw',
                'value' =>  ($model->business_id!=null)?Html::a($model->business->name, ['business/view', 'id' => $model->business_id]):null
            ), 
            'name',
            'nameAr',
            'address',
            'addressAr',
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
            array(
                'attribute' => 'area_id',
                'format' => 'raw',
                'value' =>  ($model->area_id!=null)?Html::a($model->area->name, ['area/view', 'id' => $model->area_id]):null
            ), 
            'phone',
            'operation_hours',
            array(
                'attribute' => 'flags',
                'format' => 'raw',
                'value' =>  $styled_flags_list
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
            array(
                'attribute' => 'approved',
                'format' => 'raw',
                'value' => function ($data) {
                    if($data->approved === 0){
                        $html = "No" . "&nbsp;&nbsp;&nbsp;<button onclick='approve(".$data->id.")'>Approve</button>" . "&nbsp;&nbsp;&nbsp;<button onclick='disapprove(".$data->id.")'>Disapprove</button>";
                    }else{
                        $html = "Yes";
                    }
                    return $html;
                },
            ),
            'is_reservable',
        ],
    ]) 
    ?>
    <?php Pjax::end(); ?>

    <script type="text/javascript">
    function approve(id){
        $.ajax( {
            url: 'approve',
            type: 'POST',
            data: {id: id},
            success: function(response) {
                if( response == 'done' ){
                    $.pjax.reload({container: '#pjax_widget'});
                }else{
                    alert(response);
                }
            },
            error: function(){
                alert('ERROR at PHP side!!');
            },
        });
    }
    function disapprove(id){
        $.ajax( {
            url: 'disapprove',
            type: 'POST',
            data: {id: id},
            success: function(response) {
                if( response == 'done' ){
                    $.pjax.reload({container: '#pjax_widget'});
                }else{
                    alert(response);
                }
            },
            error: function(){
                alert('ERROR at PHP side!!');
            },
        });
    }
    </script>
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