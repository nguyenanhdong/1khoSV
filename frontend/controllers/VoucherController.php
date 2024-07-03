<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;

/**
 * Voucher controller
 */
class VoucherController extends Controller
{
    public function actionIndex(){
        if(Yii::$app->user->isGuest){
             return $this->redirect(['/site/login']);
        }
        return $this->render('index');
    }
}
