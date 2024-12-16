<?php

namespace app\modules\api\controllers;

use app\models\FeedbackForm;
use yii\rest\Controller;

class FeedbackController extends Controller
{
    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => \kaabar\jwt\JwtHttpBearerAuth::class,
        ];
        return $behaviors;
    }

    public function actionCreate()
    {
        $model = new FeedbackForm();
        $model->load(\Yii::$app->request->post(), '');
        if ($model->submit()) {
            return ['status' => 'success'];
        }

        return ['status' => 'error', 'errors' => $model->errors];
    }
}
