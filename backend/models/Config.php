<?php

namespace backend\models;

use Yii;

class Config extends \yii\db\ActiveRecord
{
    const TYPE_STRING = 0;
    const TYPE_HTML = 1;
    const TYPE_JSON = 2;
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

    public static function getConfigApp($config_key){
        $model = self::findOne(['key' => $config_key]);
        if( $model ){
            $value = $model->value;
            if( $model->type == self::TYPE_JSON ){
                $value = json_decode($value, true);
            }

            return $value;
        }
        return null;
    }
}
