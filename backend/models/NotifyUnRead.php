<?php

namespace backend\models;

use Exception;
use Yii;

class NotifyUnRead extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notify_unread';
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

    public static function saveUnread($user_id, $total, $type_user){
        $model = self::findOne(['user_id' => $user_id]);
        if( !$model ){
            $model = new NotifyUnRead;
            $model->user_id = $user_id;
            $model->type_user = $type_user;
            $model->total = 0;
        }
        
        $model->total = $model->total + $total;
        $model->save(false);
    }
}
