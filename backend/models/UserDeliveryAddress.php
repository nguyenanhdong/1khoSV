<?php

namespace backend\models;

use Yii;
use common\helpers\Format;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class UserDeliveryAddress extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_delivery_address';
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
    
    public static function getListDeliveryAddressOfUser($user_id, $limit = null, $offset = null){
        
        $query = self::find()->where(['user_id' => $user_id]);

        if( !is_null($limit) )
            $query->limit($limit);
        if( !is_null($offset) )
            $query->offset($offset);

        $result = $query->asArray()->all();
        if( !empty($result) )
            return self::getItemApp($result);

        return [];
    }

    public static function getItemApp($listItem){
        $data   = [];
        foreach($listItem as $item){
            
            $row = [
                'id' => $item['id'],
                'fullname'=> $item['fullname'],
                'phone'   => $item['phone'],
                'address' => $item['address'],
                'district'=> $item['district'],
                'province'=> $item['province'],
                'is_primary' => (int)$item['is_save_primary_address']
            ];
            
            $data[] = $row;
        }
        
        return $data;
    }

    public static function getDeliveryAddressOrderDefault($user_id, $id = 0){
        if( $id > 0 )
            $model = self::findOne(['user_id' => $user_id, 'id' => $id]);
        else
            $model = self::findOne(['user_id' => $user_id, 'is_save_primary_address' => 1]);
        if( !$model )
            return null;

        return [
            'id' => $model->id,
            'address' => $model->address,
            'district'=> $model->district,
            'province'=> $model->province
        ];
    }

    public static function addDeliveryAddress($params, $user_id){
        $fullname   = $params['fullname'];
        $phone      = $params['phone'];
        $district   = $params['district'];
        $province   = $params['province'];
        $address    = $params['address'];
        $is_primary_address    = $params['is_primary_address'];

        if( $is_primary_address == 1 ){
            self::updateAll([
                'is_save_primary_address' => 0,
            ], ['user_id' => $user_id]);
        }
        $model      = new UserDeliveryAddress;
        $model->user_id = $user_id;
        $model->fullname = $fullname;
        $model->phone = $phone;
        $model->district = $district;
        $model->province = $province;
        $model->address = $address;
        $model->is_save_primary_address = $is_primary_address;
        $model->save(false);

        return [
            'id' => $model['id'],
            'fullname'=> $model['fullname'],
            'phone'   => $model['phone'],
            'address' => $model['address'],
            'district'=> $model['district'],
            'province'=> $model['province'],
            'is_primary' => (int)$model['is_save_primary_address']
        ];
    }
}
