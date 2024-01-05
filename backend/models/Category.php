<?php

namespace backend\models;

use Yii;

class Category extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'],'required','message'=>'Nhập {attribute}']
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'image' => 'Ảnh',
            'name' => 'Tên'
        ];
    }

    public static function getListCateApp($parent_id = 0, $limit = null, $offset = null, $domain = ''){
        $params     = [":parent_id" => $parent_id];
        $condition  = "is_delete = 0 and status = 1 and parent_id = :parent_id";
        $page_navi  = "";

        if( !is_null($limit) && !is_null($offset) ){
            $page_navi = "LIMIT $offset,$limit";
        }elseif( !is_null($limit)){
            $page_navi = "LIMIT $limit";
        }

        $sql = "
            SELECT id, name, concat('$domain',image) as image
            FROM category WHERE $condition $page_navi
        ";
        
        return Yii::$app->db->CreateCommand($sql, $params)->queryAll();
    }
}
