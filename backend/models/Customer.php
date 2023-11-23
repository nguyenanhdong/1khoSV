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
            [['fullname'], 'string', 'max' => 100],
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
            'email' => 'Email',
            'phone' => 'Số điện thoại',
            'fb_id' => 'ID Facebook',
            'apple_id' => 'ID Apple',
            'address' => 'Địa chỉ'
        ];
    }
}
