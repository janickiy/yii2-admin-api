<?php

namespace app\modules\api\models\forms;

use common\models\User;
use Yii;
use yii\base\Model;

class RegisterForm extends Model
{
    public $username;
    public $password;
    public $name;
    public $email;
    public $auth_key;

    public function rules(): array
    {
        return [
            [['username', 'name', 'password', 'email'], 'required'],
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => User::class, 'message' => 'This email is already taken.'],
            ['username', 'unique', 'targetClass' => User::class, 'message' => 'This username is already taken.'],
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Регистрация нового пользователя
     *
     * @return User|null
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function register()
    {
        if ($this->validate()) {
            $user = new User();
            $user->username = $this->username;
            $user->name = $this->name;
            $user->email = $this->email;
            $user->password_hash = Yii::$app->security->generatePasswordHash($this->password);
            $user->auth_key = Yii::$app->security->generateRandomString();

            return $user->save() ? $user : null;
        }
        return null;
    }
}