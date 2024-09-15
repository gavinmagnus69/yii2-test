<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 *
 */
class LoginForm extends Model
{
    public $email;
    public $password;

    public $user;

    public function setEmail($email):void {
        $this->email = $email;
    }

    public function setPassword($password): void {
        $this->password = $password;
    }
    
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {

            $user = User::findOne(['email' => $this->email]);

            if (!$user || !Yii::$app->getSecurity()->validatePassword($this->password, $user->password_hash)) {
                $this->addError($attribute, 'Incorrect username or password.');
                $this->user = null;   
            }

            $this->user = $user;
        }
    }

    public function login()
    {
        if ($this->validate()) {
                        
            Yii::$app->user->login($this->user);
            
            return true;
        }

        return false;
    }
}
