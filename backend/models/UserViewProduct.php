<?php

namespace backend\models;

use Yii;
use common\helpers\Format;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class UserViewProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_view_product';
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

    public static function saveViewProduct($user_id, $product_id){
        $model = self::findOne(['user_id' => $user_id, 'product_id' => $product_id]);
        if( !$model ){
            $model          = new UserViewProduct;
            $model->user_id = $user_id;
            $model->product_id = $product_id;            
        }

        $model->last_time_view = date('Y-m-d H:i:s');
        $model->save(false);
    }

    public static function getListProductView($user_id, $limit = null, $offset = null){
        
        $query = self::find()
        ->select('B.*')
        ->from(self::tableName() . ' A')
        ->innerJoin(Product::tableName() . ' B', 'A.product_id = B.id')
        ->where(['A.user_id' => $user_id]);

        if( !is_null($limit) )
            $query->limit($limit);
        if( !is_null($offset) )
            $query->offset($offset);
        
        $query->orderBy(['A.last_time_view' => SORT_DESC]);

        $result = $query->asArray()->all();

        if( empty($result) ){
            return [];
        }
        $data   = [];

        $domain = Yii::$app->params['urlDomain'];
        foreach($result as $item){
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
                'quantity_sold' => $item['quantity_sold'],
                'is_favourites' => UserFavouriteProduct::checkStatusUserFavourites($user_id, $item['id'])
            ];

            $data[] = $row;
        }
       
        return $data;
    }
}
