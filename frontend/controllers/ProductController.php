<?php
namespace frontend\controllers;

use backend\controllers\ApiNewController;
use backend\models\Product;
use yii\web\Controller;

/**
 * Product controller
 */
class ProductController extends Controller
{
    //Chi tiết sản phẩm
    public function actionDetail($id){
        $product = Product::getProductDetail($id);
        $this->view->title = 'Chi tiết sản phẩm';
        return $this->render('detail-product',[
            'product' => $product
        ]);
    }

    //Thông tin shop
    public function actionShop(){
        $this->view->title = 'Thông tin shop';
        return $this->render('shop');
    }

    //Giao vặt
    public function actionDelivery(){
        $this->view->title = 'Giao vặt';
        return $this->render('delivery');
    }

    //Chi tiết giao vặt
    public function actionDetailDelivery(){
        $this->view->title = 'Chi tiết giao vặt';
        return $this->render('detail-delivery');
    }

    //Đăng tin giao vặt
    public function actionPostDelivery(){
        $this->view->title = 'Đăng tin giao vặt';
        return $this->render('post-delivery');
    }
    //Đăng tin giao vặt thành công
    public function actionPostSuccessDelivery(){
        $this->view->title = 'Đăng tin giao vặt';
        return $this->render('post-success-delivery');
    }
}
