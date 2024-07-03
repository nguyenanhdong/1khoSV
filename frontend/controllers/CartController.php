<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;

/**
 * Cart controller
 */
class CartController extends Controller
{
    public function beforeAction($action)
    {
        if (Yii::$app->user->isGuest) {
            $this->redirect(['/site/login']);
            return false; 
        }
        return parent::beforeAction($action);
    }
    //Giỏ hàng
    public function actionIndex(){
        $this->view->title = 'Giỏ hàng';
        return $this->render('index');
    }
}
