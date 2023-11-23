<?php

namespace backend\models;

use Yii;
use backend\models\Product;

class ProductOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product_order';
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
        ];
    }
	
	public function getProduct() {
		return $this->hasOne(Product::className(), ['id' => 'product_id']);
	}
}
