<?php

namespace app\modules\api\controllers;

use common\models\Category;
use yii\rest\Controller;
use Yii;
use yii\web\Response;

class CategoryController extends Controller
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

    public function actionIndex()
    {
        $categories = Category::find()->all();

        $result = [];
        foreach ($categories as $category) {
            $categoryData = [
                'id' => $category->id,
                'name' => $category->name,
            ];
            $result[] = $categoryData;
        }

        return $result;
    }
}