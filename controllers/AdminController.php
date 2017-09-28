<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\AccessRule;
use app\models\Media;
use yii\web\UploadedFile;

class AdminController extends Controller
{
    public $layout = "admin";
    
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                // We will override the default rule config with the new AccessRule class
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'allow' => false,
                        'roles' => ['*'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function runAction($id, $params = [])
    {
        // Extract the params from the request and bind them to params
        $params = \yii\helpers\BaseArrayHelper::merge(Yii::$app->getRequest()->getBodyParams(), $params);
        
        return parent::runAction($id, $params);
    }

    protected function uploadPhoto($model, $object_type, $media_type, $image_name){
        if( !empty($_FILES[$object_type]) && 
            !empty($_FILES[$object_type]['size']) && 
            !empty($_FILES[$object_type]['size'][$image_name]) ){

            $media = new Media;
            $media->file = UploadedFile::getInstance($model, $image_name);
            if( isset($media->file) ){
                $file_path = 'uploads/'.$media_type.'/'.$model->id.'.'.pathinfo($media->file->name, PATHINFO_EXTENSION);
                while( file_exists($file_path) ){
                    $file_path = preg_replace('/(\.[^.]+)$/', sprintf('%s$1', '-'), $file_path);
                }

                $media->url = $file_path;
                $media->type = $media_type;
                $media->user_id = Yii::$app->user->identity->id;
                $media->object_id = $model->id;
                $media->object_type = $object_type;

                if($media->save()){
                    $media->file->saveAs($file_path);
                    $model->$image_name = $file_path;

                    if(!$model->save()){
                        echo $this->_getErrors($model);
                    }
                }else{
                    echo $this->_getErrors($media);
                }
            }
        }
    }

    protected function _getErrors($model)
    {
        $errors = '';
        foreach ($model->errors as $element) {
            foreach ($element as $error) {
                $errors .= $error . ', ';
            }
        }

        return $errors;
    }
}
