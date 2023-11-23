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
class Staff extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'staff';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fullname','phone','address'],'required','message'=>'Nhập {attribute}'],
            [['create_at'], 'safe'],
            [['fullname','email','phone','avatar','address'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fullname' => 'Tên nhân viên',
            'create_at' => 'Ngày tạo',
            'email' => 'Email',
            'address' => 'Địa chỉ',
            'phone' => 'Số điện thoại',
            'is_active' => 'Trạng thái',
            'avatar'    => 'Ảnh'
        ];
    }
}
