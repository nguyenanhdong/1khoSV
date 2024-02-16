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
