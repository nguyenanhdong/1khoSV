<?php
namespace frontend\controllers;
use yii\web\Controller;

/**
 * Info controller
 */
class InfoController extends Controller
{
    //Ví tích điểm
    public function actionAccPoints(){
        $this->view->title = 'Ví tích điểm';
        return $this->render('accumulate-points');
    }
}
