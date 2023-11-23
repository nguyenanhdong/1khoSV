<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "router".
 *
 * @property integer $id
 * @property string $name
 * @property integer $type
 * @property string $list_time
 * @property integer $id_bus
 * @property integer $index_router
 * @property integer $status
 */
class Router extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'router';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','id_bus','id_bus'],'required','message'=>'Nhập {attribute}'],
            [['type', 'id_bus', 'index_router', 'status'], 'integer'],
            [['name'], 'string', 'max' => 300],
            [['list_time'], 'safe'],
        ];
    }
    public function beforeSave($insert){
        
        if( is_array($this->list_time) && !empty($this->list_time) ){
            $list_time       = array_filter($this->list_time);
            asort($list_time);
            foreach($list_time as $key=>$time){

                $list_time[$key] = substr($time,0,2) . ':' . substr($time,-2);
            }
            $this->list_time = implode(',', $list_time);
        }else
            $this->list_time = '';
        return parent::beforeSave($insert);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên',
            'type' => 'Loại',
            'list_time' => 'Thời gian',
            'id_bus' => 'Xe bus',
            'index_router' => 'Vị trí',
            'status' => 'Trạng thái',
        ];
    }
}
