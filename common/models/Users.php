<?php
namespace common\models;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;


class Users extends ActiveRecord implements IdentityInterface
{
    public $date_start;
    public $date_end;

    const ACCOUNT_VERIFYED = 1;
    const ACCOUNT_NOT_VERIFY = 1;
    const STATUS_ACTIVE = 1;
    const STATUS_BANNED = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_at','fullname','province','district'], 'safe'],
            [['fullname','province','district','address','phone'], 'required','message'=>'{attribute} không được để trống'],
            [['fullname'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fullname' => 'Họ tên',
            'create_at' => 'Ngày đăng ký',
            'phone' => 'Số điện thoại',
            'fb_id' => 'ID Facebook',
            'apple_id' => 'ID Apple',
            'gg_id'    => 'ID Google',
            'address' => 'Địa chỉ',
            'district' => 'Quận/Huyện',
            'province' => 'Thành phố',
        ];
    }

  
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return md5(md5($password)) == $this->password;
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = md5(md5($password));
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public static function findIdentity($id){        
         return static::findOne(['id'=>$id,'status'=>self::STATUS_ACTIVE]);
    }
   /* modified */
    public static function findIdentityByAccessToken($token, $type = null){
          return static::findOne(['access_token' =>$token]);
    }
    public static function findByPhone($phone){
        return static::findOne(['phone'=>$phone,'status'=>self::STATUS_ACTIVE]);
    }
    
}
