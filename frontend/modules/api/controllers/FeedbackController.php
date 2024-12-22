<?php

namespace app\modules\api\controllers;

use app\modules\api\models\forms\FeedbackForm;
use yii\rest\Controller;
use yii\web\Response;
use Yii;

class FeedbackController extends Controller
{
    public function init()
    {
        parent::init();
        Yii::$app->request->parsers = ['application/json' => 'yii\web\JsonParser'];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
        ];

        $behaviors['authenticator'] = [
            'class' => \kaabar\jwt\JwtHttpBearerAuth::class,
        ];

        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];

        return $behaviors;
    }


    public function actionCreate()
    {
        $model = new FeedbackForm();
        $model->load(Yii::$app->request->post(), '');

        if ($model->submit()) {
            return ['result' => true];
        }

        return $model->getFirstErrors();
    }
}
