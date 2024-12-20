<?php

namespace app\modules\api\controllers;

use app\models\FeedbackForm;
use yii\rest\Controller;

/**
 * @OA\Info(title="My First API", version="0.1")
 */
class FeedbackController extends Controller
{
    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => \kaabar\jwt\JwtHttpBearerAuth::class,
        ];
        return $behaviors;
    }
    /**
     * @OA\Get(
     *   tags={"Products"},
     *   path="/products/{product_id}",
     *   @OA\Response(
     *       response="default",
     *       description="successful operation",
     *
     *   )
     * )
     */

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
