<?php

namespace app\controllers;

use app\models\LoginForm;
use app\models\RegisterForm;
use app\models\User;
use Codeception\Util\HttpCode;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\rbac\CheckAccessInterface;
use yii\web\UnauthorizedHttpException;

class UserController extends \yii\rest\ActiveController
{

    public $enableCsrfValidation = false;

    public $modelClass = 'app\models\User';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Add the Bearer token authentication
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['login'],
        ];

        return $behaviors;
    }

    //GET /users
    public function actionAllUsers() {
        
        if(!$this->authorize()){
            throw new UnauthorizedHttpException('Unauthorized');
        }

        return $this->currentUser()->getAll();

    }

    //POST /users
    public function actionAddUsers() {        
        
        if(! $this->authorize()){
            throw new UnauthorizedHttpException('Unauthorized');
        }

        $data = $this->getData();

        $registerForm = new RegisterForm();

        $registerForm->set(
            $data['username'],
            $data['email'],
            $data['password'],
            $data['role']
        );

        if($registerForm->register())
        {
            return ['Success' => 'User created'];
        }

        return $registerForm->errors;

    }

    //PATCH /users/{id}
    public function actionUpdateUser($id) {
        
        if(! $this->authorize()){
            throw new UnauthorizedHttpException('Unauthorized');
        }

        $registerForm = new RegisterForm();
        $data = $this->getData();

        $registerForm->set(
            $data['username'],
            $data['email'],
            $data['password'],
            $data['role']
        );

        if($registerForm->update($id)){

            if($this->currentUser()-> id == $id) {
                Yii::$app->user->logout();
            }

            return ["User $id" => 'Updated'];
            
        }

        return $registerForm->errors;
    }

    //DELETE /users/{id}
    public function actionDeleteUser($id) {
        
        if(! $this->authorize()){
            throw new UnauthorizedHttpException('Unauthorized');
        }

        if($this->currentUser()->id == $id) {
            Yii::$app->user->logout();
        }

        User::deleteAll(['id' => $id]);

        return ["Success" => "User with $id was deleted"];
    }

    //GET /me
    public function actionMe() {
        return $this->currentUser() != null ? $this->currentUser(): throw new UnauthorizedHttpException('Invalid username or password.');
    }

    //POST /login
    public function actionLogin() {

        
        $data = $this->getData();
        
        $model = new LoginForm();

        $model->setEmail($data['email']);
        $model->setPassword($data['password']);


        if ($model->login()) {

            $token = Yii::$app->security->generateRandomString();

            $this->currentUser()->registerToken($token);
    
            return [
                'token' => $token,
            ];

        } else {
            throw new UnauthorizedHttpException('Invalid username or password.');
        }   
    }

    private function getData(): array {
        return json_decode(Yii::$app->request->getRawBody(), true);
    }

    private function currentUser(): User|null {
        return Yii::$app->user->identity;
    }

    private function authorize(): bool {
        
        $user = $this->currentUser();

        return $user != null ? $user->isAdmin() : false;
    }

}
