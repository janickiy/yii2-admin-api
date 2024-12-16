<?php

namespace app\modules\api\controllers;


use Yii;
use common\models\UserRefreshTokens;
use common\models\User;
use app\modules\api\models\forms\LoginForm;
use app\modules\api\models\forms\RegisterForm;
use yii\rest\Controller;
use yii\web\UnauthorizedHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\Cookie;

class AuthController extends Controller
{
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => \kaabar\jwt\JwtHttpBearerAuth::class,
            'except' => [
                'login',
                'register',
                'options',
            ],
        ];
        return $behaviors;
    }

    /**
     * @return array|string[]
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        $post = Yii::$app->request->post();

        if (!isset($post['login']) || !isset($post['password'])) {
            return ['error' => 'Необходимо заполнить поля: логин и пароль'];
        }

        $model->username = $post['login'];
        $model->password = $post['password'];

        if ($model->login()) {
            $user = Yii::$app->user->identity;
            $token = $this->generateJwt($user);
            return ['token' => $token];
        } else {
            return ['error' => $model->errors];
        }
    }

    /**
     * @return array|string[]
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionRegister()
    {
        $model = new RegisterForm();
        $post = Yii::$app->request->post();

        $model->username = $post['username'] ?? '';
        $model->password = $post['password'] ?? '';
        $model->email = $post['email'] ?? '';

        if ($model->register()) {
            return [
                'status' => 'ok',
                'message' => 'User registered successfully.'
            ];
        } else {
            return [
                'status' => 'error',
                'errors' => $model->errors
            ];
        }
    }


    /**
     * @return string[]|ServerErrorHttpException|UnauthorizedHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionRefreshToken()
    {
        $refreshToken = Yii::$app->request->cookies->getValue('refresh-token', false);
        if (!$refreshToken) {
            return new UnauthorizedHttpException('No refresh token found.');
        }

        $userRefreshToken = UserRefreshTokens::findOne(['urf_token' => $refreshToken]);

        if (Yii::$app->request->getMethod() == 'POST') {
            if (!$userRefreshToken) {
                return new UnauthorizedHttpException('The refresh token no longer exists.');
            }

            $user = User::find()
            ->where(['userID' => $userRefreshToken->urf_userID])
                ->andWhere(['not', ['usr_status' => 'inactive']])
                ->one();
            if (!$user) {
                $userRefreshToken->delete();
                return new UnauthorizedHttpException('The user is inactive.');
            }

            $token = $this->generateJwt($user);

            return [
                'status' => 'ok',
                'token' => (string)$token,
            ];

        } elseif (Yii::$app->request->getMethod() == 'DELETE') {
            if ($userRefreshToken && !$userRefreshToken->delete()) {
                return new ServerErrorHttpException('Failed to delete the refresh token.');
            }

            return ['status' => 'ok'];
        } else {
            return new UnauthorizedHttpException('The user is inactive.');
        }
    }

    /**
     * @param User $user
     * @return mixed
     */
    private function generateJwt(User $user)
    {
        $jwt = Yii::$app->jwt;
        $signer = $jwt->getSigner('HS256');
        $key = $jwt->getKey();

        $now = new \DateTimeImmutable();
        $jwtParams = Yii::$app->params['jwt'];

        $token = $jwt->getBuilder()
            ->issuedBy($jwtParams['issuer'])    
            ->permittedFor($jwtParams['audience']) 
            ->identifiedBy($jwtParams['id'], true)
            ->issuedAt($now)
            ->expiresAt($now->modify($jwtParams['expire'])) 
            ->withClaim('uid', $user->id)
            ->getToken($signer, $key);

        return $token->toString();
    }

    /**
     * @param User $user
     * @param User|null $impersonator
     * @return UserRefreshTokens
     * @throws ServerErrorHttpException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    private function generateRefreshToken(User $user, User $impersonator = null): UserRefreshTokens
    {
        $refreshToken = Yii::$app->security->generateRandomString(200);
        $userRefreshToken = new UserRefreshTokens([
            'urf_userID' => $user->id,
            'urf_token'  => $refreshToken,
            'urf_ip'     => Yii::$app->request->userIP,
            'urf_user_agent' => Yii::$app->request->userAgent,
            'urf_created'    => gmdate('Y-m-d H:i:s'),
        ]);
        if (!$userRefreshToken->save()) {
            throw new ServerErrorHttpException('Failed to save the refresh token: ' . $userRefreshToken->getErrorSummary(true));
        }

        Yii::$app->response->cookies->add(new Cookie([
            'name'  => 'refresh-token',
            'value' => $refreshToken,
            'httpOnly' => true,
            'sameSite' => 'none',
            'secure' => true,
            'path'   => '/api/auth/refresh-token',
        ]));

        return $userRefreshToken;
    }
}
