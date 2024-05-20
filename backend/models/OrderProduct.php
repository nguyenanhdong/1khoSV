<?php

namespace backend\models;

use Yii;
use common\helpers\Format;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class OrderProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_product';
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
    
    public static function getProductByOrderId($order_id){
        $query = self::find()
        ->select('B.id, B.name, B.image, A.price_origin, A.price, A.total_price, A.quantity, C.fullname as agent_name')
        ->from(self::tableName() . ' A')
        ->innerJoin(Product::tableName() . ' B', 'A.product_id = B.id')
        ->leftJoin(Agent::tableName() . ' C', 'B.agent_id = C.id')
        ->where(['A.order_id' => $order_id]);

        $result = $query->asArray()->one();

        return $result;
    }
}
