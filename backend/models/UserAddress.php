<?php

namespace backend\models;

use Yii;

class UserAddress extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_address';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            
        ];
    }

    public static function saveAddress($arrAddress,$user_id){
        self::deleteAll(['user_id' => $user_id]);
        if( !is_array($arrAddress) )
            $arrAddress = [$arrAddress];
        foreach($arrAddress as $address){
            if( !empty($address) ){
                $model = new UserAddress;
                $model->user_id = $user_id;
                $model->address = $address;
                $model->save(false);
            }
        }
    }
}
