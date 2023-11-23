<?php

namespace backend\models;

use Yii;

class Services extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'services';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            
        ];
    }

    public static function getServiceById($service_id){
        $model = self::findOne($service_id);
        return $model;
    }

    public static function getListService($isAll = true){
        $condition_all = ['>','id',0];
        if( !$isAll )
            $condition_all = '(parent_id is not null or has_child = 0)';
        return \yii\helpers\ArrayHelper::map(self::find()->where(['is_active'=>1])->andWhere($condition_all)->all(),'id','name');
    }

    public static function getListServiceChild($parent_id){
        return \yii\helpers\ArrayHelper::map(self::find()->where(['is_active'=>1])->andWhere(['parent_id'=>$parent_id])->all(),'id','name');
    }
}
