<?php

namespace backend\models;

use Yii;
use common\helpers\Format;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use common\helpers\Response;

class Order extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order';
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
    

    public static function createNewOrder($params, $modelUser){
        $dataReturn             = ['status' => false, 'msg' => ''];
        $transaction            = Yii::$app->db->beginTransaction();
        
        try{
            $user_id                = $modelUser->id;
            $voucher_id             = $params['voucher_id'];
            $delivery_address_id    = $params['delivery_address_id'];
            $type_payment           = $params['type_payment'];
            $use_wallet_payment     = $params['use_wallet_payment'];
            $product_combination    = $params['product_combination'];

            $delivery_address       = UserDeliveryAddress::getDeliveryAddressOrderDefault($user_id, $delivery_address_id);
            if( !$delivery_address ){
                $dataReturn['msg']  = Response::getErrorMessage('delivery_address', Response::KEY_NOT_FOUND);
                return $dataReturn;
            }

            $dataVoucher            = $voucher_id > 0 ? Voucher::calculatePriceVoucherUse($user_id, $product_combination, $voucher_id, true) : ["price" => "0", "price_org" => 0, "price_type" => 1];
            
            $listProduct            = Product::getProductByCombination($product_combination);

            if( empty($listProduct) ){
                $dataReturn['msg']  = Response::getErrorMessage('order', Response::KEY_INVALID);
                return $dataReturn;
            }

            //Tính toán tổng số tiền cần thanh toán theo từng sản phẩm
            $price_voucher              = 0;
            $list_product_valid         = [];
            $wallet_point               = $modelUser->wallet_point;
            $isUpdateWalletPoint        = false;
            $isUseVoucherValid          = false;

            if( $dataVoucher['price_org'] > 0  ){
                if( $dataVoucher['price_type'] == 1 ){//Voucher giảm tiền
                    $price_voucher      = $dataVoucher['price_org'];//Số tiền được giảm của voucher
                    $list_product_valid = $dataVoucher['product_id'];//List sản phẩm hợp lệ đc dùng voucher này
                }else{
                    $isUseVoucherValid  = true;
                }
            }

            
            foreach($listProduct as $key=>$product){
                $product_id             = $product['product_id'];
                $total_price_product    = $product['total_price'];//Tổng tiền gốc của sản phẩm (Giá * Số lượng)
                $fee_ship               = $product['fee_ship'];
                $price_voucher_apply    = 0;
                $isSaveHistoryWallet    = false;
                $subtracted_balance     = 0;//Số dư ví cần trừ

                if( $price_voucher > 0 && in_array($product_id, $list_product_valid) ){
                    if( $price_voucher <= $total_price_product ){
                        $price_voucher_apply  = $price_voucher;
                        $total_price_product -= $price_voucher_apply;
                        $price_voucher = 0;
                    }else{
                        $price_voucher_apply = $total_price_product;
                        $price_voucher -= $price_voucher_apply ;
                        $total_price_product = 0;
                    }
                    
                    $isUseVoucherValid  = true;
                }
                
                if( $total_price_product > 0 && $use_wallet_payment && $wallet_point > 0 ){//Sử dụng ví để thanh toán
                    if( $wallet_point <= $total_price_product){
                        $subtracted_balance = $wallet_point;
                    }else{
                        $subtracted_balance = $total_price_product;
                    }

                    $total_price_product -= $subtracted_balance;
                    
                    $wallet_point       -= $subtracted_balance;

                    $isUpdateWalletPoint    = true;
                    
                    $isSaveHistoryWallet    = true;
                }

                $total_price_order          = $total_price_product + $fee_ship;

                $modelOrder                         = new Order;
                $modelOrder->user_id                = $user_id;
                $modelOrder->price                  = $product['total_price'];
                $modelOrder->price_voucher          = $price_voucher_apply;
                $modelOrder->price_wallet           = $subtracted_balance;
                $modelOrder->fee_ship               = $fee_ship;
                $modelOrder->total_price            = $total_price_order;
                $modelOrder->voucher_id             = $price_voucher_apply > 0 ? $voucher_id : 0;
                $modelOrder->type_payment           = $type_payment;
                $modelOrder->use_wallet_payment     = $use_wallet_payment;
                $modelOrder->delivery_address_id    = $delivery_address_id;
                $modelOrder->agent_id               = $product['agent_id'];
                if( $key == 0 && $isUseVoucherValid && $dataVoucher['price_type'] == 2 ){
                    $modelOrder->voucher_id         = $voucher_id;
                    $modelOrder->voucher_point_refundable = $dataVoucher['price_org'];
                }

                if( $modelOrder->save(false) ){
                    //Lưu sản phẩm + số lượng vào bản product_order
                    $modelOrderProduct                          = new OrderProduct;
                    $modelOrderProduct->order_id                = $modelOrder->id;
                    $modelOrderProduct->product_id              = $product['product_id'];
                    $modelOrderProduct->price                   = $product['total_price'];
                    $modelOrderProduct->quantity                = $product['quantity'];
                    $modelOrderProduct->total_price             = $total_price_order;
                    $modelOrderProduct->product_classification_id = $product['id'];
                    $modelOrderProduct->save(false);

                    //Lưu lịch sử sử dụng ví
                    if( $isSaveHistoryWallet )
                        HistoryWalletPoint::insertHistory($user_id, 2, 'Dùng ví mua sản phẩm', $modelOrder->id, $subtracted_balance);
                    //Tạo notify cho user + đại lý

                }

            }
            
            //Lưu số điểm ví còn lại
            if( $isUpdateWalletPoint ){
                $modelUser->wallet_point = $wallet_point ? $wallet_point : 0;
                $modelUser->save(false);
            }
            
            //Cộng lượt sử dụng voucher
            if( $isUseVoucherValid ){
                Voucher::updateAll([
                    'total_use' => new Expression("total_use + 1"),
                ], ['id' => $voucher_id]);

                UserUseVoucher::insertHistory($user_id, $voucher_id);
            }
            
            $transaction->commit();

            $dataReturn['status'] = true;
        }catch(\Exception $e){
            $transaction->rollBack();
            $data = $params;
            $data['message'] = $e->getMessage();
            $Util = new Util;
            $Util->writeLog('order-error', $data);
            $dataReturn['msg'] = Response::getErrorMessage('sys', Response::KEY_SYS_ERR);
        }

        return $dataReturn;
    }
}
