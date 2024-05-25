<?php

namespace backend\models;

use Yii;

class AdvertisementCategory extends \yii\db\ActiveRecord
{
    const GROUP_TYPE_PRODUCT = 1; //Nhóm loại sản phẩm rao vặt
    const GROUP_TYPE_FUEL    = 2;//Nhóm loại nhiên liệu

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'advertisement_category';
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

    public static function getListItemGroup($group_type){
        $result = self::find()->where(['group_type' => $group_type, 'status' => self::STATUS_ACTIVE])->all();
        $data   = [];
        if( !empty($result) ){
            foreach($result as $row){
                $data[(int)$row->id] = $row->name;
            }
        }
        return $data;
    }
}
