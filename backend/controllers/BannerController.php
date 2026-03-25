<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\Banner;
use backend\models\BannerSearch;

class BannerController extends Controller
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
        $searchModel = new BannerSearch();
        
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $model = new Banner();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new Banner();
        
        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            if( $model->save() ){
                Yii::$app->session->setFlash('message', "Add banner success");
            }else{
                Yii::$app->session->setFlash('message', "Error!");
            }
                
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $model->save(false);
            Yii::$app->session->setFlash('message', "Cập nhật banner thành công");
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
        if (($model = Banner::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
