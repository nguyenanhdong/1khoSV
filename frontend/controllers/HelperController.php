<?php
namespace frontend\controllers;

use backend\controllers\ApiNewController;
use yii\web\Controller;


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
}
