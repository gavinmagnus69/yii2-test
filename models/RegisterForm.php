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

class RegisterForm extends Model {

    public $username;
    public $password;
    public $email;
    public $role;
    

    public function rules()
    {
        return [
            [['username', 'email', 'password', 'role'], 'required'],
            ['role', 'integer', 'min' => 0, 'max' => 1],
            [['username', 'email', 'password'], 'string', 'max' => 255],
            [['username', 'email'], 'string', 'min' => 3],
            [['password'], 'string', 'min' => 8],
            [['email'], 'email'],
        ];
    }

    public function set($username, $email, $password, $role) {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    public function update(int $id): bool {

        if ($this->validate()) {
            
            $user = User::findOne($id);

            if(! $user) {
                $this->addError($id , 'User with this id does not exists');
                return false;
            }

            $user->username = $this->username;
            $user->password_hash = Yii::$app->getSecurity()->generatePasswordHash($this->password);
            $user->role = $this->role;
            $user->email = $this->email;
            $user->save();
            return true;
        }

        return false;
    }

    public function register(): bool
    {
        $existingUser = User::findOne(['email' => $this->email]);

        if($existingUser) {
            $this->addError($this->email, "User with this email already exists");
            return false;
        }

        if ($this->validate()) {
            $user = new User();
            $user->username = $this->username;
            $user->password_hash = Yii::$app->getSecurity()->generatePasswordHash($this->password);
            $user->role = $this->role;
            $user->email = $this->email;
            $user->save();
            return true;
        }

        return false;
    }
}