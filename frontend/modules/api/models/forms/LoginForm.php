<?php

namespace app\modules\api\models\forms;

use common\models\User;
use Yii;
use yii\base\Model;


/**
 * @OA\Schema(
 *      schema="User",
 *      required={"username"},
 *     @OA\Property(
 *        property="id",
 *        description="User ID",
 *        type="integer",
 *        format="int64",
 *    ),
 *     @OA\Property(
 *         property="name",
 *         description="name",
 *         type="string",
 *         maxLength=255,
 *     ),
 *     @OA\Property(
 *        property="username",
 *        description="user name",
 *        type="string",
 *        maxLength=100,
 *    ),
 *     @OA\Property(
 *        property="email",
 *        description="Mail",
 *        type="string",
 *        maxLength=100,
 *    ),
 *     @OA\Property(
 *        property="password",
 *        description="password",
 *        type="string",
 *        maxLength=64,
 *    ),
 *     @OA\Property(
 *        property="created_at",
 *        description="creationTime",
 *        type="string",
 *        default="0",
 *    ),
 *     @OA\Property(
 *        property="last_login_at",
 *        description="最后登录时间",
 *        type="string",
 *        default="0",
 *    ),
 *)
 *
* /**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 *
 */
class LoginForm extends Model
{
    public string $username;
    public string $password;
    public bool $rememberMe = true;

    private bool $_user = false;

    public function rules(): array
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect login or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}