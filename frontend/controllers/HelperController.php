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
    public static function getStar($star){
        
    }
}
