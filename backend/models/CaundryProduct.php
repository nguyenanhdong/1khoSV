<?php

namespace backend\models;

use Yii;

class CaundryProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'caundry_product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','price','category_id','service_id'],'required','message'=>'Nhập {attribute}'],
            [['name','category_id','unit'],'safe']
        ];
    }
    public function beforeSave($insert){
        if( $this->price )
            $this->price = str_replace([',','.'],'',$this->price);
        if( $this->price_max )
            $this->price_max = str_replace([',','.'],'',$this->price_max);
        else
            $this->price_max = NULL;
        return parent::beforeSave($insert);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên',
            'price' => 'Giá tối thiểu',
            'price_max' => 'Giá tối đa',
            'category_id' => 'Nhóm',
            'status' => 'Trạng thái',
            'unit'   => 'Đơn vị tính',
            'created_at' => 'Ngày thêm',
            'service_id' => 'Dịch vụ'
        ];
    }
}
