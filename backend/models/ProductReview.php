<?php

namespace backend\models;

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
}
