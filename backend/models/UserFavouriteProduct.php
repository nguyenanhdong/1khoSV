<?php

namespace backend\models;

use Yii;
use common\helpers\Format;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class UserFavouriteProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_favourite_product';
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
    
    public static function checkStatusUserFavourites($user_id, $product_id){
        $model = self::findOne(['user_id' => $user_id, 'product_id' => $product_id]);
        return $model && $model->status ? true : false;
    }

    public static function toggleFavourites($user_id, $product_id){
        $model = self::findOne(['user_id' => $user_id, 'product_id' => $product_id]);
        if( $model ){
            $model->status = $model->status ? 0 : 1;
            $model->save(false);
            return $model->status ? true : false;
        }else{
            $model          = new UserFavouriteProduct;
            $model->user_id = $user_id;
            $model->product_id = $product_id;
            $model->update_at = date('Y-m-d H:i:s');
            $model->save(false);
            return true;
        }
    }

    public static function getListProductFavourites($user_id, $limit = null, $offset = null){
        
        $query = self::find()
        ->select('B.*')
        ->from(self::tableName() . ' A')
        ->innerJoin(Product::tableName() . ' B', 'A.product_id = B.id')
        ->where(['A.user_id' => $user_id, 'A.status' => 1]);

        if( !is_null($limit) )
            $query->limit($limit);
        if( !is_null($offset) )
            $query->offset($offset);
        
        $query->orderBy(['A.update_at' => SORT_DESC]);

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
                'is_favourites' => true
            ];

            $data[] = $row;
        }
       
        return $data;
    }
}
