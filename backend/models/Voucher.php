<?php

namespace backend\models;

use Yii;
use common\helpers\Format;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
class Voucher extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'voucher';
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
    
    public static function getVoucherDetail($id, $user_id){
        $model          = self::findOne($id);
        if( $model ){
            $data       = $model->getAttributes();
            
        }

        return null;
    }

    public static function getListVoucherAppCustomer($voucher_type = 1, $user_id, $limit = null, $offset = null){
        $condition = [];
        $column_select = ['A.*', 'C.avatar as agent_avatar'];
        if( $voucher_type == 1 ){//Chưa dùng
            $column_select[] = new Expression("1 as can_use_voucher");
            $condition = ['and', ['>=', 'A.date_end', date('Y-m-d')], ['>', 'A.total_maximum_use', 'A.total_use']];
        }else if( $voucher_type == 2 ){//Đã dùng hoặc hết hạn
            $column_select[] = new Expression("0 as can_use_voucher");
            $condition = ['or', ['B.user_id' => $user_id], ['<', 'A.date_end', date('Y-m-d')]];
        }
        
        $query = self::find()->select($column_select)->from(self::tableName() . ' A')
        ->leftJoin('`order` B', 'A.id = B.voucher_id')
        ->leftJoin('agent C', 'A.agent_id = C.id');

        $query->where($condition);
        if( $voucher_type == 1 ){
            $query->leftJoin(new Expression("(SELECT count(id) as total_voucher_has_use, voucher_id FROM user_use_voucher WHERE user_id = $user_id GROUP BY voucher_id)") . ' D', 'D.voucher_id = A.id');
            $query->andWhere(['or', ['IS', new Expression("D.total_voucher_has_use"), NULL], ['>', 'A.maximum_use_by_user', new Expression("D.total_voucher_has_use")]]);
        }

        if( !is_null($limit) )
            $query->limit($limit);
        if( !is_null($offset) )
            $query->offset($offset);

        $result = $query->asArray()->all();
        if( !empty($result) )
            return self::getItemApp($result);

        return [];
    }

    public static function getItemApp($listItem){
        $data   = [];
        $domain = Yii::$app->params['urlDomain'];
        foreach($listItem as $item){
            $voucher_name   = "";
            $desc           = "";
            $priceStr       = $item['type_price'] == 1 ? Format::formatPrice($item['price']) : $item['price'] . '%';
            $minimum_order  = Format::formatPrice($item['minimum_order']);
            $max_price_by_percent  = $item['max_price_by_percent'] > 0 ? Format::formatPrice($item['max_price_by_percent']) : '';
            $product_id     = !empty($item['product_id']) ? array_values(array_filter(explode(';', $item['product_id']))) : [];
            $labelName      = "";
            $priceType      = "";
            if( $item['type_voucher'] == 1 ){//Giảm tiền
                $labelName  = "Giảm ";
            }
            else if( $item['type_voucher'] == 2 ){//Hoàn xu
                $labelName  = "Hoàn ";
                $priceType  = " xu";
            }
            $voucher_name   = $labelName . $priceStr . $priceType;
            $desc           = $labelName . $priceStr . $priceType . ( $max_price_by_percent ? " tối đa $max_price_by_percent" : "" ) . " cho đơn hàng có giá trị trên " . $minimum_order;
            $imageShow = "";
            if( $item['agent_id'] ){
                $imageShow = $domain . $item['agent_avatar'];
            }else{
                $imageShow = $domain . "/img/logo-1khosv.png";
            }

            $row = [
                'id' => $item['id'],
                'name' => $voucher_name,
                'desc' => $desc,
                'image'=> $imageShow,
                'can_use_voucher' => (int)$item['can_use_voucher'],
                'agent_id' => (int)$item['agent_id'],
                'product_id' => $product_id
            ];
            
            $data[] = $row;
        }
        
        return $data;
    }
}
