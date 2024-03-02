<?php

namespace backend\models;

use Yii;

class HistoryWalletPoint extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'history_wallet_point';
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
            'id' => 'ID'
        ];
    }

    public static function insertHistory($user_id, $type, $note, $obj_id, $point){
        $model = new HistoryWalletPoint;
        $model->user_id = $user_id;
        $model->type    = $type;
        $model->note    = $note;
        $model->obj_id  = $obj_id;
        $model->point   = $point;
        $model->save(false);
    }
}
