<?php

namespace backend\models;

use Yii;
use common\helpers\Format;
use common\helpers\Response;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class OrderRefund extends \yii\db\ActiveRecord
{
    const STATUS_PENDING = 0;
    const STATUS_APPROVE = 1;
    const STATUS_REJECT = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_refund';
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

    public static function checkData($order_id, $user_id){
        $dataOrder = Order::getOrderDetail($order_id, $user_id);
        if( !$dataOrder || $dataOrder['status_id'] != Order::STATUS_PURCHASED ){
            $message = !$dataOrder ? Response::getErrorMessage('order', Response::KEY_NOT_FOUND) : Response::getErrorMessage('order', Response::KEY_INVALID);
            return [
                'status' => false,
                'message'=> $message
            ];
        }

        $model = self::findOne(['order_id' => $order_id, 'user_id' => $user_id]);
        if( $model ){
            $key = "";
            if( $model->status == self::STATUS_PENDING ){
                $key = "order_refund_pending";
            }else if( $model->status == self::STATUS_APPROVE ){
                $key = "order_refund_approve";
            }else if( $model->status == self::STATUS_REJECT ){
                $key = "order_refund_reject";
            }
            $message    = Response::getErrorMessage($key, Response::KEY_EXISTS);
            return [
                'status' => false,
                'message'=> $message
            ];
        }

        return [
            'status' => true,
            'data'   => $dataOrder
        ];
    }

    public static function getInfoOrder($order_id, $user_id){
        $dataCheckOrder = self::checkData($order_id, $user_id);
        if( !$dataCheckOrder['status'] ){
            return $dataCheckOrder;
        }
        $dataOrder = $dataCheckOrder['data'];

        $list_reason = Config::getConfigApp("LIST_REASON_REFUN");
        if( is_array($list_reason) && !empty($list_reason) ){
            $list_reason = array_values($list_reason);
        }

        $price_refund = $dataOrder['pay_info']['total_price'] - $dataOrder['pay_info']['fee_ship'];

        $data = [
            'plan_name' => 'Trả hàng/Hoàn tiền',
            'product_info' => $dataOrder['product_info'],
            'list_reason'  => $list_reason,
            'refund_price' => $price_refund,
            'refund_in'    => 'Số tài khoản ngân hàng'
        ];
        

        return [
            'status' => true,
            'data'   => $data
        ];

    }
    
    public static function createRefundOrder($user_id, $params){
        $order_id   = $params['order_id'];
        $reason     = $params['reason_refund'];
        $note       = $params['note_refund'];
        $type_situation = $params['type_situation'];

        $dataCheckOrder = self::checkData($order_id, $user_id);
        if( !$dataCheckOrder['status'] ){
            return $dataCheckOrder;
        }
        $dataOrder = $dataCheckOrder['data'];

        $price_refund       = $dataOrder['pay_info']['total_price'] - $dataOrder['pay_info']['fee_ship'];

        $model              = new OrderRefund;
        $model->order_id    = $order_id;
        $model->user_id     = $user_id;
        $model->price_refund    = $price_refund;
        $model->type_situation    = $type_situation;
        $model->reason      = $reason;
        $model->note        = $note;

        $model->save(false);

        return [
            'status' => true,
            'message'=> 'Gửi yêu cầu thành công'
        ];
    }
}
