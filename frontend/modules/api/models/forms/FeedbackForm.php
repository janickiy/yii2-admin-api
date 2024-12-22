<?php

namespace app\modules\api\models\forms;

use Yii;
use yii\base\Model;
use common\models\Feedback;

class FeedbackForm extends Model
{
    public string $email;
    public string $name;
    public string $message;
    public string $phone;

    public function rules(): array
    {
        return [
            [['email', 'message'], 'required'],
            ['email', 'email'],
            [['name', 'phone'], 'string', 'max' => 255],
        ];
    }

    public function submit()
    {
        if ($this->validate()) {
            $feedback = new Feedback();
            $feedback->email = $this->email;
            $feedback->name = $this->name;
            $feedback->message = $this->message;
            $feedback->phone = $this->phone;
            $feedback->save();

            Yii::$app->mailer->compose()
                ->setTo(Yii::$app->params['adminEmail'])
                ->setFrom([$this->email => $this->name])
                ->setSubject('Обратная связь')
                ->setTextBody($this->message)
                ->send();

            return true;
        }
        return false;
    }
}