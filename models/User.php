<?php

namespace app\models;

use PhpParser\Node\Expr\FuncCall;
use Yii;

use yii\web\IdentityInterface;

/**
 * This is the model class for table "User".
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property int $role
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Token[] $tokens
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'User';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'email', 'password_hash'], 'required'],
            ['role', 'integer', 'min' => 0, 'max' => 1],
            [['created_at', 'updated_at'], 'safe'],
            [['username', 'email', 'password_hash'], 'string', 'max' => 255],
            [['username', 'email'], 'string', 'min' => 3],
            [['password_hash'], 'string', 'min' => 8],

            [['email'], 'email'],
            [['email'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'email' => 'Email',
            'password_hash' => 'Password Hash',
            'role' => 'Role',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getAll(): array {
        return $this->find()->all();
    }

    /**
     * Gets query for [[Tokens]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTokens()
    {
        return $this->hasMany(Token::class, ['user_id' => 'id']);
    }

    public static function getToken(int $id): string|null {
        return Token::findOne(['user_id' => $id])->token;
    }

    public static function findIdentity($id)
    {
        return User::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        $token = Token::findOne(['token' => $token]);

        if(! $token) {
            return null;
        }
        
        $user = User::findOne($token->user_id);
        
        if(! $user) {
            return null;
        }

        $currentTime = date('Y-m-d H:i:s');
        $expireTime = $token->expires_at;

        if($currentTime > $expireTime) {
            return null;
        }

        return $user;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return null;
    }

    public function validateAuthKey($authKey)
    {
        return null;
    }

    public function isAdmin(): bool {
        return $this->role;
    }

    public function registerToken(string $token, int $expireTime = 3600) {
        
        $oldToken = Token::findOne(['user_id' => $this->id]);
        
        if($oldToken) {
            Token::deleteAll(['user_id' => $this->id]);
        }

        $newToken = new Token();
        $newToken->user_id = $this->id;
        $newToken->token = $token;
        // $newToken->created_at = date('Y-m-d H:i:s');
        $newToken->expires_at = date('Y-m-d H:i:s', time() + $expireTime);
        $newToken->save();

    } 
}
