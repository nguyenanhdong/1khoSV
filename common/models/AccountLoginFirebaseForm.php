<?php
namespace common\models;

use common\models\Users;
use Yii;
use yii\base\Model;
use common\models\Account;
use yii\web\Response;
class AccountLoginFirebaseForm extends Model {
    public $phone;
    public $rememberMe = true;
    private $_account;

    public function rules(){
        return [
            // ['phone', 'filter', 'filter' => 'trim'],
            ['rememberMe', 'boolean'],            
        ];
    }
 public function attributeLabels() {
        return [
            'phone' =>Yii::t('app','Phone'),
            'rememberMe'=>Yii::t('app','Remember Me')
        ];
    }
    public function validatePassword($attribute,$params) {
        if(!$this->hasErrors()) {
            $account = $this->getAccount();
            if (!$account) {
               $this->addError($attribute,Yii::t('app','Incorrect e-mail or password.'));
            }
        }
    }
    public function login(){
        Yii::$app->response->format = Response::FORMAT_JSON;
        if ($this->validate()) {
           return Yii::$app->getUser()->login($this->getAccount(),$this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }
    protected function getAccount() {
        if ($this->_account === null) {
            if( !$this->_account && $this->phone )
                $this->_account = Users::findByPhone($this->phone);
        }
        return $this->_account;
    }
}