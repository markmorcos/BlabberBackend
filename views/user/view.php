<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-view">

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

    <?php Pjax::begin(['id' => 'pjax_widget', 'timeout' => false]); ?>
    <?= DetailView::widget([
        'id' => 'DetailView',
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            // 'password',
            'role',
            'email:email',
            'username',
            'mobile',
            'gender',
            'birthdate',
            // 'auth_key',
            array(
                'attribute' => 'profile_photo',
                'format' => 'raw',
                'value' =>  ($model->profile_photo!=null)?Html::img('@web/'.$model->profile_photo, ['style'=>'max-width: 300px;']):null
            ), 
            // 'cover_photo',
            // 'facebook_id',
            'firebase_token',
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
            array(
                'attribute' => 'blocked',
                'format' => 'raw',
                'value' => function ($data) {
                    if($data->blocked === 0){
                        $html = "No" . "&nbsp;&nbsp;&nbsp;<button onclick='block(".$data->id.")'>Block</button>";
                    }else{
                        $html = "Yes" . "&nbsp;&nbsp;&nbsp;<button onclick='unblock(".$data->id.")'>Unblock</button>";
                    }
                    return $html;
                },
            ), 
            array(
                'attribute' => 'private',
                'format' => 'raw',
                'value' => ($model->private === 0) ? "No" : "Yes"
            ), 
            'created',
            'updated',
        ],
    ]) ?>
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
    function block(id){
        $.ajax( {
            url: 'block',
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
    function unblock(id){
        $.ajax( {
            url: 'unblock',
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
