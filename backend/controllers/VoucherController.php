<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\User;
use yii\data\ActiveDataProvider;
use yii\data\CArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\db\Query;
use backend\models\Category;
use backend\models\Notify;
use backend\models\NotifySearch;
use backend\models\Product;
use backend\models\ProductSearch;
use backend\models\Voucher;
use backend\models\VoucherSearch;
use yii\base\Model;

class VoucherController extends Controller
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    // 'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel    = new VoucherSearch();
        $dataProvider   = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionCreate()
    {
        $model = new Voucher();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', "Tạo voucher thành công");
            } else
                Yii::$app->session->setFlash('success', "Error!");

            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $modelOld      = $model->getAttributes();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->save(false);

            // if (is_array($modelOld['product_show_home'])) {
            //     $modelOld['product_show_home'] = ';' . implode(';', $modelOld['product_show_home']) . ';';
            // }
            Yii::$app->session->setFlash('success', "Cập nhật voucher thành công");
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = Voucher::findOne($id);
        if ($model) {
            $model->delete();
            // if (is_array($modelOld['product_show_home'])) {
            //     $modelOld['product_show_home'] = ';' . implode(';', $modelOld['product_show_home']) . ';';
            // }

            Yii::$app->session->setFlash('success', "Xóa voucher thành công");
        }
        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

   

    protected function findModel($id)
    {
        if (($model = Voucher::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
