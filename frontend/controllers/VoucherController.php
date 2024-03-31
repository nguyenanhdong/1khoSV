<?php
namespace frontend\controllers;
use yii\web\Controller;

/**
 * Voucher controller
 */
class VoucherController extends Controller
{
    public function actionIndex(){

        return $this->render('index');
    }
}
