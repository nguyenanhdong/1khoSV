<?php

namespace backend\models;

use common\helpers\Response;
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
            if( !$modelOrder ){
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
}
