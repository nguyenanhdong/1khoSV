<?php
namespace frontend\controllers;

use backend\controllers\ApiNewController;
use backend\models\Order;
use backend\models\ProductReview;
use common\models\District;
use common\models\Province;
use common\models\Users;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;

/**
 * Info controller
 */
class InfoController extends Controller
{
    public function beforeAction($action)
    {
        if (Yii::$app->user->isGuest) {
            $this->redirect(['/site/login']);
            return false; 
        }
        return parent::beforeAction($action);
    }
    //Ví tích điểm
    public function actionAccPoints(){
        $this->view->title = 'Ví tích điểm';
        return $this->render('accumulate-points');
    }

    //thông tin cá nhân
    public function actionAccInfo(){
        $this->view->title = 'Thông tin cá nhân';
        $model = Users::findOne(Yii::$app->user->identity->id);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if($model->save()){
                Yii::$app->session->setFlash('success', 'Cập nhật thành công');
                return $this->redirect(['acc-info']);
            }
        }
        $province = Province::getProvince();
        $district = [];
        if(!empty($model->province))
            $district = District::getDistrict($model->province);

        return $this->render('account-info', [
            'model' => $model,
            'province' => $province,
            'district' => $district
        ]);
    }

    public function actionGetProductHis(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $type = Yii::$app->request->post('type', '');
        $page = !empty(Yii::$app->request->post('page')) ? Yii::$app->request->post('page') : 0;
        $userId = Yii::$app->user->identity->id;
        $response['data'] = '';
        $response['checkLoadMore'] = false;
        if($type != ''){
            $limit = 2;
            $offset = $page * $limit;
            $offsetCheck = $limit + $offset + 1;
            $data = Order::getOrderOfUserByType($type, $userId, $limit, $offset);
            $dataCheckLoadMore = !empty(Order::getOrderOfUserByType($type, $userId, 1, $offsetCheck)) ? true : false;
            $item = '';
            $btn_action = '';
            if (!empty($data)) {
                $title_item = '';
                switch($type){
                    case 'pending':
                        $title_item = 'Chờ xác nhận';
                        break;
                    case 'confirm':
                        $title_item = 'Đã xác nhận';
                        break;
                    case 'are_delivering':
                        $title_item = 'Đang giao';
                        break;
                    case 'purchased':
                        $title_item = 'Đã mua';
                        break;
                    case 'refund':
                        $title_item = 'Trả hàng';
                        break;
                    default:
                        break;
                }
                foreach ($data as $row) {
                    $btn_action = '';
                    $class_not_purchased = 'not_purchased';
                    if($row['status'] == 3){
                        $class_not_purchased = '';
                        $btn_action = '<a href="'. Url::to(['/product/detail', 'id' => $row['product_id']]) .'" class="btn_action btn-orange flex-center">Mua lại</a>';
                    }
                    $star = '';
                    for($i = 0; $i < $row['star']; $i++){
                        $star .= '<img src="/images/icon/star-active.svg" alt="">';
                    }
                    // $url = Url::to(['/product/detail', 'id' => $row['id']]);
                    $item .= '<div class="group_item_shop d-flex flex-column">
                                <div class="title_shop d-flex justify-content-between">
                                    <p>'. $row['agent_name'].'</p>
                                    <span>'. $title_item .'</span>
                                </div>
                                <div class="item_shop">
                                    <div class="item_shop_left d-flex flex-column">
                                        <div class="desc_item">
                                            <div class="flex-center avatar_pro">
                                                <img src="'. $row['product_image'].'" alt="'. $row['product_name'].'">
                                            </div>
                                            <div class="text_desc d-flex flex-column">
                                                <p>'. $row['product_name'].'</p>
                                                <div class="flex-item-center">
                                                    <strong>'. HelperController::formatPrice($row['price']).'</strong>
                                                    <span>-'. $row['percent_discount'].'%</span>
                                                </div>
                                                <div class="flex-item-center justify-content-between">
                                                    <p>Số lượng '. $row['quantity'].'</p>
                                                    <div class="rating_product flex-item-center">
                                                        '. $star .'
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="item_shop_right d-flex flex-column">
                                        <div class="btn_item">
                                            <div class="action_form '. $class_not_purchased .'">
                                                <a href="'. Url::to(['/info/order-detail', 'id' => $row['order_id']]).'" class="btn_action btn-blue flex-center">Xem chi tiết</a>
                                                '. $btn_action .'
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                }
            }
            $response['data'] = $item;
            $response['checkLoadMore'] = $dataCheckLoadMore;
        }
        return $response;
    }

    //Lịch sử mua hàng Chờ xác nhận
    public function actionAwaitConfirmed(){
        $this->view->title = 'Chờ xác nhận';
        $type = 'pending';
        $userId = Yii::$app->user->identity->id;
        $limit = 2;
        $offset = 0;
        $data = Order::getOrderOfUserByType($type, $userId, $limit, $offset);
        $dataCheckLoadMore = !empty(Order::getOrderOfUserByType($type, $userId, 1, $limit)) ? true : false;
        return $this->render('await-confirmed',[
            'data' => $data,
            'dataCheckLoadMore' => $dataCheckLoadMore,
            'status' => Order::STATUS_PENDING
        ]);
    } 
    //Lịch sử mua hàng Đã xác nhận
    public function actionConfirmed(){
        $this->view->title = 'Đã xác nhận';
        $type = 'confirm';
        $userId = Yii::$app->user->identity->id;
        $limit = 2;
        $offset = 0;
        $data = Order::getOrderOfUserByType($type, $userId, $limit, $offset);
        $dataCheckLoadMore = !empty(Order::getOrderOfUserByType($type, $userId, 1, $limit)) ? true : false;
        return $this->render('confirmed', [
            'data' => $data,
            'dataCheckLoadMore' => $dataCheckLoadMore,
            'status' => $type
        ]);
    } 
    //Lịch sử mua hàng Đang giao
    public function actionDelivering(){
        $this->view->title = 'Đang giao';
        $type = 'are_delivering';
        $userId = Yii::$app->user->identity->id;
        $limit = 2;
        $offset = 0;
        $data = Order::getOrderOfUserByType($type, $userId, $limit, $offset);
        $dataCheckLoadMore = !empty(Order::getOrderOfUserByType($type, $userId, 1, $limit)) ? true : false;
        return $this->render('delivering', [
            'data' => $data,
            'dataCheckLoadMore' => $dataCheckLoadMore,
            'status' => $type
        ]);
    } 
    //Lịch sử mua hàng Đã mua
    public function actionPurchaseHistory(){
        $this->view->title = 'Đã mua';
        $type = 'purchased';
        $userId = Yii::$app->user->identity->id;
        $limit = 2;
        $offset = 0;
        $data = Order::getOrderOfUserByType($type, $userId, $limit, $offset);
        $dataCheckLoadMore = !empty(Order::getOrderOfUserByType($type, $userId, 1, $limit)) ? true : false;
        return $this->render('purchase-history', [
            'data' => $data,
            'dataCheckLoadMore' => $dataCheckLoadMore,
            'status' => $type
        ]);
    } 
    //Lịch sử mua hàng Chi tiết đơn hàng
    public function actionOrderDetail($id){
        $userId = Yii::$app->user->identity->id;
        $data = Order::getOrderDetail($id, $userId);
        $this->view->title = 'Chi tiết đơn hàng';
        return $this->render('order-detail',[
            'data' => $data
        ]);
    } 
    //sản phẩm yêu thích 
    public function actionFavourite(){
        $this->view->title = 'Yêu thích';
        return $this->render('favourite');
    } 
    //sản phẩm đã xem 
    public function actionSeen(){
        $this->view->title = 'Đã xem';
        return $this->render('seen');
    } 
    //mời bạn bè
    public function actionInviteFriend(){
        $this->view->title = 'Mời bạn bè';
        return $this->render('invite-friend');
    } 
    // Giới thiệu
    public function actionIntroduce(){
        $this->view->title = 'Giới thiệu';
        return $this->render('introduce');
    }
    // Đánh giá
    public function actionReview(){
        $this->view->title = 'Đánh giá';
        $userId = Yii::$app->user->identity->id;
        $limit = 2;
        $offset = 0;
        $dataNotReview = ProductReview::getListReviewOfUser($userId, 0, $limit, $offset);
        $dataCheckLoadMoreNotReview = !empty(ProductReview::getListReviewOfUser($userId, 0, 1, $limit)) ? true : false;
        // echo '<pre>';
        // print_r($dataNotReview);
        // echo '</pre>';die;
        $dataReviewd = ProductReview::getListReviewOfUser($userId, 1, $limit, $offset);
        
        return $this->render('review',[
            'dataNotReview' => $dataNotReview,
            'dataReviewd' => $dataReviewd,
            'dataCheckLoadMoreNotReview' => $dataCheckLoadMoreNotReview
        ]);
    }
    // Trả hàng hoàn tiền
    public function actionReturn(){
        $this->view->title = 'Trả hàng hoàn tiền';
        return $this->render('return');
    }
    // Tài khoản
    public function actionProfile(){
        $this->view->title = 'Tài khoản';
        return $this->render('profile');
    }
}
