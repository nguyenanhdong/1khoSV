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
        $domain     = Yii::$app->params['urlDomain'];
        $listCategoryParent = self::find()->where(['parent_id' => 0, 'is_delete' => 0, 'status' => self::STATUS_ACTIVE])->limit($limit)->offset($offset)->asArray()->all();
        
        if( !empty($listCategoryParent) ){
            foreach($listCategoryParent as $row){
                $dataCategory   = [];
                $cate_id        = $row['id'];
                $listCategoryChild = self::find()->select(['id', 'name', new Expression("concat('$domain',image) as image")])->where(['parent_id' => $cate_id, 'is_delete' => 0, 'status' => self::STATUS_ACTIVE])->asArray()->all();
                if( !empty($listCategoryChild) ){
                    $dataCategory = [
                        'id'      => $row['id'],
                        'name'    => $row['name'],
                        'cate_child' => $listCategoryChild,
                        'product' => []
                    ];
                    $listId = ArrayHelper::map($listCategoryChild, 'id', 'id');
                    //Lấy sản phẩm nổi bật
                    $listProductHightLight = Product::find()->where(['is_highlight' => 1, 'status' => Product::STATUS_ACTIVE])->andWhere(['in', 'category_id' , $listId])->andWhere(['>', 'quantity_in_stock', 0])->orderBy(['quantity_sold' => SORT_DESC])->limit(10)->all();
                    if( !empty($listProductHightLight) ){
                        
                        $dataCategory['product'] = Product::getItemApp($listProductHightLight);
                    }
                    $data[] = $dataCategory;
                }
            }
        }
        
        return $data;
    }
}
