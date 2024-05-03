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
            $model->status = 0;
            $model->save(false);
            return false;
        }else{
            $model          = new UserFavouriteProduct;
            $model->user_id = $user_id;
            $model->product_id = $product_id;
            $model->save(false);
            return true;
        }
    }
}
