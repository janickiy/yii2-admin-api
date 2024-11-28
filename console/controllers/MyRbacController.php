<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;

/**
 * Инициализатор RBAC выполняется в консоли php yii my-rbac/init
 */
class MyRbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        $auth->removeAll(); //На всякий случай удаляем старые данные из БД...

        // Создадим роли админа и редактора новостей
        $admin = $auth->createRole('admin');
        $manager = $auth->createRole('manager');

        // запишем их в БД
        $auth->add($admin);
        $auth->add($manager);

        // Создаем разрешения. Например, просмотр админки viewAdminPage и редактирование новости updateNews
        $viewAdminPage = $auth->createPermission('viewAdminPage');
        $viewAdminPage->description = 'Просмотр админки';

        // Создадим еще новое разрешение «Редактирование собственного контента» и ассоциируем его с правилом AuthorRule
        $updateOwnContent = $auth->createPermission('updateOwnContent');
        $updateOwnContent->description = 'Редактирование собственного контента';

        $updateContent = $auth->createPermission('updateContent');
        $updateContent->description = 'Редактирование контента';

        // Запишем эти разрешения в БД
        $auth->add($viewAdminPage);
        $auth->add($updateContent);
        $auth->add($updateOwnContent);

        $auth->addChild($manager, $updateContent);
        $auth->addChild($admin, $manager);
        $auth->addChild($admin, $viewAdminPage);

        // Назначаем роль admin пользователю с ID 1
        $auth->assign($admin, 1);

        // Назначаем роль manager пользователю с ID 2
        $auth->assign($manager, 2);
    }
}