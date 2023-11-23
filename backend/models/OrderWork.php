<?php

namespace backend\models;

use Yii;

class OrderWork extends \yii\db\ActiveRecord
{
    public $date_start;
    public $date_end;
    public $date_start_work;
    public $date_end_work;
    public $service_id;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_work';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            
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
            'email' => 'Email',
            'phone' => 'Số điện thoại',
            'address' => 'Địa chỉ',
            'gift_code' => 'Mã khuyến mại',
            'note'  => 'Ghi chú',
            'type_payment' => 'Hình thức thanh toán',
            'service_id'   => 'Dịch vụ',
            'price' => 'Giá',
        ];
    }
}
