<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;

class ProductClassificationCombination extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product_classification_combination';
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

    /** Tiền tố id dòng giỏ hàng của sản phẩm không có phân loại: "p{product_id}" */
    const PLAIN_PREFIX = 'p';

    /** Id dòng giỏ hàng: id phân loại, hoặc "p{product_id}" nếu sản phẩm không có phân loại */
    public static function lineId($product_id, $classification_id)
    {
        return !empty($classification_id) ? $classification_id : self::PLAIN_PREFIX . (int)$product_id;
    }

    /**
     * Đọc các dòng đặt hàng [['id' => ..., 'quantity' => ...]] thành dòng giá.
     * id số = phân loại sản phẩm (như app gửi); id "p{product_id}" = sản phẩm không phân loại, giá = giá khuyến mãi nếu có, ngược lại giá gốc.
     * Mỗi dòng trả về: id, product_id, price, name, weight, agent_id, price_origin (+ các cột của bảng phân loại nếu có).
     */
    public static function resolveLines($product_combination)
    {
        $comboIds = [];
        $productIds = [];
        foreach ((array)$product_combination as $line) {
            $id = (string)($line['id'] ?? '');
            if ($id !== '' && $id[0] === self::PLAIN_PREFIX && ctype_digit(substr($id, 1))) {
                $productIds[] = (int)substr($id, 1);
            } elseif (ctype_digit($id)) {
                $comboIds[] = (int)$id;
            }
        }
        $rows = [];
        if ($comboIds) {
            $rows = self::find()->select('A.*, B.name, B.weight, B.agent_id, B.price as price_origin')
                ->from(self::tableName() . ' A')
                ->leftJoin(Product::tableName() . ' B', 'A.product_id = B.id')
                ->where(['in', 'A.id', $comboIds])->asArray()->all();
        }
        if ($productIds) {
            $products = Product::find()->select(['id', 'name', 'price', 'price_discount', 'weight', 'agent_id'])
                ->where(['id' => $productIds, 'status' => Product::STATUS_ACTIVE])
                ->andWhere(['or', ['is_delete' => 0], ['is_delete' => null]])
                // Sản phẩm đã có phân loại thì bắt buộc đặt theo phân loại
                ->andWhere(['not exists', self::find()->from(self::tableName() . ' C')->where('C.product_id = product.id')])
                ->asArray()->all();
            foreach ($products as $p) {
                $rows[] = [
                    'id' => self::PLAIN_PREFIX . $p['id'], 'product_id' => $p['id'], 'classification_id' => '',
                    'price' => $p['price_discount'] ? $p['price_discount'] : $p['price'], 'price_discount' => 0,
                    'name' => $p['name'], 'weight' => $p['weight'], 'agent_id' => $p['agent_id'], 'price_origin' => $p['price'],
                ];
            }
        }
        return $rows;
    }

    public static function _combinationsData($arrays, $i = 0) {
        if (!isset($arrays[$i])) {
            return array();
        }
        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }
    
        // get combinations from subsequent arrays
        $tmp = self::_combinationsData($arrays, $i + 1);
    
        $result = array();
    
        // concat each array from tmp with each element from $arrays[$i]
        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ? 
                    array_merge(array($v), $t) :
                    array($v, $t);
            }
        }
    
        return $result;
    }

    public static function getListCombination($product_id){
        $result = self::find()->where(['product_id' => $product_id])->asArray()->all();
        if( empty($result) ){
            return [];
        }
        $data = ['group' => [], 'data' => []];
        $listClassificationId = [];
        foreach($result as $key=>$row){
            $classification_id      = explode(',', $row['classification_id']);
            $listClassificationId   = array_merge($listClassificationId, $classification_id);

            $domain                 = Yii::$app->params['urlDomain'];
            $images                 = "";
            if( $row['images'] != "" ){
                $images         =  explode(';', $row['images']);
                $images         = preg_filter('/^/', $domain, $images);
            }
            $result[$key]['classification_id'] = $classification_id;
            $result[$key]['images'] = $images;
            unset($result[$key]['product_id']);
        }
        
        $resultClassification = ProductClassificationGroup::find()->where(['in','id', array_values($listClassificationId)])->asArray()->all();
        if( !empty($resultClassification) ){
            $listClassificationParentId = ArrayHelper::map($resultClassification, 'parent_id', 'parent_id');

            $listClassificationParent = ArrayHelper::map(ProductClassificationGroup::find()->where(['in','id', $listClassificationParentId])->asArray()->all(), 'id', 'name');
            $dataGroup = [];
            foreach($listClassificationParent as $parent_id=>$parent_name){
                $dataGroup[$parent_id] = [
                    'id'    => $parent_id,
                    'name'  => $parent_name,
                    'childs'=> []
                ];
                foreach($resultClassification as $row){
                    if( $row['parent_id'] == $parent_id ){
                        $dataGroup[$parent_id]['childs'][] = [
                            'id'        => $row['id'],
                            'name'      => $row['name'],
                        ];
                    }
                }
            }
            $data['group'] = array_values($dataGroup);
        }

        $data['data'] = array_values($result);
        
        return $data;
    }
}
