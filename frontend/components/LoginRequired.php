<?php

namespace frontend\components;

use Yii;
use yii\base\ActionFilter;
use yii\helpers\Url;
use yii\web\Response;

/**
 * Bắt đăng nhập cho khách: request thường → lưu trang đang xem rồi chuyển tới trang đăng nhập;
 * request ajax → trả JSON 401 {status:false, login:true} để JS hiển thị thông báo / chuyển trang.
 */
class LoginRequired extends ActionFilter
{
    public function beforeAction($action)
    {
        if (!Yii::$app->user->isGuest) {
            return true;
        }
        $request = Yii::$app->request;
        $loginUrl = Url::to(['/site/login']);
        if ($request->isAjax) {
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->statusCode = 401;
            $response->data = ['status' => false, 'login' => true, 'message' => 'Vui lòng đăng nhập để tiếp tục', 'loginUrl' => $loginUrl];
            return false;
        }
        if ($request->isGet) {
            Yii::$app->user->setReturnUrl($request->url);
        }
        Yii::$app->response->redirect($loginUrl);
        return false;
    }
}
