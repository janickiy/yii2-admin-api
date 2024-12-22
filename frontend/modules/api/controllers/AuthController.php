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

    /**
     * @OA\Post(
     *     path="/auth/login/",
     *     summary="Login to get JWT token",
     *     description="Logs in the user and generates a JWT token and refresh token.",
     *     operationId="login",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful, returns JWT token and user details",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="token", type="string", description="JWT token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *     )
     * )
     */


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

    private function generateRefreshToken(User $user, string $token)
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
