<?php

namespace backend\models;

use Yii;

class Advertisement extends \yii\db\ActiveRecord
{
    const TYPE_BUY = 1;
    const TYPE_SELL= 2;

    const STATUS_PENDDING = 0;//Chờ duyệt
    const STATUS_ACTIVE = 1;//Admin duyệt
    const STATUS_ADMIN_REJECT = 3;//Admin từ chối duyệt
    const STATUS_ADMIN_REVOKE = 4;//Admin gỡ
    const STATUS_CUSTOM_REVOKE = 5;//KH gỡ
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'advertisement';
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

    public static function getAdvertisementHome( $type = null, $limit = null, $offset = null, $sortBy = 'NEWS' ){
        $condition  = ['status' => self::STATUS_ACTIVE];
        if( $type > 0 ){
            $condition['type'] = $type;
        }
        $result     = self::find()->where($condition);

        if( !is_null($limit) )
            $result->limit($limit);
        if( !is_null($offset) )
            $result->offset($offset);
        if( $sortBy == 'NEWS' )
            $result->orderBy(['date_publish' => SORT_DESC]);

        $result     = $result->asArray()->all();

        return self::getItemApp($result);
    }

    public static function getItemApp($listItem){
        $data   = [];
        $domain = Yii::$app->params['urlDomain'];
        foreach($listItem as $item){
            $price      = $item['price_discount'] ? $item['price_discount'] : $item['price'];
            $price_old  = $item['price'];
            $percent_discount = $item['price_discount'] && $item['price_discount'] < $price_old ? round((($item['price'] - $item['price_discount'])/$item['price'])*100) : 0;
            $imageShow = "";
            if( $item['image'] != "" ){
                $image =  explode(';', $item['image']);
                $imageShow = $domain . $image[0];
            }

            $row = [
                'id' => $item['id'],
                'name' => $item['title'],
                'price'=> $price,
                'price_old' => $price_old,
                'percent_discount' => $percent_discount,
                'image'=> $imageShow
            ];

            $data[] = $row;
        }
        
        return $data;
    }
}
