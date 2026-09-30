<?php
namespace frontend\controllers;

use backend\models\Voucher;
use Yii;
use yii\web\Controller;

/**
 * Voucher controller
 */
class VoucherController extends Controller
{
    public function behaviors()
    {
        return ['login' => ['class' => \frontend\components\LoginRequired::class]];
    }
    public function actionIndex(){
        $this->view->title = 'Voucher của tôi';
        $userId = Yii::$app->user->identity->id;
        $dataUnused = Voucher::getListVoucherAppCustomer(1, $userId);
        $dataUsed = Voucher::getListVoucherAppCustomer(2, $userId);
        // echo '<pre>';
        // print_r($dataUsed);
        // echo '</pre>';die;
        return $this->render('index',[
            'dataUnused' => $dataUnused,
            'dataUsed' => $dataUsed,
        ]);
    }
}
