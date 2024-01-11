<?php

namespace backend\models;

use Yii;
use common\helpers\Format;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class Agent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'agent';
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
    
    public static function getInfoAgent($id){
        $data   = null;
        $model  = self::findOne($id);
        if( $model ){
            $domain = Yii::$app->params['urlDomain'];
            $data   = [
                'id' => $id,
                'name' => $model->fullname,
                'avatar' => $domain . $model->avatar,
                'cover' => $domain . $model->cover_img,
                'total_follow' => $model->follow_count
            ];
        }

        return $data;
    }
}
