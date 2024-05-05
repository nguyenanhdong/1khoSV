<?php
namespace frontend\controllers;
use yii\web\Controller;

/**
 * Cart controller
 */
class CartController extends Controller
{
    //Giỏ hàng
    public function actionIndex(){
        $this->view->title = 'Giỏ hàng';
        return $this->render('index');
    }
}
