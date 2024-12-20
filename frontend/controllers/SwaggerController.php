<?php

namespace frontend\controllers;

use OpenApi\Attributes\Info;
use OpenApi\Attributes\OpenApi;
use OpenApi\Generator;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;

#[OpenApi(
    info: new Info(version: '1.0.0', title: 'API'),
)]
class SwaggerController extends Controller
{
    public function actionDoc()
    {



        $openapi = Generator::scan([
            Yii::getAlias('@app/modules/api/controllers'),
            Yii::getAlias('@app/modules/api/models'),
        ]);

        $this->asJson(Json::decode($openapi->toJson()));
    }
}