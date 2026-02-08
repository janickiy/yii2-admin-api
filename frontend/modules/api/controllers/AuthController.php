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
use kaabar\jwt\JwtHttpBearerAuth;
use OpenApi\Attributes as OAT;

#[OAT\Info(title: 'My First API', version: '0.1')]
class AuthController extends Controller
{
    public $enableCsrfValidation = false;

    public function init()
    {
        parent::init();
        Yii::$app->request->parsers = ['application/json' => 'yii\web\JsonParser'];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
            'except' => [
                'login',
                'registration',
                'options',
            ],
        ];
        return $behaviors;
    }

    #[OAT\Post(
        path: '/api/auth/login',
        summary: 'Авторизация пользователя',
        description: 'Возвращает JWT токен при успешном входе',
        tags: ['Auth'],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
            required: ['username', 'password'],
            properties: [
                new OAT\Property(property: 'username', type: 'string', example: 'admin'),
                new OAT\Property(property: 'password', type: 'string', format: 'password', example: 'secret123')
                ]
            )
        ),
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Успешный вход',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'token', type: 'string', example: 'eyJhbGciOiJIUzI1NiJ9...')
                    ]
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'Неверный логин или пароль'
            )
        ]
    ),
    ]
    public function actionLogin()
    {
        $model = new LoginForm();

        if ($model->load(Yii::$app->request->bodyParams, '') && $model->login()) {
            $user = Yii::$app->user->identity;
            $token = $this->generateJwt($user);
            $this->generateRefreshToken($user, $token);

            return [
                'user' => $user->id,
                'token' => (string)$token,
            ];
        } else {
            return $model->getFirstErrors();
        }
    }

    /**
     * @return array|string[]
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionRegistration()
    {
        $model = new RegisterForm();

        $post = Yii::$app->request->post();
        $model->name = $post['name'];
        $model->username = $post['username'];
        $model->password = $post['password'];
        $model->email = $post['email'];

        if ($model->register()) {
            return [
                'result' => true,
            ];
        } else {
            return $model->getFirstErrors();
        }
    }

    #[OAT\Post(
        path: '/auth/refresh-token',
        summary: 'Обновление access-токена',
        description: 'Использует refresh-токен для получения новой пары токенов',
        tags: ['Auth'],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ['refresh_token'],
                properties: [
                    new OAT\Property(
                        property: 'refresh_token',
                        type: 'string',
                        example: 'def5020081014e2c...'
                    )
                ]
            )
        ),
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Токены успешно обновлены',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'token', type: 'string', example: 'new_access_token'),
                        new OAT\Property(property: 'refresh_token', type: 'string', example: 'new_refresh_token')
                    ]
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'Refresh-токен невалиден или просрочен'
            )
        ]
    )]
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

            $user = User::find()->where(['userID' => $userRefreshToken->urf_userID])
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
     * @return string
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
            ->canOnlyBeUsedAfter($now->modify($jwtParams['request_time']))
            ->expiresAt($now->modify($jwtParams['expire']))
            ->withClaim('uid', $user->id)
            ->getToken($signer, $key);

        $token = $token->toString();
        $this->generateRefreshToken($user, $token);

        return $token;
    }

    /**
     * @param User $user
     * @param string $token
     * @return void
     * @throws ServerErrorHttpException
     * @throws \yii\db\Exception
     */
    private function generateRefreshToken(User $user, string $token): void
    {
        $expiresAt = (new \DateTimeImmutable())->modify('+1 hour')->getTimestamp();
        $userRefreshToken = new UserRefreshTokens([
            'urf_userID' => $user->id,
            'urf_token' => $token,
            'urf_ip' => Yii::$app->request->userIP,
            'urf_user_agent' => Yii::$app->request->userAgent,
            'urf_expires_at' => $expiresAt,
            'urf_created' => gmdate('Y-m-d H:i:s'),
        ]);

        if (!$userRefreshToken->save()) {
            throw new ServerErrorHttpException('Failed to save the refresh token: ' . $userRefreshToken->getErrorSummary(true));
        }
    }
}
