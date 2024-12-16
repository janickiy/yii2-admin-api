<?php

namespace common\models;

class UserRefreshTokens extends \yii\db\ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%user_refresh_tokens}}';
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['urf_userID', 'urf_token', 'urf_ip', 'urf_user_agent', 'urf_created'], 'required'],
            [['urf_userID'], 'integer'],
            [['urf_created'], 'safe'],
            [['urf_token', 'urf_user_agent'], 'string', 'max' => 1000],
            [['urf_ip'], 'string', 'max' => 50],
        ];
    }

    /**
     * @return string[]
     */
    public function attributeLabels(): array
    {
        return [
            'user_refresh_tokenID' => 'User Refresh Token ID',
            'urf_userID' => 'Urf User ID',
            'urf_token' => 'Urf Token',
            'urf_ip' => 'Urf Ip',
            'urf_user_agent' => 'Urf User Agent',
            'urf_created' => 'Urf Created',
        ];
    }
}