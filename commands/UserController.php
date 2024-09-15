<?php
namespace app\commands;

use app\models\User;
use app\models\RegisterForm;
use yii\console\Controller;
use Yii;

class UserController extends Controller
{
    public function actionRegister($username, $email, $password, $role = 0)
    {
        
        $registerForm = new RegisterForm();

        $registerForm->set(
            $username,
            $email,
            $password,
            $role
        );

        if($registerForm->register())
        {

            echo "User registered\n";
            return;
        }

        foreach($registerForm->errors as $error) {
            foreach($error as $message){
                echo $message."\n";
            }
        }    
    }

   
}