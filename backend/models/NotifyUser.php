<?php

namespace backend\models;

use Yii;
use yii\db\Expression;

class NotifyUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notify_user';
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

    public static function getDetailNotify($id, $userId, $typeUser = 1){
        $columnSelect   = ["A.id", new Expression("B.obj_id as id_order"), "B.title", "B.description", "B.content", new Expression("date_format(B.create_at,'%H:%i - %d/%m/%Y') as date"), "A.is_viewed"];
        
        $condition      = ["A.id" => $id, "A.user_id" => $userId, "A.type_user" => $typeUser, "A.is_delete" => 0];
        
        $query          = self::find()->select($columnSelect)->from(self::tableName() . ' A')
        ->innerJoin( Notify::tableName() . ' B', 'A.notify_id = B.id');

        $query->where($condition);

       
        $result = $query->asArray()->one();
        if( $result && !$result['is_viewed'] ){
            self::updateAll([
                'is_viewed' => 1,
                'time_seen' => date('Y-m-d H:i:s')
            ], ['id' => $result['id']]);
            $result['is_viewed'] = 1;
        }
        return $result;
    }

    public static function getListNotifyByUser($userId = 0, $typeUser = 1, $limit = null, $offset = null){
        $columnSelect   = ["A.id", new Expression("B.obj_id as id_order"), "B.title", "B.description", new Expression("date_format(B.create_at,'%H:%i - %d/%m/%Y') as date"), "A.is_viewed", "A.type_user" ];
        $condition      = ["A.user_id" => $userId, "A.type_user" => $typeUser, "A.is_delete" => 0];
        
        $query          = self::find()->select($columnSelect)->from(self::tableName() . ' A')
        ->innerJoin( Notify::tableName() . ' B', 'A.notify_id = B.id');

        $query->where($condition);

        if( !is_null($limit) )
            $query->limit($limit);
        if( !is_null($offset) )
            $query->offset($offset);

        $result = $query->orderBy(["A.id" => SORT_DESC])->asArray()->all();
        if( !empty($result) )
            return self::getItemApp($result);

        return [];
    }

    public static function getItemApp($listItem){
        $data   = [];
        foreach($listItem as $item){
            
            $row = [
                'id'    => $item['id'],
                'title' => $item['title'],
                'desc'  => $item['description'],
                'id_order' => (int)$item['id_order'],
                'is_viewed' => (int)$item['is_viewed'],
                'date'=> $item['date'],
            ];
            
            $data[] = $row;
        }
        
        return $data;
    }
}
