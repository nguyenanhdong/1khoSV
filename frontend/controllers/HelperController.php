<?php
namespace frontend\controllers;

use backend\controllers\ApiNewController;
use common\models\District;
use common\models\Province;
use Yii;
use yii\web\Controller;
use yii\web\Response;

/**
 * Helper controller
 */
class HelperController extends Controller
{
    public static function formatPrice($price){
        if (!is_numeric($price)) {
            return "Invalid amount";
        }
        return number_format($price, 0, ',', '.');
    }
    public static function getCurrentUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $requestUri = $_SERVER['REQUEST_URI'];
        
        return $protocol . $host . $requestUri;
    }

    public function actionGetDistrict(){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $res = [];
        if(!empty($_POST['province_name'])){
            $res = District::getDistrict($_POST['province_name']);
        }
        return $res;
    }
}
