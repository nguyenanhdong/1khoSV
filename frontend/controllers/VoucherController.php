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
    public function beforeAction($action)
    {
        if (Yii::$app->user->isGuest) {
            $this->redirect(['/site/login']);
            return false; 
        }
        return parent::beforeAction($action);
    }
    public function actionIndex(){
        $this->view->title = 'Ví voucher';
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
