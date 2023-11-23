<?php

namespace backend\models;

use Yii;

class CaundryCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'caundry_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'],'required','message'=>'Nhập {attribute}'],
            [['service_id'],'required','message'=>'Chọn {attribute}'],
            [['service_id','name'],'safe']
        ];
    }
    public function beforeSave($insert){
        return parent::beforeSave($insert);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên nhóm',
            'service_id' => 'Dịch vụ',
            'status' => 'Trạng thái',
        ];
    }
}
