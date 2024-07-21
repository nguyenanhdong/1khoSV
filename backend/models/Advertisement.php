<?php

namespace backend\models;

use Yii;
use common\helpers\Response;
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
            [['category_id'],'required','message'=>'Chọn {attribute}'],
            [['type_strain', 'brand_name', 'origin', 'title', 'description', 'kilometer_used', 'hours_of_use', 'production_year', 'fuel_id'],'required','message'=>'Nhập {attribute}']
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Loại tin giao vặt',
            'user_id' => 'Mã khách hàng',
            'phone' => 'Số điện thoại',
            'category_id' => 'Chuyên mục',
            'video' => 'Link video sản phẩm',
            'image' => 'Link ảnh sản phẩm',
            'type_strain' => 'Chủng loại',
            'load_capacity' => 'Trọng tải',
            'state' => 'Tình trạng sản phẩm',
            'brand_name' => 'Thương hiệu',
            'origin' => 'Xuất xứ',
            'title' => 'Tiêu đề',
            'description' => 'Mô tả',
            'kilometer_used' => 'Số km sử dụng',
            'hours_of_use' => 'Số giờ sử dụng',
            'production_year' => 'Năm sản xuất',
            'fuel_id' => 'Nhiên liệu',
            'Status' => 'Trạng thái',
            'price' => 'Giá mua/bán',
            'price_discount' => 'Giá khuyến mại',
            'create_at' => 'Ngày tạo',
            'date_publish' => 'Ngày được duyệt',
            'is_hot' => 'Là tin hot',
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

    public static function getFormInfo(){
        $data = [
            'list_category' => AdvertisementCategory::getListItemGroup(AdvertisementCategory::GROUP_TYPE_PRODUCT),
            'list_fuel'     => AdvertisementCategory::getListItemGroup(AdvertisementCategory::GROUP_TYPE_FUEL),
        ];

        return $data;
    }

    public static function createNewAdvertisement($params, $user_id){
        
        $listCategory   = AdvertisementCategory::getListItemGroup(AdvertisementCategory::GROUP_TYPE_PRODUCT);
        $listFuel       = AdvertisementCategory::getListItemGroup(AdvertisementCategory::GROUP_TYPE_FUEL);

        $type_adv       = $params['type_adv'];
        $phone          = $params['phone'];
        $title_adv      = $params['title_adv'];
        $category_adv   = $params['category_adv'];
        $video_adv      = $params['video_adv'];
        $image_adv      = $params['image_adv'];
        $type_strain    = $params['type_strain'];
        $load_capacity  = $params['load_capacity'];
        $state_adv      = $params['state_adv'];
        $brand_name     = $params['brand_name'];
        $origin_adv     = $params['origin_adv'];
        $description_adv= $params['description_adv'];
        $kilometer_used = $params['kilometer_used'];
        $hours_of_use   = $params['hours_of_use'];
        $production_year= $params['production_year'];
        $fuel_adv       = $params['fuel_adv'];
        $price_adv      = $params['price_adv'];

        if( !isset($listCategory[$category_adv]) ){
            return [
                'status' => false,
                'msg'    => Response::getErrorMessage('category_adv', Response::KEY_INVALID)
            ];
        }

        if( !isset($listFuel[$fuel_adv]) ){
            return [
                'status' => false,
                'msg'    => Response::getErrorMessage('fuel_adv', Response::KEY_INVALID)
            ];
        }

        if( !empty($video_adv) ){
            
        }

        $model = new Advertisement;
        $model->type = $type_adv;
        $model->user_id = $user_id;
        $model->phone = $phone;
        $model->category_id = $category_adv;
        $model->video = !empty($video_adv) ? json_encode($video_adv) : null;
        $model->image = !empty($image_adv) ? json_encode($image_adv) : null;
        $model->type_strain = $type_strain;
        $model->load_capacity = $load_capacity;
        $model->state = $state_adv;
        $model->brand_name = $brand_name;
        $model->origin = $origin_adv;
        $model->title = $title_adv;
        $model->description = $description_adv;
        $model->kilometer_used = $kilometer_used;
        $model->hours_of_use = $hours_of_use;
        $model->production_year = $production_year;
        $model->fuel_id = $fuel_adv;
        $model->price = $price_adv;
        $model->save(false);

        return [
            'status' => true,
            'msg'    => 'Đăng tin thành công'
        ];
    }

}
