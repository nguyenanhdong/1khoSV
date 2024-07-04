<?php
namespace frontend\controllers;

use backend\models\Product;
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
                        'id' => $product['product_info']['id'],
                        'name' => $product['product_info']['name'],
                        'images' => $product['product_info']['images'][0] ?? '',
                        'percent_discount' => $product['product_info']['percent_discount'],
                        'price' => $this->getPriceProduct($row['option'],$product),
                        'price_format' => HelperController::formatPrice($this->getPriceProduct($row['option'],$product)),
                        'qty' => $row['qty'],
                    ];
                }
            }
        }
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';die;
        return $this->render('index',[
            'data' => $data
        ]);
    }

    //lấy giá sản phẩm
    public static function getPriceProduct($arrOptionId,$product){
        $price = 0;
        if(!empty($product)){
            $price = $product['product_info']['price'];
            $classificationData = $product['product_info']['classification_data'];
            if(!empty($classificationData)){
                foreach ($classificationData as $row) {
                    if($row['classification_id'] == $arrOptionId){
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
        $arrOptionId = Yii::$app->request->post('arrOptionId', '');
        $productQty = Yii::$app->request->post('productQty', 1);
        $resStatus = 0;
        if(!empty($productId)){
            $_SESSION['list_product'][$productId]['option'] = $arrOptionId;
            $_SESSION['list_product'][$productId]['qty'] = $productQty;
            $resStatus = 1;
        }
        return $resStatus;
        // $product_cart = $session->get('list_product');
        // $session->destroy();
    }
}
