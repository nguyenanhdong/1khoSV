<?php
namespace frontend\controllers;

use backend\models\Config;
use backend\models\Product;
use backend\models\UserDeliveryAddress;
use backend\models\Voucher;
use common\models\District;
use common\models\Province;
use Yii;
use yii\web\Controller;

/**
 * Cart controller
 */
class CartController extends Controller
{
    public function beforeAction($action)
    {
        if (Yii::$app->user->isGuest) {
            $this->redirect(['/site/login']);
            return false; 
        }
        return parent::beforeAction($action);
    }
    //Giỏ hàng
    public function actionIndex(){
        $this->view->title = 'Giỏ hàng';
        $session = Yii::$app->session;
        $listProduct = $session->get('list_product');
        $data = [];
        if(!empty($listProduct)){
            foreach ($listProduct as $productId => $row) {
                $product = Product::getProductDetail($productId);
                if(!empty($product)){
                    $data[] = [
                        'product_id' => $product['product_info']['id'],
                        'name' => $product['product_info']['name'],
                        'images' => $product['product_info']['images'][0] ?? '',
                        'percent_discount' => $product['product_info']['percent_discount'],
                        'price' => $this->getPriceProduct($row['classification_id'],$product),
                        'price_format' => HelperController::formatPrice($this->getPriceProduct($row['classification_id'],$product)),
                        'qty' => $row['qty'],
                        'classification_id' => $row['classification_id']
                    ];
                }
            }
        }
        $userId = Yii::$app->user->identity->id;
        $deliveryAddress = UserDeliveryAddress::findOne(['user_id' => $userId, 'is_save_primary_address' => 1]);
        $province = Province::getProvince();
        $district = [];
        if(!empty($deliveryAddress->province))
            $district = District::getDistrict($deliveryAddress->province);
        if ($deliveryAddress->load(Yii::$app->request->post())) {
            if($deliveryAddress->validate()){
                $deliveryAddress->save(false);
                return $this->asJson(['success' => true]);
            }else{
                return $this->asJson(['success' => false, 'errors' => $deliveryAddress->errors]);
            }
            if($deliveryAddress->save()){
                Yii::$app->session->setFlash('success', 'Cập nhật thành công');
                return $this->redirect(['index']);
            }
        }
        return $this->render('index',[
            'data' => $data,
            'deliveryAddress' => $deliveryAddress,
            'province' => $province,
            'district' => $district,
        ]);
    }

    //lấy giá sản phẩm
    public static function getPriceProduct($classification_id,$product){
        $price = 0;
        if(!empty($product)){
            $price = $product['product_info']['price'];
            $classificationData = $product['product_info']['classification_data'];
            if(!empty($classificationData)){
                foreach ($classificationData as $row) {
                    if($row['id'] == $classification_id){
                        $price = $row['price'];
                    }
                }
            }
        }
        return $price;
    }

    //add giỏ hàng
    public function actionAddCart(){
        $session = Yii::$app->session;
        $productId = Yii::$app->request->post('productId', '');
        $classificationId = Yii::$app->request->post('classificationId', '');
        $productQty = Yii::$app->request->post('productQty', 1);
        $resStatus = 0;
        if(!empty($productId)){
            $_SESSION['list_product'][$productId]['classification_id'] = $classificationId;
            $_SESSION['list_product'][$productId]['qty'] = $productQty;
            $resStatus = 1;
        }
        return $resStatus;
        // $product_cart = $session->get('list_product');
        // $session->destroy(); 
    }

    //lấy thông tin order khi chọn sản phẩm thanh toán
    public function actionGetInfoOrder(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $session = Yii::$app->session;
        $listProductId = Yii::$app->request->post('arrProductId', '');
        $voucherId = Yii::$app->request->post('voucherId', 0);
        $productCombination = [];
        if(!empty($listProductId)){
            $listProductCart = $session->get('list_product');
            if(!empty($listProductCart)){
                foreach($listProductCart as $product_id => $row){
                    if(in_array($product_id, $listProductId)){
                        $productCombination[] = [
                            'id' => $row['classification_id'],
                            'quantity' => $row['qty']
                        ];
                    }
                }
            }
        }
        $dataInfoOrder = $this->GetOrder($voucherId, $productCombination);
        return $dataInfoOrder;
    }
    public static function GetOrder($voucherId, $productCombination){
        $user = Yii::$app->user->identity;
        $dataVoucher            = $voucherId > 0 ? Voucher::calculatePriceVoucherUse($user->id, $productCombination, $voucherId) : ["price" => "0", "price_org" => 0, "price_type" => 1];
        
        $price_order            = Product::getPriceOfOrder($productCombination);
        $fee_ship               = Product::getFeeShipProduct($productCombination);
        
        $delivery_address       = UserDeliveryAddress::getDeliveryAddressOrderDefault($user->id);

        $price_deduct           = $dataVoucher['price_org'] && $dataVoucher['price_type'] == 1 ? $dataVoucher['price_org'] : 0;

        $price_voucher          = $dataVoucher['price'] == "0" ? "" : $dataVoucher['price'];

        $total_price_order      = ($price_order + $fee_ship) - $price_deduct;

        $bank_payment           = Config::getConfigApp("BANK_PAYMENT");

        $dataRes                = [
            'price_voucher'     => $price_voucher,
            'fee_ship'          => $fee_ship,
            'wallet_point'      => $user->wallet_point,
            'price_order'       => $price_order,
            'total_price_order' => $total_price_order,
            'delivery_address'  => $delivery_address,
            'bank_payment'      => $bank_payment
        ];
        return $dataRes;
    }
    public function actionUpdateInfoProduct(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $productId = Yii::$app->request->post('productId', '');
        $qty = Yii::$app->request->post('qty', 1);

        $session = Yii::$app->session;
        $listProductCart = $session->get('list_product');

        $dataInfoProduct = [];
        if(!empty($productId) && !empty($listProductCart)){
            $productCombination = [];
            foreach($listProductCart as $product_id => $row){
                $qtyNew = $row['qty'];
                if($product_id == $productId){
                    $qtyNew = $qty;
                    $productCombination[] = [
                            'id' => $row['classification_id'],
                            'quantity' => $qtyNew
                        ];
                }
                //update lai so luong san pham trong session
                $_SESSION['list_product'][$product_id]['classification_id'] = $row['classification_id'];
                $_SESSION['list_product'][$product_id]['qty'] = $qtyNew;
            }
            $dataInfoProduct = $this->GetOrder(0, $productCombination);
        }
        return $dataInfoProduct;
    }

}
