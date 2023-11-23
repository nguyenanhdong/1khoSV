<?php

namespace backend\models;

use Yii;

class GiftCode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'gift_code';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code','price'],'required','message'=>'Nhập {attribute}'],
            [['code'], 'unique','message'=>'{attribute} đã tồn tại'],
            [['type_price','service','max_price_promotion','date_start','date_end'],'safe']
        ];
    }
    public function beforeSave($insert){
        if( $this->date_start != '' ){
            if( strpos($this->date_start,'/') !== false ){
                $date_start = explode('/',$this->date_start);
                $this->date_start = $date_start[2] . '-' . $date_start[1] . '-' . $date_start[0];
            }
        }else
            $this->date_start = NULL;
        if( $this->date_end != '' ){
            if( strpos($this->date_end,'/') !== false ){
                $date_end = explode('/',$this->date_end);
                $this->date_end = $date_end[2] . '-' . $date_end[1] . '-' . $date_end[0];
            }
        }else
            $this->date_end = NULL;

        if( $this->price )
            $this->price = str_replace([',','.','%','đ'],'',$this->price);
        if( $this->type_price == 2 && $this->max_price_promotion )
            $this->max_price_promotion = str_replace([',','.'],'',$this->max_price_promotion);
        else
            $this->max_price_promotion = 0;
        if( is_array($this->service) && !empty($this->service) ){
            $this->service = ';' . implode(';', $this->service) . ';';
        }else
            $this->service = 0;
        return parent::beforeSave($insert);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Mã khuyến mại',
            'service' => 'Dịch vụ',
            'type_price' => 'Loại giảm giá',
            'price'=> 'Số tiền/Phần trăm giảm giá',
            'date_start' => 'Ngày bắt đầu',
            'date_end'   => 'Ngày kết thúc',
            'max_price_promotion' => 'Số tiền giảm tối đa'
        ];
    }
}
