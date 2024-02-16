<?php

namespace backend\models;

use Yii;
use common\helpers\Format;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class UserFollowAgent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_follow_agent';
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
    
    public static function checkUserFollowAgent($user_id, $agent_id){
        $model = self::findOne(['user_id' => $user_id, 'agent_id' => $agent_id]);
        return $model ? true : false;
    }

    public static function toggleFollowAgent($user_id, $agent_id){
        $model = self::findOne(['user_id' => $user_id, 'agent_id' => $agent_id]);
        if( $model ){
            $model->delete();
            return false;
        }else{
            $model = new UserFollowAgent;
            $model->user_id = $user_id;
            $model->agent_id = $agent_id;
            $model->save(false);
            return true;
        }
    }
}
