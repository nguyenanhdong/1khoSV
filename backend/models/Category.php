<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\db\Expression;

class Category extends \yii\db\ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
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

    public static function getListCateApp($parent_id = 0, $limit = null, $offset = null){
        $domain     = Yii::$app->params['urlDomain'];
        $params     = [":parent_id" => $parent_id, ':status' => self::STATUS_ACTIVE];
        $condition  = "is_delete = 0 and status = :status and parent_id = :parent_id";
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

    public static function getCateProductHome($limit = 4, $offset = 0){
        $data       = [];
        $listCategoryParent = self::find()->where(['parent_id' => 0, 'is_delete' => 0, 'status' => self::STATUS_ACTIVE])->limit($limit)->offset($offset)->asArray()->all();
        
        if( !empty($listCategoryParent) ){
            foreach($listCategoryParent as $row){
                $dataCategory   = [];
                $cate_id        = $row['id'];
                $listCategoryChild = self::getAllChildByParentId($cate_id);
                if( !empty($listCategoryChild) ){
                    $dataCategory = [
                        'id'      => $row['id'],
                        'name'    => $row['name'],
                        'cate_child' => $listCategoryChild,
                        'product' => []
                    ];
                    $listId = ArrayHelper::map($listCategoryChild, 'id', 'id');
                    //Lấy sản phẩm nổi bật
                    $listProductHightLight = Product::getProductByCategory($listId, 'highlight', 10);
                    if( !empty($listProductHightLight) ){
                        
                        $dataCategory['product'] = $listProductHightLight;
                    }
                    $data[] = $dataCategory;
                }
            }
        }
        
        return $data;
    }

    public static function getAllChildByParentId($parent_id){
        $domain     = Yii::$app->params['urlDomain'];
        $listCategoryChild = self::find()->select(['id', 'name', new Expression("concat('$domain',image) as image")])->where(['parent_id' => $parent_id, 'is_delete' => 0, 'status' => self::STATUS_ACTIVE])->asArray()->all();
        return $listCategoryChild;
    }

    public static function getListCateAppByAgent($agent_id = 0, $limit = null, $offset = null){
        $domain     = Yii::$app->params['urlDomain'];
        $params     = [":parent_id" => 0, ':status' => self::STATUS_ACTIVE, ':agent_id' => $agent_id, ':prod_status' => Product::STATUS_ACTIVE];
        $condition  = "A.is_delete = 0 and A.status = :status and A.parent_id = :parent_id and C.agent_id = :agent_id and C.status = :prod_status and C.quantity_in_stock > 0";
        $page_navi  = "";

        if( !is_null($limit) && !is_null($offset) ){
            $page_navi = "LIMIT $offset,$limit";
        }elseif( !is_null($limit)){
            $page_navi = "LIMIT $limit";
        }

        $sql = "
            SELECT A.id, A.name, concat('$domain', A.image) as image
            FROM category A
            LEFT JOIN category B ON A.id = B.parent_id
            LEFT JOIN product C ON B.id = C.category_id
            WHERE $condition $page_navi
            GROUP BY A.id
        ";
        
        return Yii::$app->db->CreateCommand($sql, $params)->queryAll();
    }

}
