<?php
namespace frontend\controllers;
use yii\web\Controller;

/**
 * Category controller
 */
class CategoryController extends Controller
{
    public function actionIndex(){

        return $this->render('index');
    }
}
