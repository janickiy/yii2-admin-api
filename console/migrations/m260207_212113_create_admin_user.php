<?php

use yii\db\Migration;
use common\models\User;

/**
 * Class m260207_212113_create_admin_user
 */
class m260207_212113_create_admin_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // 1. Создаем разрешение (permission)
        $adminPanel = $auth->createPermission('updateContent');
        $adminPanel->description = 'Доступ к админке';
        $auth->add($adminPanel);

        // 2. Создаем роль и даем ей разрешение
        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $adminPanel);

        // 3. Создаем пользователя
        $user = new User();
        $user->username = 'admin';
        $user->name = 'admin';
        $user->email = 'admin@example.com';
        $user->status = User::STATUS_ACTIVE; // Статус 10
        $user->setPassword('1234567');
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();

        if ($user->save()) {
            // 4. Привязываем роль админа к ID пользователя
            $auth->assign($admin, $user->id);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll(); // Удалит все роли и связи

        $user = User::findByUsername('admin');
        if ($user) {
            $user->delete();
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260207_212113_create_admin_user cannot be reverted.\n";

        return false;
    }
    */
}
