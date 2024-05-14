<?php

namespace backend\models;

use common\helpers\Response;
use yii\db\Expression;
use Yii;

class ProductReview extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product_review';
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


    public static function getReviewByProductId($product_id, $limit = null, $offset = null){
        
        $query = self::find()
        ->select('A.*, B.fullname, B.avatar')
        ->from(self::tableName() . ' A')
        ->innerJoin(Customer::tableName() . ' B', 'A.user_id = B.id')
        ->where(['product_id' => $product_id]);

        if( !is_null($limit) )
            $query->limit($limit);
        if( !is_null($offset) )
            $query->offset($offset);
        
        $query->orderBy(['A.create_at' => SORT_DESC]);

        $result = $query->asArray()->all();

        if( empty($result) ){
            return [];
        }
        $data   = [];

        foreach($result as $item){
            $video_image = [];
            if( $item['video_image'] && strpos($item['video_image'], '[') !== false ){
                $video_image = json_decode($item['video_image'], true);
                $domain      = Yii::$app->params['urlDomain'];
                $video_image = preg_filter('/^/', $domain, $video_image);
            }
            $data[] = [
                'fullname' => $item['fullname'] ? $item['fullname'] : '',
                'avatar' => $item['avatar'] ? $item['avatar'] : '',
                'date_review' => date('d/m/Y', strtotime($item['create_at'])),
                'point_review'=> $item['rating_point'],
                'content'     => $item['content'],
                'video_image' => $video_image
            ];
        }
       
        return $data;
    }

    public static function checkUserReviewOrder($user_id, $order_id){
        $model = self::findOne(['user_id' => $user_id, 'order_id' => $order_id]);
        return $model ? false : true;
    }

    public static function createReview($user_id, $params){
        $order_id           = $params['order_id'];
        $product_id         = $params['product_id'];
        $video_image        = $params['video_image_review'];
        $star               = $params['star_review'];
        $content            = $params['content_review'];

        $flagCanReview      = self::checkUserReviewOrder($user_id, $order_id);
        if( $flagCanReview ){
            $modelOrder= Order::findOne(['id' => $order_id, 'user_id' => $user_id]);
            if( !$modelOrder || $modelOrder->status != Order::STATUS_PURCHASED ){
                return [
                    'status' => false,
                    'message'=> Response::getErrorMessage('order', Response::KEY_NOT_FOUND)
                ];
            }

            $modelOrderProduct= OrderProduct::findOne(['order_id' => $order_id, 'product_id' => $product_id]);
            if( !$modelOrderProduct ){
                return [
                    'status' => false,
                    'message'=> 'Thông tin đơn hàng, sản phẩm không hợp lệ'
                ];
            }

            $modelProduct   = Product::findOne($product_id);
            if( !$modelProduct ){
                return [
                    'status' => false,
                    'message'=> Response::getErrorMessage('product', Response::KEY_NOT_FOUND)
                ];
            }

            $model          = new ProductReview;
            $model->user_id = $user_id;
            $model->order_id= $order_id;
            $model->product_id = $product_id;
            $model->rating_point = $star;
            $model->content    = strip_tags($content);
            $model->video_image= json_encode($video_image);
            $model->save(false);

            $prod_star = $modelProduct->star;
            $prod_total_rate = $modelProduct->total_rate + 1;
            $prod_total_rate_point = $modelProduct->total_rate_point + $star;
            $prod_star = round($prod_total_rate_point/$prod_total_rate);

            $modelProduct->star = $prod_star;
            $modelProduct->total_rate = $prod_total_rate;
            $modelProduct->total_rate_point = $prod_total_rate_point;
            $modelProduct->save(false);

            return [
                'status' => true,
                'message'=> 'Đánh giá sản phẩm thành công'
            ];
        }else{
            return [
                'status' => false,
                'message'=> 'Bạn đã đánh giá sản phẩm rồi'
            ];
        }
    }

    public static function getListReviewOfUser($user_id, $type, $limit = null, $offset = null){
        if( $type == 0 ){//Chưa đánh giá
            $query = self::find()
            ->select('A.id, D.id as product_id, D.name as product_name, D.image as product_img, E.fullname as agent_name, C.price_origin, C.price, C.total_price, C.quantity')
            ->from(Order::tableName() . ' A')
            ->leftJoin(self::tableName() . ' B', 'A.id = B.order_id')
            ->innerJoin(OrderProduct::tableName() . ' C', 'A.id = C.order_id')
            ->innerJoin(Product::tableName() . ' D', 'C.product_id = D.id')
            ->leftJoin(Agent::tableName() . ' E', 'D.agent_id = E.id')
            ->where(['A.user_id' => $user_id, 'A.status' => Order::STATUS_PURCHASED])
            ->andWhere(['IS', new Expression("B.id"), NULL]);
        }else{//Đã đánh giá
            $query = self::find()
            ->select('A.*, B.fullname, B.avatar')
            ->from(self::tableName() . ' A')
            ->innerJoin(Customer::tableName() . ' B', 'A.user_id = B.id')
            ->where(['A.user_id' => $user_id]);
        }

        if( !is_null($limit) )
            $query->limit($limit);
        if( !is_null($offset) )
            $query->offset($offset);
        
        $query->orderBy(['A.create_at' => SORT_DESC]);

        $result = $query->asArray()->all();

        if( empty($result) ){
            return [];
        }
        $data   = [];

        $domain = Yii::$app->params['urlDomain'];
        foreach($result as $item){

            if( $type == 0 ){
                $imageShow = "";
                if( $item['product_img'] != "" ){
                    $image =  explode(';', $item['product_img']);
                    $imageShow = $domain . $image[0];
                }
                $price      = $item['price'];
                $price_old  = $item['price_origin'];
                $percent_discount = $price < $price_old ? round((($price_old - $price)/$price_old)*100) : 0;
                $data[] = [
                    'order_id' => (int)$item['id'],
                    'product_id' => (int)$item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_img' => $imageShow,
                    'quantity'    => (int)$item['quantity'],
                    'agent_name'  => $item['agent_name'] ? $item['agent_name'] : '1KHO',
                    'price'=> $price,
                    'price_old' => $price_old,
                    'percent_discount' => $percent_discount,
                    'status_name' => 'Đã mua'
                ];
            }else{
                $video_image = [];
                if( $item['video_image'] && strpos($item['video_image'], '[') !== false ){
                    $video_image = json_decode($item['video_image'], true);
                    $domain      = Yii::$app->params['urlDomain'];
                    $video_image = preg_filter('/^/', $domain, $video_image);
                }
                $data[] = [
                    'fullname' => $item['fullname'] ? $item['fullname'] : '',
                    'avatar' => $item['avatar'] ? $item['avatar'] : '',
                    'date_review' => date('d/m/Y', strtotime($item['create_at'])),
                    'point_review'=> $item['rating_point'],
                    'content'     => $item['content'],
                    'video_image' => $video_image
                ];
            }

            
        }
       
        return $data;
    }
}
