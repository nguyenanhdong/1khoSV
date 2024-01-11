<?php

namespace backend\models;

use Yii;

class Product extends \yii\db\ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
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
            [['name'],'required','message'=>'Nhập {attribute}']
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'image' => 'Ảnh',
            'name' => 'Tên'
        ];
    }

    public static function getItemApp($listItem){
        $data   = [];
        $domain = Yii::$app->params['urlDomain'];
        foreach($listItem as $item){
            $price = $item['price_discount'] ? $item['price_discount'] : $item['price'];
            $price_old = $item['price'];
            $percent_discount = $item['price_discount'] && $item['price_discount'] < $price_old ? round((($item['price'] - $item['price_discount'])/$item['price'])*100) : 0;
            $imageShow = "";
            if( $item['image'] != "" ){
                $image =  explode(';', $item['image']);
                $imageShow = $domain . $image[0];
            }

            $row = [
                'id' => $item['id'],
                'name' => $item['name'],
                'price'=> $price,
                'price_old' => $price_old,
                'percent_discount' => $percent_discount,
                'image'=> $imageShow,
                'star' => $item['star'],
                'total_rate' => $item['total_rate'],
                'quantity_in_stock' => $item['quantity_in_stock']
            ];
            if( isset($item['date_end_sale']) ){
                $from    = new \DateTime(date('Y-m-d'));
                $end     = new \DateTime($item['date_end_sale']);
                $row['date_sale_remain'] = $end->diff($from)->format("%a");
            }


            $data[] = $row;
        }
        
        return $data;
    }

    public static function getProductByCategory($category_id = [], $type_get = 'popular', $limit = null, $offset = null){
        return self::queryProductApp($category_id, 0, $type_get, $limit, $offset);
    }

    public static function getProductByAgent($category_id = [], $agent_id = 0, $type_get = 'popular', $limit = null, $offset = null){
        return self::queryProductApp($category_id, $agent_id, $type_get, $limit, $offset);
    }

    private function queryProductApp($category_id = [], $agent_id = 0, $type_get = 'popular', $limit = null, $offset = null){
        $condition  = ['status' => self::STATUS_ACTIVE];
        
        $query      = self::find()
        ->where($condition)
        ->andWhere(['>', 'quantity_in_stock', 0]);
        
        if( !empty($category_id) ){
            $query->andWhere(['in', 'category_id', $category_id]);
        }

        if( $agent_id > 0 ){
            $query->andWhere(['agent_id' => $agent_id]);
        }
        
        $sortBy     = [];
        switch($type_get){
            case 'popular'://Phổ biến
                $sortBy = ['view_count' => SORT_DESC];
                break;
            case 'best-selling'://Bán chạy
                $sortBy = ['quantity_sold' => SORT_DESC];
                break;
            case 'new'://Hàng mới
                $sortBy = ['create_at' => SORT_DESC];
                break;
            case 'price_asc'://Giá thấp -> cao
                $sortBy = ['price_discount' => SORT_ASC];
                break;
            case 'price_desc'://Giá cao -> thấp
                $sortBy = ['price_discount' => SORT_DESC];
                break;
            case 'highlight'://Nổi bật
                $query->andWhere(['is_highlight' => 1]);
                $sortBy = ['quantity_sold' => SORT_DESC];
                break;
            default:
                break;
        }

        if( !is_null($limit) )
            $query->limit($limit);
        if( !is_null($offset) )
            $query->offset($offset);

        $query->orderBy($sortBy);

        $result = $query->asArray()->all();
        if( !empty($result) )
            return self::getItemApp($result);

        return [];
    }
}
