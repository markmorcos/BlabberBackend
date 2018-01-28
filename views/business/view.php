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
#products > .images > div, #menus > .images > div {
    width: 180px;
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
    $styled_interests_list = '';
    foreach ($model->interests as $interest) {
        $styled_interests_list .= '<span class="interest">'.$interest->interest->name.'</span>';
    }


    $all_images  = '';
    $all_images .= '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>';
    $all_images .= '<ul class="nav nav-tabs">';
    $all_images .= '    <li class="active"><a data-toggle="tab" href="#menus">Menu</a></li>';
    $all_images .= '    <li><a data-toggle="tab" href="#products">Product</a></li>';
    $all_images .= '    <li><a data-toggle="tab" href="#brochures">Brochure</a></li>';
    $all_images .= '</ul>';

    $all_images .= '<div class="tab-content">';
    $all_images .= '    <div id="menus" class="tab-pane fade in active">';
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
                'maxFileSize' => $type === 'brochure' ? 50 * 1024 * 1024 : 2 * 1024 * 1024
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
        } else if ($type === 'product' || $type === 'menu') {
            $image .= '<div class="fields" style="width:100%">
                <img src="'.Url::base(true).'/'.$media->url.'" style="max-height: 100px; max-width: 100px;"/>
                <span>
                    <input value="' . $media->section . '" name="section" placeholder="Section" class="form-control" />
                    <input value="' . $media->sectionAr . '" name="sectionAr" placeholder="Arabic Section" class="form-control" />
                    <input value="' . $media->title . '" name="title" placeholder="Title" class="form-control" />
                    <input value="' . $media->titleAr . '" name="titleAr" placeholder="Arabic Title" class="form-control" />
                    <input value="' . $media->caption . '" name="caption" placeholder="Caption" class="form-control" />
                    <input value="' . $media->captionAr . '" name="captionAr" placeholder="Arabic Caption" class="form-control" />
                    <input value="' . $media->currency . '" name="currency" placeholder="Currency" class="form-control" />
                    <input value="' . $media->currencyAr . '" name="currencyAr" placeholder="Arabic Currency" class="form-control" />
                    <input value="' . $media->price . '" name="price" placeholder="Price" class="form-control" />
                    <input value="' . $media->discount . '" name="discount" placeholder="Discount" class="form-control" />
                </span>
            </div>';
            $image .= Html::a('Update', '#', [
                'style' => 'margin-right:10px',
                'class' => 'btn btn-primary',
                'onclick' => "return saveMedia(this,'".$media->id."','".$media->object_id."','".$type."s');",
            ]);
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
    function editMedia(id, business_id, type){
        $.ajax('<?= Url::to(['media/edit']) ?>', {
            type: 'POST',
            data: { id: id, caption: $('#caption-' + id)[0].value, price: $('#price-' + id)[0].value },
        }).done(function(data) {
            if (data === 'done') updateMedia(business_id,type);
            else alert('Unable to update details');
        });
        return false;
    }
    function saveMedia(btn, id, business_id, type){
        let data = { id: id };

        const elems = $(btn).parent().find('.fields span input');
        for (var i = 0; i < elems.length; ++i) {
            let elem = $($(elems).get(i));
            
            const key = elem.attr('name');
            const value = elem.val();

            data[key] = value;
        }

        $.ajax('<?= Url::to(['media/save']) ?>', {
            type: 'POST',
            data: data,
        }).done(function(data) {
            updateMedia(business_id,type);
        });
        return false;
    }
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
                    } else if (type === 'products' || type === 'menus') {
                        imageDiv += '\
                        <div class="fields" style="width:100%">\
                            <img src="<?= Url::base(true) ?>/' + images[i]["url"] + '" style="max-height: 100px; max-width: 100px;">\
                            <span>\
                                <input value="' + images[i]['section'] + '" name="section" placeholder="Section" class="form-control" />\
                                <input value="' + images[i]['sectionAr'] + '" name="sectionAr" placeholder="Arabic Section" class="form-control" />\
                                <input value="' + images[i]['title'] + '" name="title" placeholder="Title" class="form-control" />\
                                <input value="' + images[i]['titleAr'] + '" name="titleAr" placeholder="Arabic Title" class="form-control" />\
                                <input value="' + images[i]['caption'] + '" name="caption" placeholder="Caption" class="form-control" />\
                                <input value="' + images[i]['captionAr'] + '" name="captionAr" placeholder="Arabic Caption" class="form-control" />\
                                <input value="' + images[i]['currency'] + '" name="currency" placeholder="Currency" class="form-control" />\
                                <input value="' + images[i]['currencyAr'] + '" name="currencyAr" placeholder="Arabic Currency" class="form-control" />\
                                <input value="' + images[i]['price'] + '" name="price" placeholder="Price" class="form-control" />\
                                <input value="' + images[i]['discount'] + '" name="discount" placeholder="Discount" class="form-control" />\
                            </span>\
                        </div>\
                        <a class="btn btn-primary" href="#" style="margin-right:10px" onclick="return saveMedia(this, ' + images[i]['id'] + ',\'' + business_id + '\',\'' + type + '\');">Update</a>';
                    } else {
                        imageDiv += "   <div><img src='<?= Url::base(true) ?>/" + images[i]['url'] + "' style='max-height: 100px; max-width: 100px;'></div>";
                    }
                    imageDiv += "<a class='btn btn-danger' href='#' onclick='return deleteMedia(\"" + images[i]['id'] + "\",\"" + business_id + "\",\"" + type + "\");'>Delete</a>";
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
            'name',
            'nameAr',
            'phone',
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
                'label' => 'Media',
                'format' => 'raw',
                'value' =>  $all_images
            ),
            'created',
            'updated',
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
</div>
