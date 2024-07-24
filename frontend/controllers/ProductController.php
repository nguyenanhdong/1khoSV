<?php
namespace frontend\controllers;

use backend\controllers\ApiNewController;
use backend\models\Advertisement;
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
        $offsetCheck = $limit + $offset + 1;
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
