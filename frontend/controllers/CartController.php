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
        // echo '<pre>';
        // print_r($listProduct);
        // echo '</pre>';die;
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
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';die;
        return $this->render('index',[
            'data' => $data
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
        $classificationId = Yii::$app->request->post('classificationId', 0);
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
}
