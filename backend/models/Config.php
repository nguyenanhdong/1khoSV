<?php

namespace backend\models;

use Yii;

class Config extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'config';
    }
    public function beforeSave($insert){
        if( $this->type == 0 )
            $this->value = strip_tags($this->value);
       
        return parent::beforeSave($insert);
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type','description','name','value'],'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên cấu hình',
            'description'=> 'Mô tả',
            'type' => 'Loại giá trị',
            'value'=> 'Giá trị'
        ];
    }
}
