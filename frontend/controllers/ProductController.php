<?php
namespace frontend\controllers;

use backend\controllers\ApiNewController;
use backend\models\Agent;
use backend\models\Category;
use backend\models\Product;
use backend\models\ProductReview;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;

/**
 * Product controller
 */
class ProductController extends Controller
{
    //Chi tiết sản phẩm
    public function actionDetail($id){
        $product = Product::getProductDetail($id);
        // echo '<pre>';
        // print_r($product);
        // echo '</pre>';die;
        $this->view->title = 'Chi tiết sản phẩm';
        return $this->render('detail-product',[
            'product' => $product
        ]);
    }

    public function actionViewMoreReview(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $product_id     = Yii::$app->request->post('product_id', '');
        $limit          = 3;
        $page           = Yii::$app->request->post('page', 1);
        $offset         = ($page - 1) * $limit;
        $offsetCheck = $limit + $offset + 1;
        $response['data'] = '';
        $response['checkLoadMore'] = false;
        if(!empty($product_id)){
            $dataRes        = ProductReview::getReviewByProductId($product_id, $limit, $offset);
            $response['checkLoadMore'] = !empty(ProductReview::getReviewByProductId($product_id, 1, $offsetCheck)) ? true : false;
            if (!empty($dataRes)) {
                $item = '';
                foreach($dataRes as $row) {
                    $elementNav = '';
                    $elementFor = '';
                    if(!empty($row['video_image'])){
                        foreach($row['video_image'] as $link){ 
                            $elementNav .= '<div class="item_slide slide_nav"><img class="img_slide_nav" src="'. $link .'"></div>';
                            $elementFor .= '<div class="item_slide item_for"><img class="" src="'. $link .'"></div>';
                            if(strpos($link, 'mp4') !== false){
                                $elementNav .= '<div class="item_slide slide_nav position-relative">
                                                <video class="img_slide_nav" width="640" height="360">
                                                    <source src="'. $link .'" type="video/mp4">
                                                </video>
                                                <div class="icon_play flex-center"><img src="/images/icon/play.svg"></div>
                                            </div>';
                                $elementFor .= '<div class="item_slide item_for">
                                                    <video class="" width="640" height="360" controls>
                                                        <source src="'. $link .'" type="video/mp4">
                                                    </video>
                                                </div>';
                            }

                        }
                    }
                    $avatar = !empty($row['avatar']) ? $row['avatar'] : '/images/icon/user-icon.svg';
                    $item .= '<div class="comment_item">
                                <img class="comment_avatar" src="'. $avatar .'" alt="">
                                <div class="comment_group_right">
                                    <div class="user_name flex-item-center">
                                        <p>'. $row['fullname'] .'</p>
                                        <span>•</span>
                                        <span>'. $row['date_review'] .'</span>
                                    </div>
                                    <p>'. $row['content'] .'</p>
                                    <div class="video_image_comment slider-comment-nav">
                                        '. $elementNav .'
                                    </div>
                                    <div class="video_image_comment slider-comment-for hide">
                                        '. $elementFor .'
                                    </div>
                                </div>
                            </div>';
                }
                $response['data'] = $item;
            }
        }
        return $response;
    }

    //Thông tin shop
    public function actionShop($id){
        $agentData = ApiNewController::AgentHome();
        $this->view->title = 'Thông tin shop';
        return $this->render('shop',[
            'data' => isset($agentData['data']) ? $agentData['data'] : []
        ]);
    }

    public function actionGetProductShop()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $id                 = Yii::$app->request->post('shop_id', '');
        $page               = !empty(Yii::$app->request->post('page')) ? Yii::$app->request->post('page') : 0;
        $sort               = Yii::$app->request->post('sort', 'popular');
        $response['data'] = '';
        $response['checkLoadMore'] = false;
        $response['append'] = false;

        if (!empty($page)) {
            $response['append'] = true;
        }
        
        $limit = 10;
        $offset = $page * $limit;

        $listCateIdProd     = [];
        $listProductTab     = Product::getProductByAgent($listCateIdProd, $id, $sort, $limit, $offset);
        $offsetCheck = $limit + $offset + 1;
        $response['checkLoadMore'] = !empty(Product::getProductByAgent($listCateIdProd, $id, $sort, 1, $offsetCheck)) ? true : false;
        $item = '';
        if (!empty($listProductTab)) {
            foreach ($listProductTab as $row) {
                $url = Url::to(['/product/detail', 'id' => $row['id']]);
                $item .= '<div class="product_item">
                                <a href="' . $url . '">
                                    <span class="prod_sale">' . $row['percent_discount'] . '% <br> OFF</span>
                                    <img class="prod_avatar" src="' . $row['image'] . '" alt="Image product">
                                    <div class="prod_price_star">
                                        <p class="prod_title line_2" title="' . $row['name'] . '">' . $row['name'] . '</p>
                                        <div class="des_prod mt-2">
                                            <span>' . HelperController::formatPrice($row['price']) . '</span>
                                            <div class="flex-center">
                                                <img src="/images/icon/star.svg" alt="">
                                                <p class="product_star">' . $row['star'] . ' (' . $row['total_rate'] . ')</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>';
            }
        }
        $response['data'] = $item;
        return $response;
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
