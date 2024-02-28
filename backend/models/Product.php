<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;

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

    private static function queryProductApp($category_id = [], $agent_id = 0, $type_get = 'popular', $limit = null, $offset = null){
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

    public static function getProductDetail($id, $user_id_current = 0){
        $model = self::findOne($id);
        
        if( !$model || !$model->status ){
            return null;
        }

        $domain             = Yii::$app->params['urlDomain'];

        $price              = $model['price_discount'] ? $model['price_discount'] : $model['price'];
        $price_old          = $model['price'];
        $percent_discount   = $model['price_discount'] && $model['price_discount'] < $price_old ? round((($model['price'] - $model['price_discount'])/$model['price'])*100) : 0;
        
        $images             = [];
        if( $model['image'] != "" ){
            $images         =  explode(';', $model['image']);
            $images         = preg_filter('/^/', $domain, $images);
        }
        $videos             = [];
        if( $model['video'] != "" ){
            $videos         = explode(';', $model['video']);
            $videos         = preg_filter('/^/', $domain, $videos);
        }

        $state_name         = $model['state'] == 1 ? 'Mới' : 'Đã sử dụng';

        $dataReview         = ProductReview::getReviewByProductId($model->id, 3);
        $agentInfo          = $model['agent_id'] ? Agent::getInfoAgent($model['agent_id']) : null;

        if( $agentInfo ){
            $agentInfo['is_follow']    = $user_id_current ? UserFollowAgent::checkUserFollowAgent($user_id_current, $model['agent_id']) : false;
        }


        $productSuggest     = self::getProductByCategory([$model->category_id], 'popular', 8);

        $productClassification = ProductClassificationCombination::getListCombination($model->id);

        $data = [
            'product_info' => [
                'id' => $model->id,
                'name' => $model->name,
                'description' => $model->description,
                'price'=> $price,
                'price_old' => $price_old,
                'percent_discount' => $percent_discount,
                'images' => $images,
                'videos' => $videos,
                'origin' => $model->origin,
                'state' => $state_name,
                'weight' => $model->weight,
                'width' => $model->width,
                'height' => $model->height,
                'length' => $model->length,
                'star' => $model->star,
                'total_rating' => $model->total_rate,
                'quantity_sold' => $model->quantity_sold,
                'classification_group' => $productClassification['group'],
                'classification_data' => $productClassification['data'],
            ],
            'review_info' => $dataReview,
            'agent_info'  => $agentInfo,
            'product_suggest' => $productSuggest
        ];


        return $data;
    }

    public static function getFeeShipProduct($product_combination){
        $listCombinationId  = ArrayHelper::map($product_combination, 'id', 'id');
        $resultCombination  = ProductClassificationCombination::find()->select('A.*, B.weight')
        ->from(ProductClassificationCombination::tableName() . ' A')
        ->leftJoin(self::tableName() . ' B', 'A.product_id = B.id')
        ->where(['in', 'A.id', $listCombinationId])->asArray()->all();
        
        if( empty($resultCombination) )
            return 0;

        $config = Config::findOne(['key' => 'FEE_SHIP']);
        
        if( !$config || !$config->value )
            return 0;

        $fee_ship = 0;
        foreach($resultCombination as $rows){
            $product_id = $rows['product_id'];
            foreach($product_combination as $combination){
                if( $combination['id'] == $rows['id'] && $combination['quantity'] > 0 ){
                    $fee_ship += $rows['weight'] * $config->value;
                    break;
                }
            }
        }

        return $fee_ship;//number_format($fee_ship, 0, '.', '.');
    }


    public static function getPriceOfOrder($product_combination){
        $listCombinationId  = ArrayHelper::map($product_combination, 'id', 'id');
        $resultCombination  = ProductClassificationCombination::find()->where(['in', 'id', $listCombinationId])->asArray()->all();
        
        if( empty($resultCombination) )
            return 0;
        
        $price = 0;
        foreach($resultCombination as $rows){
            foreach($product_combination as $combination){
                if( $combination['id'] == $rows['id'] && $combination['quantity'] > 0 ){
                    $price += $rows['price'] * $combination['quantity'];
                    break;
                }
            }
        }

        return $price;//number_format($price, 0, '.', '.');
    }
}
