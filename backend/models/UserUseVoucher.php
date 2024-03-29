<?php

namespace backend\models;

use Yii;
use common\helpers\Format;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class UserUseVoucher extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_use_voucher';
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
    
    
    public static function insertHistory($user_id, $voucher_id){
        $model = new UserUseVoucher;
        $model->user_id         = $user_id;
        $model->voucher_id      = $voucher_id;
        $model->save(false);
    }
}
