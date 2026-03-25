<?php
namespace frontend\controllers;

use backend\controllers\ApiNewController;
use backend\models\Advertisement;
use backend\models\Agent;
use backend\models\Category;
use backend\models\Product;
use backend\models\ProductReview;
use backend\models\UserFavouriteProduct;
use backend\models\UserViewProduct;
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
        if(!Yii::$app->user->isGuest){
            UserViewProduct::saveViewProduct(Yii::$app->user->identity->id, $id);
        }
        $this->view->title = 'Chi tiết sản phẩm';
        return $this->render('detail-product',[
            'product' => $product
        ]);
    }

    //lấy giá sản phẩm khi chọn phân loại
    public function actionGetPrice(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $productId = Yii::$app->request->post('productId', '');
        $arrOptionId = Yii::$app->request->post('arrOptionId', '');
        $data['classification_id'] = '';
        $data['price'] = '';
        if(!empty($productId)){
            $product = Product::getProductDetail($productId);
            $data['price'] = HelperController::formatPrice($product['product_info']['price']);
            $classificationData = $product['product_info']['classification_data'];
            if(!empty($classificationData)){
                foreach ($classificationData as $row) {
                    if($row['classification_id'] == $arrOptionId){
                        $data['price'] = HelperController::formatPrice($row['price']);
                        $data['classification_id'] = $row['id'];
                    }
                }
            }
        }
        return $data;
    }

    public function actionGetProductReview(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $limit          = 10;
        $page           = Yii::$app->request->post('page', 1);
        $type           = Yii::$app->request->post('type');
        $offset         = $page * $limit;
        $offsetCheck = $limit + $offset;
        
        $response['data'] = '';
        $response['checkLoadMore'] = false;
        $userId = Yii::$app->user->identity->id;

        if($type == 'not-review'){
            $dataNotReview = ProductReview::getListReviewOfUser($userId, 0, $limit, $offset);
            $response['checkLoadMore'] = !empty(ProductReview::getListReviewOfUser($userId, 0, 1, $offsetCheck)) ? true : false;
            if(!empty($dataNotReview)){
                $item = '';
                foreach($dataNotReview as $row){
                    $rating = '';
                    for ($i = 0; $i < 5; $i++) {
                        if ($i < $row['product_star']) { 
                            $rating .= '<img src="/images/icon/star-active.svg" alt="">';
                            } else { 
                                $rating .= '<img src="/images/icon/star-inactive.svg" alt="">';
                            } 
                    }
                    $item .= '<div class="group_item_shop px-0 d-flex flex-column">
                            <div class="item_shop">
                                <div class="item_shop_left d-flex flex-column">
                                    <div class="desc_item">
                                        <div class="flex-center avatar_pro">
                                            <img src=" '. $row['product_img'] .'" alt="">
                                        </div>
                                        <div class="text_desc d-flex flex-column">
                                            <p>'. $row['product_name'] .'</p>
                                            <div class="flex-item-center">
                                                <strong>'. HelperController::formatPrice($row['price']) .'</strong>
                                                <span>-'. $row['percent_discount'] .'%</span>
                                            </div>
                                            <div class="flex-item-center justify-content-between">
                                                <p>Số lượng '. $row['quantity'] .'</p>
                                                <div class="rating_product flex-item-center">
                                                    '. $rating .'
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="item_shop_right d-flex flex-column">
                                    <div class="btn_item">
                                        <div class="action_form">
                                            <button class="btn_action btn-blue flex-center" data-toggle="modal" data-target="#modalReview'. $row['order_id'] .'">Đánh giá</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="modalReview'. $row['order_id'] .'" tabindex="-1" role="dialog" aria-labelledby="modaReviewTitle" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content modal_content_review">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <h2>ĐÁNH GIÁ SẢN PHẨM</h2>
                                            <div class="product_review desc_item">
                                                <div class="flex-center avatar_pro">
                                                    <img src="'. $row['product_img'] .'" alt="">
                                                </div>
                                                <div class="product_review_detail">
                                                    <p>'. $row['product_name'] .'</p>
                                                    <div class="flex-item-center text_desc">
                                                        <strong>'. HelperController::formatPrice($row['price']) .'</strong>
                                                        <span>-'. $row['percent_discount'] .'%</span>
                                                    </div>
                                                    <p>Số lượng '. $row['quantity'] .'</p>
                                                </div>
                                            </div>
                                            <div class="form_review">
                                                <label>Chất lượng sản phẩm <span class="color_red">*</span></label>

                                                <div id="group-stars">
                                                    <div class="rating-group">
                                                        <input  disabled checked class="rating__input rating__input--none rating_'. $row['order_id'] .'" name="rating'. $row['order_id'] .'" id="rating'. $row['order_id'] .'-none" value="0" type="radio">
                                                        <label aria-label="1 star" class="rating__label" for="rating'. $row['order_id'] .'-1"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                        <input  class="rating__input rating_'. $row['order_id'] .'" name="rating'. $row['order_id'] .'" id="rating'. $row['order_id'] .'-1" value="1" type="radio">
                                                        <label aria-label="2 stars" class="rating__label" for="rating'. $row['order_id'] .'-2"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                        <input class="rating__input rating_'. $row['order_id'] .'" name="rating'. $row['order_id'] .'" id="rating'. $row['order_id'] .'-2" value="2" type="radio">
                                                        <label aria-label="3 stars" class="rating__label" for="rating'. $row['order_id'] .'-3"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                        <input  class="rating__input rating_'. $row['order_id'] .'" name="rating'. $row['order_id'] .'" id="rating'. $row['order_id'] .'-3" value="3" type="radio">
                                                        <label aria-label="4 stars" class="rating__label" for="rating'. $row['order_id'] .'-4"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                        <input  class="rating__input rating_'. $row['order_id'] .'" name="rating'. $row['order_id'] .'" id="rating'. $row['order_id'] .'-4" value="4" type="radio">
                                                        <label aria-label="5 stars" class="rating__label" for="rating'. $row['order_id'] .'-5"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                        <input  class="rating__input rating_'. $row['order_id'] .'" name="rating'. $row['order_id'] .'" id="rating'. $row['order_id'] .'-5" value="5" type="radio">
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="form-group">
                                                <div class="box-image flex-center flex-column">
                                                    <div class="form-group field-fileInput">
                                                        <input type="hidden" name="Advertisement[image][]" value=""><input class="fileInput" type="file" id="fileInput_'. $row['order_id'] .'" order-id="'. $row['order_id'] .'" name="Advertisement[image][]" multiple="" accept="image/*,video/*">
                                                        <div class="help-block"></div>
                                                    </div> <img src="/images/icon/icon-img.svg" alt="">
                                                    <label for="">Chọn 3 video ngắn dưới 1 phút + 8 hình ảnh</label>
                                                    <i class="error">Dung lượng ảnh tối đa 1MB, dung lượng video tối đa 20MB</i>
                                                </div>
                                                <div class="preview-box" id="previewBox_'. $row['order_id'] .'"></div>
                                            </div>
                                            <div class="form-group">
                                                <label for="">Đánh giá <span class="color_red">*</span></label>
                                                <textarea rows="5" name="" id="content_'. $row['order_id'] .'"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <button type="button" pro-id="'. $row['product_id'] .'" od-id="'. $row['order_id'] .'" class="btn_action btn_submit_review btn-orange flex-center">Gửi đánh giá</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>';
                }
                $response['data'] = $item;
            }
        }else if($type = 'reviewed'){
            $dataReviewd = ProductReview::getListReviewOfUser($userId, 1, $limit, $offset);
            $response['checkLoadMore'] = !empty(ProductReview::getListReviewOfUser($userId, 1, 1, $offsetCheck)) ? true : false;
            if(!empty($dataReviewd)){
                $item = '';
                foreach($dataReviewd as $row){
                    $avatar = !empty($row['avatar']) ? $row['avatar'] : '/images/icon/user-icon.svg';
                    $elementNav = '';
                    $elementFor = '';
                    if(!empty($row['video_image'])){
                        foreach($row['video_image'] as $link){ 
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
                            }else{
                                $elementNav .= '<div class="item_slide slide_nav"><img class="img_slide_nav" src="'. $link .'"></div>';
                                $elementFor .= '<div class="item_slide item_for"><img class="" src="'. $link .'"></div>';
                            }
                        }
                    }
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

    public function actionGetProductSeen(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $limit          = 10;
        $page           = Yii::$app->request->post('page', 1);
        $offset         = $page * $limit;
        $offsetCheck = $limit + $offset;
        
        $response['data'] = '';
        $userId = Yii::$app->user->identity->id;

        $data = UserViewProduct::getListProductView($userId, $limit, $offset);
        $response['checkLoadMore'] = !empty(UserViewProduct::getListProductView($userId, 1, $offsetCheck)) ? true : false;
        if(!empty($data)){
            $item = '';
            foreach($data as $row){
                $rating = '';
                for ($i = 0; $i < 5; $i++) {
                    if ($i < $row['star']) { 
                        $rating .= '<img src="/images/icon/star-active.svg" alt="">';
                        } else { 
                            $rating .= '<img src="/images/icon/star-inactive.svg" alt="">';
                        } 
                }
                $item .= '<div class="group_item_shop d-flex flex-column">
                            <div class="item_shop">
                                <div class="item_shop_left d-flex flex-column">
                                    <div class="desc_item">
                                        <div class="flex-center avatar_pro">
                                            <img src="'. $row['image'] .'" alt="">
                                        </div>
                                        <div class="text_desc d-flex flex-column">
                                            <p>'. $row['name'] .'</p>
                                            <div class="flex-item-center">
                                                <strong>'. HelperController::formatPrice($row['price']) .'</strong>
                                                <span>-'. $row['percent_discount'] .'%</span>
                                            </div>
                                            <div class="flex-item-center justify-content-between">
                                                <p>Số lượng '. $row['quantity_sold'] .'</p>
                                                <div class="rating_product flex-item-center">
                                                    '. $rating .'
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="item_shop_right d-flex flex-column">
                                    <div class="btn_item">
                                        <div class="action_viewd">
                                            <div class=" position-relative check_heart">
                                            </div>
                                            <a target="_blank" href="'. Url::to(['/product/detail', 'id' => $row['id']]) .'" class="btn_action btn-blue flex-center">Xem chi tiết</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }
            $response['data'] = $item;
        }
        return $response;
    }
    public function actionGetProductFavourite(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $limit          = 10;
        $page           = Yii::$app->request->post('page', 1);
        $offset         = $page * $limit;
        $offsetCheck = $limit + $offset;
        
        $response['data'] = '';
        $userId = Yii::$app->user->identity->id;

        $data = UserFavouriteProduct::getListProductFavourites($userId, $limit, $offset);
        $response['checkLoadMore'] = !empty(UserFavouriteProduct::getListProductFavourites($userId, 1, $offsetCheck)) ? true : false;
        if(!empty($data)){
            $item = '';
            foreach($data as $row){
                $rating = '';
                for ($i = 0; $i < 5; $i++) {
                    if ($i < $row['star']) { 
                        $rating .= '<img src="/images/icon/star-active.svg" alt="">';
                        } else { 
                            $rating .= '<img src="/images/icon/star-inactive.svg" alt="">';
                        } 
                }
                $item .= '<div class="group_item_shop d-flex flex-column">
                            <div class="item_shop">
                                <div class="item_shop_left d-flex flex-column">
                                    <div class="desc_item">
                                        <div class="flex-center avatar_pro">
                                            <img src="'. $row['image'] .'" alt="">
                                        </div>
                                        <div class="text_desc d-flex flex-column">
                                            <p>'. $row['name'] .'</p>
                                            <div class="flex-item-center">
                                                <strong>'. HelperController::formatPrice($row['price']) .'</strong>
                                                <span>-'. $row['percent_discount'] .'%</span>
                                            </div>
                                            <div class="flex-item-center justify-content-between">
                                                <p>Số lượng '. $row['quantity_sold'] .'</p>
                                                <div class="rating_product flex-item-center">
                                                    '. $rating .'
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="item_shop_right d-flex flex-column">
                                    <div class="btn_item">
                                        <div class="action_viewd">
                                            <div class=" position-relative check_heart">
                                            </div>
                                            <a target="_blank" href="'. Url::to(['/product/detail', 'id' => $row['id']]) .'" class="btn_action btn-blue flex-center">Xem chi tiết</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }
            $response['data'] = $item;
        }
        return $response;
    }

    public function actionViewMoreReview(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $product_id     = Yii::$app->request->post('product_id', '');
        $limit          = 3;
        $page           = Yii::$app->request->post('page', 1);
        $offset         = ($page - 1) * $limit;
        $offsetCheck = $limit + $offset;
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
        $offsetCheck = $limit + $offset;
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
        $listCategory   = Category::getListCateApp(0, 8, 0);
        $deliveryHot = Advertisement::getAdvertisementHome(null, 100, null, null, 1);

        $productBuy = Advertisement::getAdvertisementHome(1, 20, 0, null, null);
        $productSell = Advertisement::getAdvertisementHome(2, 20, 0, null, null);
        // echo '<pre>';
        // print_r($productBuy);
        // echo '</pre>';die;
        return $this->render('delivery',[
            'listCategory' => $listCategory,
            'deliveryHot' => $deliveryHot,
            'productBuy' => $productBuy,
            'productSell' => $productSell,

        ]);
    }

    public function actionGetProductDelivery()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $page               = !empty(Yii::$app->request->post('page')) ? Yii::$app->request->post('page') : 0;
        $type               = Yii::$app->request->post('type', '');
        $response['data'] = '';
        $response['checkLoadMore'] = false;
        
        $limit = 20;
        $offset = $page * $limit;
        $data     = Advertisement::getAdvertisementHome($type, $limit, $offset);
        $offsetCheck = $limit + $offset;
        $response['checkLoadMore'] = !empty(Advertisement::getAdvertisementHome($type, 1, $offsetCheck)) ? true : false;
        $item = '';
        if (!empty($data)) {
            foreach ($data as $row) {
                $url = Url::to(['/product/detail', 'id' => $row['id']]);
                $item .= '<div class="product_item">
                                <a href="' . $url . '">
                                    <span class="prod_sale">' . $row['percent_discount'] . '% <br> OFF</span>
                                    <img class="prod_avatar" src="' . $row['image'] . '" alt="Image product">
                                    <div class="prod_price_star">
                                        <p class="prod_title line_2" title="' . $row['name'] . '">' . $row['name'] . '</p>
                                        <div class="des_prod mt-2">
                                            <span>' . HelperController::formatPrice($row['price']) . '</span>
                                        </div>
                                    </div>
                                </a>
                            </div>';
            }
        }
        $response['data'] = $item;
        return $response;
    }

    //Chi tiết giao vặt
    public function actionDetailDelivery(){
        $this->view->title = 'Chi tiết giao vặt';
        return $this->render('detail-delivery');
    }

    //Đăng tin giao vặt
    public function actionPostDelivery(){
        $this->view->title = 'Đăng tin giao vặt';
        $model = new Advertisement();
        $formInfo = ApiNewController::AdvertisementFormInfo();
        
        if ($model->load(Yii::$app->request->post())) {
            if(!empty($_FILES)){
               $upload = $this->upload($_FILES);
               if(!empty($upload) && !$upload['status']){
                $model->addError('image', $upload['message']);
                return $this->render('post-delivery', [
                    'formInfo'  => $formInfo,
                    'model' => $model
                ]);
               }
               if(!empty($upload) && $upload['status']){
                $model->image = !empty($upload['arrImage']) ? json_encode($upload['arrImage']) : '';
                $model->video = !empty($upload['arrVideo']) ? json_encode($upload['arrVideo']) : '';
               }
               
            }
            if($model->validate()){
                $model->user_id = Yii::$app->user->identity->id;
                $model->phone = Yii::$app->user->identity->phone;
                if($model->save()){
                    Yii::$app->session->setFlash('success', 'Tạo tin thành công');
                    return $this->redirect(['post-success-delivery']);
                }
            }
        }
        return $this->render('post-delivery', [
            'formInfo'  => $formInfo,
            'model' => $model
        ]);
    }

    //Upload image, video
    public static function upload($files){
        if(!empty($files['Advertisement'])){
            $count_video = 0;
            $count_image = 0;
            $maxSizeImage = 1048576;//1MB
            $maxSizeVideo = 20971520;//20MB
      
            $allowed = ["jpg", "jpeg", "gif", "png", "mp4", "flv", "m4a", "mov"];
            $allowed_video    = array("mp4", "flv", "m4a", "mov");
            $allowed_image    = array("jpg", "jpeg", "gif", "png");
            foreach($files['Advertisement']['type']['image'] as $key => $type_file){
                $extFileType    =pathinfo($files['Advertisement']['name']['image'][$key], PATHINFO_EXTENSION);
                if(!in_array($extFileType, $allowed) && !empty($type_file)) {
                    return [
                        'status' => false,
                        'message'=> "Chỉ chấp nhận file video và file ảnh"
                    ];
                }
                if(in_array($extFileType, $allowed_video) && !empty($type_file)) {
                    $count_video++;
                    if($files['Advertisement']['size']['image'][$key] > $maxSizeVideo){
                        return [
                            'status' => false,
                            'message'=> "Dung lượng video quá lớn. Tối đa 20 MB"
                        ];
                    }
                }elseif(in_array($extFileType, $allowed_image) && !empty($type_file)) {
                    $count_image++;
                    if($files['Advertisement']['size']['image'][$key] > $maxSizeImage){
                        return [
                            'status' => false,
                            'message'=> "Dung lượng ảnh quá lớn. Tối đa 1 MB"
                        ];
                    }
                }
            }
            if($count_video > 3 || $count_image > 8){
                return [
                    'status' => false,
                    'message'=> "Chỉ chấp nhận tối đa 3 video và 8 hình ảnh"
                ];
            }
            $arrVideo = [];
            $arrImage = [];
            foreach($files['Advertisement']['tmp_name']['image'] as $key => $file){
                $extFileType    =pathinfo($files['Advertisement']['name']['image'][$key], PATHINFO_EXTENSION);
                $type = 'image';
                if($files['Advertisement']['type']['image'][$key] == 'video/mp4')
                    $type = 'video';
                $target_dir = $_SERVER['DOCUMENT_ROOT'];
                $path_folder= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $type;
                if( !is_dir($target_dir . $path_folder) ){
                    mkdir($target_dir . $path_folder, 0777, true);
                }
                $file_name  = time() . '_' . preg_replace('/[^a-zA-Z0-9-.]+/', '_', $files['Advertisement']['name']['image'][$key]);
                $path_file  = $path_folder . DIRECTORY_SEPARATOR . $file_name;
                $orgpath    = $target_dir . $path_file;
                if(in_array($extFileType, $allowed_video)) {
                    $arrVideo[] = $path_file;
                }
                if(in_array($extFileType, $allowed_image)) {
                    $arrImage[] = $path_file;
                }
                move_uploaded_file($file, $orgpath);
            }
            return [
                'status' => true,
                'arrVideo' => $arrVideo,
                'arrImage' => $arrImage,
            ];
        }
    }
    //Đăng tin giao vặt thành công
    public function actionPostSuccessDelivery(){
        $this->view->title = 'Đăng tin giao vặt';
        return $this->render('post-success-delivery');
    }
}
