<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "course_section".
 *
 * @property integer $id
 * @property string $name
 * @property string $create_date
 * @property integer $course_id
 */
class Customer extends \yii\db\ActiveRecord
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
            [['create_at'], 'safe'],
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
}
