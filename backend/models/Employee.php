<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "employee".
 *
 * @property integer $id
 * @property string $fullname
 * @property string $password
 * @property string $create_date
 * @property string $email
 * @property string $phone
 * @property string $address
 * @property integer $is_active
 * @property string $locale
 * @property string $birthday
 * @property string $last_update
 * @property string $device_id
 * @property string $ip
 * @property integer $is_admin
 * @property string $username
 * @property string $password_reset_token
 * @property string $auth_key
 */
class Employee extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'employee';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'ip', 'username', 'auth_key'], 'required'],
            [['id', 'is_active', 'is_admin'], 'integer'],
            [['create_date', 'birthday', 'last_update'], 'safe'],
            [['fullname', 'email'], 'string', 'max' => 100],
            [['password'], 'string', 'max' => 40],
            [['phone'], 'string', 'max' => 20],
            [['address', 'password_reset_token'], 'string', 'max' => 255],
            [['locale'], 'string', 'max' => 10],
            [['device_id'], 'string', 'max' => 50],
            [['ip', 'username'], 'string', 'max' => 30],
            [['auth_key'], 'string', 'max' => 32],
            [['username'], 'unique'],
            [['password_reset_token'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'mã nhân viên',
            'fullname' => 'họ và tên nhân viên',
            'password' => 'mật khẩu truy cập',
            'create_date' => 'ngày tạo tài khoản',
            'email' => 'email nhân viên',
            'phone' => 'số điện thoại',
            'address' => 'địa chỉ thường trú nhân viên',
            'is_active' => '1:active / 0: banned',
            'locale' => 'ngôn ngữ sử dụng backend: vi, en',
            'birthday' => 'ngày sinh nhật',
            'last_update' => 'ngày cập nhật profile mới nhất',
            'device_id' => 'mã thiết bị truy cập',
            'ip' => 'ip nhân viên đang truy cập',
            'is_admin' => '0: tk thường / 1: tk super admin',
            'username' => 'tên tài khoản',
            'password_reset_token' => 'token để reset mật khẩu',
            'auth_key' => 'token để reset mật khẩu',
        ];
    }
}
