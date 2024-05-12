<?php

namespace backend\models;

use Yii;
use common\helpers\Format;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use common\helpers\Response;

class Order extends \yii\db\ActiveRecord
{
    const STATUS_PENDING = 0;
    const STATUS_CONFIRM = 1;
    const STATUS_DELIVERING = 2;
    const STATUS_PURCHASED = 3;
    const STATUS_REFUND = 4;
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
                    $modelOrderProduct->price_origin            = $product['price_origin'];
                    $modelOrderProduct->price                   = $product['total_price']/$product['quantity'];
                    $modelOrderProduct->quantity                = $product['quantity'];
                    $modelOrderProduct->total_price             = $total_price_order;
                    $modelOrderProduct->product_classification_id = $product['id'];
                    $modelOrderProduct->save(false);

                    //Lưu lịch sử sử dụng ví
                    if( $isSaveHistoryWallet )
                        HistoryWalletPoint::insertHistory($user_id, 2, 'Dùng ví mua sản phẩm', $modelOrder->id, $subtracted_balance);

                    //Tạo notify cho user + đại lý
                    $product_name   = $product['name'];
                    $title_user     = "Đặt hàng thành công";
                    $desc_user      = "Bạn đã mua sản phẩm $product_name. Shop sẽ xác nhận đơn hàng của bạn";
                    $content_user   = $desc_user;
                    Notify::insertNotify($user_id, $title_user, $desc_user, $content_user, 0, 0, $modelOrder->id, Notify::TYPE_CUSTOMER);

                    $full_name_user = trim($modelUser->fullname) ? $modelUser->fullname : $modelUser->phone;
                    $title_agent    = "Bạn có đơn hàng mới";
                    $desc_agent     = "$full_name_user đã đặt 1 đơn hàng";
                    $content_agent  = $desc_agent;
                    Notify::insertNotify($modelOrder->agent_id, $title_agent, $desc_agent, $content_agent, 0, 0, $modelOrder->id, Notify::TYPE_AGENT);
                    //Cộng số lượng sản phẩm đã bán, Trừ số lượng trong kho hàng => Xử lý sau khi đại lý chấp nhận đơn
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

    public static function getOrderOfUserByType($type , $user_id, $limit = null, $offset = null){
        $condition = ['A.user_id' => $user_id];
        switch($type){
            case 'pending'://Chờ xác nhận
                $condition['A.status'] = self::STATUS_PENDING;
                break;
            case 'confirm'://Đã xác nhận
                $condition['A.status'] = self::STATUS_CONFIRM;
                break;
            case 'are_delivering'://Đang giao
                $condition['A.status'] = self::STATUS_DELIVERING;
                break;
            case 'purchased'://Đã mua
                $condition['A.status'] = self::STATUS_PURCHASED;
                break;
            case 'refund'://Trả hàng/hoàn tiền
                $condition['A.status'] = self::STATUS_REFUND;
                break;
            default:
                break;
        }
        $query = self::find()
        ->select('A.id, A.status, B.product_id, C.name as product_name, C.image as product_image, C.star as product_star, C.price as product_price, B.price as price_buy, A.total_price, B.quantity, B.product_classification_id, D.fullname as agent_name')
        ->from(self::tableName() . ' A')
        ->innerJoin(OrderProduct::tableName() . ' B', 'A.id = B.order_id')
        ->innerJoin(Product::tableName() . ' C', 'C.id = B.product_id')
        ->leftJoin(Agent::tableName() . ' D', 'D.id = C.agent_id')
        ->where($condition);

        if( !is_null($limit) )
            $query->limit($limit);
        if( !is_null($offset) )
            $query->offset($offset);

        $query->orderBy(['A.id' => SORT_DESC]);

        $result = $query->asArray()->all();
        if( !empty($result) ){
            return self::getItemApp($result);
        }
        return [];
    }

    public static function getItemApp($listItem){
        $data   = [];
        $domain = Yii::$app->params['urlDomain'];
        foreach($listItem as $item){
            $quantity   = (int)$item['quantity'];
            $agent_name = $item['agent_name'] ? $item['agent_name'] : '1KHO';
            $price      = $item['price_buy'];//Giá tại thời điểm mua
            $price_old  = $item['product_price'];//Giá gốc sản phẩm
            $percent_discount = $price < $price_old ? round((($price_old - $price)/$price_old)*100) : 0;
            $imageShow = "";
            if( $item['product_image'] != "" ){
                $image =  explode(';', $item['product_image']);
                $imageShow = $domain . $image[0];
            }

            $row = [
                'order_id' => (int)$item['id'],
                'product_id'  => (int)$item['product_id'],
                'agent_name'  => $agent_name,
                'product_name' => $item['product_name'],
                'product_image'=> $imageShow,
                'status' => (int)$item['status'],
                'can_cancel' => $item['status'] == 0 ? true : false,
                'price'=> $price,
                'price_old' => $price_old,
                'percent_discount' => $percent_discount,
                'star' => $item['product_star'],
                'quantity' => $quantity,
                'product_classification_id' => (int)$item['product_classification_id']
            ];
            $data[] = $row;
        }
        
        return $data;
    }

    public static function getOrderDetail($id, $user_id){
        $model = self::findOne(['id' => $id, 'user_id' => $user_id]);
        if( !$model )
            return null;

        $product = OrderProduct::getProductByOrderId($model->id);

        $can_cancel = false;
        $can_refund = false;
        $can_review = ProductReview::checkUserReviewOrder($user_id, $model->id);
        $status_name= "";
        $date_refund_expire = null;
        switch($model->status){
            case self::STATUS_PENDING:
                $can_cancel = true;
                $status_name= "Đơn hàng chờ xác nhận";
                break;
            case self::STATUS_CONFIRM:
                $status_name= "Đơn hàng đã xác nhận";
            case self::STATUS_DELIVERING:
                $status_name= "Đơn hàng đang giao";
                break;
            case self::STATUS_PURCHASED:
                $status_name= "Đơn hàng đã mua";
                $time_refund_expire = strtotime('+ 10 day', strtotime($model->time_payment));
                $date_refund_expire = date('d-m-Y', $time_refund_expire);
                if( time() < $time_refund_expire || date('d-m-Y') == $date_refund_expire )
                    $can_refund = true;

                $can_review = true;
                break;
            case self::STATUS_REFUND:
                $status_name= "Đơn hàng đã trả hàng/hoàn tiền";
                break;
        }
        
        $domain     = Yii::$app->params['urlDomain'];
        $price      = $product['price'];
        $price_old  = $product['price_origin'];
        $percent_discount = $price < $price_old ? round((($price_old - $price)/$price)*100) : 0;
        $imageShow = "";
        if( $product['image'] != "" ){
            $image =  explode(';', $product['image']);
            $imageShow = $domain . $image[0];
        }

        $productInfo = [
            'id' => $product['id'],
            'name' => $product['name'],
            'image' => $imageShow,
            'price' => $price,
            'percent_discount' => $percent_discount
        ];

        $shippingInformation = UserDeliveryAddress::getDeliveryAddressOrderDefault($model->user_id, $model->delivery_address_id);

        $typePayment = $model->type_payment;
        $typePaymentName = $typePayment == 1 ? 'Chuyển khoản' : 'Thanh toán khi nhận hàng';
        $bankPaymentInfo = $typePayment == 1 ? Config::getConfigApp("BANK_PAYMENT") : null;

        $payInfo = [
            'price' => $model->price,
            'price_voucher' => $model->price_voucher,
            'price_wallet' => $model->price_wallet,
            'fee_ship' => $model->fee_ship,
            'total_price' => $model->total_price
        ];

        $data = [
            'id' => $model->id,
            'status_id' => $model->status,
            'status_name' => $status_name,
            'can_cancel' => $can_cancel,
            'can_refund' => $can_refund,
            'can_review' => $can_review,
            'date_refund_expire' => $date_refund_expire,
            'product_info' => $productInfo,
            'shipping_info' => $shippingInformation,
            'type_payment' => $typePayment,
            'type_payment_name' => $typePaymentName,
            'bank_payment_info' => $bankPaymentInfo,
            'pay_info'     => $payInfo
        ];


        return $data;
    }
}
