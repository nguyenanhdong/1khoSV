<?php

namespace backend\models;

use Yii;

class ProductSale extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product_sale';
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

    public static function getProductSale($category_id = 0, $agent_id = 0, $limit = null, $offset = null){
        $condition       = ['A.status' => Product::STATUS_ACTIVE];
        
        if( $agent_id > 0 ){
            $condition['A.agent_id'] = $agent_id;
        }
        $listProductSale = Product::find()
        ->select('A.*, B.date_start as date_start_sale, B.date_end as date_end_sale')
        ->from(Product::tableName() . ' A')
        ->innerJoin(self::tableName() . ' B', 'A.id = B.product_id')
        ->where($condition)
        ->andWhere(['>', 'A.quantity_in_stock', 0])
        ->andWhere(['<=', 'B.date_start', date('Y-m-d')])
        ->andWhere(['>=', 'B.date_end', date('Y-m-d')]);
        if( (is_numeric($category_id) && $category_id > 0) || (is_array($category_id) && !empty($category_id)) ){
            
            if( is_array($category_id) && !empty($category_id) )
                $listProductSale->andWhere(['in', 'A.category_id', $category_id]);
            else
                $listProductSale->andWhere(['A.category_id' => $category_id]);
        }
        
        $listProductSale->orderBy(['A.quantity_sold' => SORT_DESC]);

        if( !is_null($limit) )
            $listProductSale->limit($limit);
        if( !is_null($offset) )
            $listProductSale->offset($offset);
        
        $listProductSale = $listProductSale->asArray()->all();
        if( !empty($listProductSale) ){
            return Product::getItemApp($listProductSale);
        }
        return [];
    }
}
