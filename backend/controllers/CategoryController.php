<?php

namespace backend\controllers;

use Yii;
use backend\models\Comment;
use backend\models\CommentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\User;
use yii\data\ActiveDataProvider;
use yii\data\CArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\db\Query;
use yii\imagine\Image;
use backend\controllers\CommonController;
use backend\models\ActionLog;
use backend\models\Category;
use backend\models\CategorySearch;
use backend\models\ProductCategory;
use yii\base\Model;

class CategoryController extends Controller
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
        $searchModel    = new CategorySearch();
        $dataProvider   = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionCreate()
    {
        $model = new Category();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', "Tạo chuyên mục thành công");
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
            Yii::$app->session->setFlash('success', "Cập nhật chuyên mục thành công");
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = Category::findOne($id);
        if ($model) {
            $modelOld   = $model->getAttributes();
            $model->is_delete = 1;
            $model->save(false);

            // if (is_array($modelOld['product_show_home'])) {
            //     $modelOld['product_show_home'] = ';' . implode(';', $modelOld['product_show_home']) . ';';
            // }

            Yii::$app->session->setFlash('success', "Xóa chuyên mục thành công");
        }
        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

   

    protected function findModel($id)
    {
        if (($model = Category::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
