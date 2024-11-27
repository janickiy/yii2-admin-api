<?php

namespace common\models;

use yii\db\ActiveQuery;

/**
 * This is the model class for table "feedbacks".
 *
 * @property int $id
 * @property string $email Email
 * @property string $phone Телефон
 * @property int|null $user_id Пользователь
 * @property string|null $text Текст сообщения
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 */
class Feedback extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'feedback';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['email', 'phone', 'created_at', 'updated_at'], 'required'],
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['text'], 'string'],
            [['email', 'phone'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
            'phone' => 'Phone',
            'user_id' => 'User ID',
            'text' => 'Text',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @param string $email
     * @param string $phone
     * @param int $user_id
     * @param string $text
     * @return array|self
     * @throws \yii\db\Exception
     */
    public static function create(string $email, string $phone, int $user_id, string$text)
    {
        $feedback = new self();
        $feedback->email = $email;
        $feedback->phone = $phone;
        $feedback->user_id = $user_id;
        $feedback->text = $text;

        $feedback->created_at = time();
        $feedback->updated_at = time();

        if (!$feedback->save()) {
            return $feedback->getErrors();
        }

        return $feedback;
    }
}
