<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\Banner;
use backend\models\BannerSearch;
use backend\models\Order;
use backend\models\OrderSearch;

class OrderController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['GET'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $user = Yii::$app->user->identity;

        return true;
    }

    /**
     * Lists all CategoryTags models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OrderSearch();
        
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
  

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

   
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $model->save(false);
            Yii::$app->session->setFlash('message', "Cập nhật đơn hàng thành công");
            return $this->redirect(['index']);
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = Banner::findOne($id);
        if ($model) {
            $model->is_delete = 1;
            $model->save(false);
            Yii::$app->session->setFlash('success', "Xóa Banner thành công");
        }
        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    protected function findModel($id)
    {
        if (($model = Order::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
