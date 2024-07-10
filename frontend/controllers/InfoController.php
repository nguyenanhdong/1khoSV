<?php
namespace frontend\controllers;

use backend\models\Order;
use common\models\District;
use common\models\Province;
use common\models\Users;
use Yii;
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

    //Lịch sử mua hàng Chờ xác nhận
    public function actionAwaitConfirmed(){
        $type = Order::STATUS_PENDING;
        $userId = Yii::$app->user->identity->id;
        $limit = 1;
        $offset = 0;
        $data = Order::getOrderOfUserByType($type, $userId, $limit, $offset);
        $this->view->title = 'Chờ xác nhận';
        return $this->render('await-confirmed',[
            'data' => $data
        ]);
    } 
    //Lịch sử mua hàng Đã xác nhận
    public function actionConfirmed(){
        $this->view->title = 'Đã xác nhận';
        return $this->render('confirmed');
    } 
    //Lịch sử mua hàng Đang giao
    public function actionDelivering(){
        $this->view->title = 'Đang giao';
        return $this->render('delivering');
    } 
    //Lịch sử mua hàng Đã mua
    public function actionPurchaseHistory(){
        $this->view->title = 'Đã mua';
    return $this->render('purchase-history');
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
        return $this->render('review');
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
