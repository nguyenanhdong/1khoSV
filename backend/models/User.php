<?php

namespace backend\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use mdm\admin\components\Configs;
use yii\widgets\ActiveForm;



/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $is_active
 * @property integer $create_date
 * @property integer $last_update
 * @property integer $isAdmin
 * @property string $password write-only password
 *
 * @property UserProfile $profile
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    public $userRole;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        // return Configs::instance()->userTable;
        return '{{%employee}}';
    }

    /**
     * @inheritdoc
     */
    // public function behaviors()
    // {
    //     return [
    //         TimestampBehavior::className(),
    //     ];
    // }
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Tài khoản đăng nhập',
            'password' => 'Mật khẩu',
            'email' => 'Email',
            'is_active' => 'Trạng thái tài khoản',
            'fullname'  => 'Họ tên',
            'phone'     => 'Điện thoại',
            'is_admin'   => 'Là Admin?'
        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username','password'], 'required', 'message' => '{attribute} không được trống'],
            array(['username','password'],'string', 'min' => 4, 'max'=>20,
					'tooShort'=>"{attribute} từ 4 - 20 ký tự.",
                    'tooLong'=>"{attribute} từ 4 - 20 ký tự."),
			array(
				'username',
				'match', 'pattern' => '/^[a-z]\w*$/i',
				'message' => '{attribute} không chứa ký tự có dấu, ký tự đặc biệt.',
			),
            ['username', 'checkExist'],
            // ['status', 'default', 'value' => self::STATUS_ACTIVE],
            // ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
        ];
    }
    public function checkExist($attribute,$params)
    {
        if(!empty($this->username)){

            if(strlen($this->username) >= 4 && preg_match('/[a-zA-Z0-9]/',$this->username)){
                $resultCheck = static::findOne(['username' => $this->username]);
                if( $resultCheck != null && ( $this->id <= 0 || ($this->id > 0 && $this->id != $resultCheck->id ) ) )
                    $this->addError('username','Tên tài khoản đã tồn tại');
            }
        }
    }
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
                'password_reset_token' => $token,
                // 'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return '';
        // return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return true;//$this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
        // return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        return $this->password = $password;
        // $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        // $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        // $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        // $this->password_reset_token = null;
    }

    public static function getDb()
    {
        return Configs::userDb();
    }
}
