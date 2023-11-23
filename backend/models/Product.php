<?php

namespace backend\models;

use Yii;

class Product extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','price'],'required','message'=>'Nhập {attribute}'],
            [['image'],'required','message'=>'Chọn {attribute}'],
            [['description','price_discount'],'safe']
        ];
    }
    public function beforeSave($insert){
        if( $this->price )
            $this->price = str_replace([',','.'],'',$this->price);
        if( $this->price_discount )
            $this->price_discount = str_replace([',','.'],'',$this->price_discount);
        return parent::beforeSave($insert);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên sản phẩm',
            'price' => 'Giá gốc',
            'price_discount' => 'Giá khuyến mại',
            'description' => 'Mô tả',
            'service_id' => 'Dịch vụ',
            'status' => 'Trạng thái',
            'created_at' => 'Ngày thêm'
        ];
    }
	
	public function getProductCategory() {
		return $this->hasOne(ProductCategory::className(), ['id' => 'category_id']);

	}
}
