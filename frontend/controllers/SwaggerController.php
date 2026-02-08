<?php

namespace frontend\controllers;

use yii\web\Controller;
use OpenApi\Generator;
use Yii;
use yii\web\Response;


class SwaggerController extends Controller
{
    public function actionJson()
    {
        // Указываем путь(и) к папкам, где лежат контроллеры с аннотациями
        $openApi = Generator::scan([
            Yii::getAlias('@frontend/modules/api/controllers')
        ]);

        // Устанавливаем заголовок ответа как JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Возвращаем сгенерированный JSON (декодируем для чистоты вывода)
        return json_decode($openApi->toJson());
    }

    // Этот экшен мы используем для отображения UI
    public function actionUi()
    {
        Yii::$app->response->format = Response::FORMAT_HTML;

        return $this->renderPartial('ui');
    }
}