<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Token".
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string $expires_at
 * @property string|null $created_at
 *
 * @property User $user
 */
class Token extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Token';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'token', 'expires_at'], 'required'],
            [['user_id'], 'integer'],
            [['expires_at', 'created_at'], 'safe'],
            [['token'], 'string', 'max' => 255],
            [['token'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'token' => 'Token',
            'expires_at' => 'Expires At',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
