<?php

namespace backend\controllers;

use Yii;
use backend\models\AuthItem;
use backend\models\Assignment;
use backend\models\searchs\AuthItem as AuthItemSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\base\NotSupportedException;
use yii\filters\VerbFilter;
use yii\rbac\Item;
use backend\models\Util;
use mdm\admin\components\Configs;
use mdm\admin\components\Helper;

/**
 * AuthItemController implements the CRUD actions for AuthItem model.
 *
 * @property integer $type
 * @property array $labels
 * 
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class ItemController extends Controller
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
                    'delete' => ['post'],
                    // 'assign' => ['post'],
                    // 'remove' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all AuthItem models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AuthItemSearch(['type' => $this->type]);
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single AuthItem model.
     * @param  string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $params= $_REQUEST;

        if( isset($params['typeAssign']) && $params['typeAssign'] == 'role' ){
            $modelAssignment = new Assignment(0);
            $modelAssignment->removeRoleAssign($model->name);
            if( isset($params['dataAssign']) && !empty($params['dataAssign']) ){
                $modelAssignment->addRoleAssign($model->name,$params['dataAssign']);
            }
            return 'success';
        }elseif( isset($params['typeAssign']) && $params['typeAssign'] == 'permission' ){
            
            $listItems  = $model->getItems();
            $dataRemove = [];
            $dataAdd    = [];
            
            if( isset($listItems['assigned']) && !empty($listItems['assigned']) ){
                $model->removeChildren( array_keys($listItems['assigned']) );
                $dataRemove = array_keys($listItems['assigned']);
            }
            
            if( isset($params['dataAssign']) && !empty($params['dataAssign']) ){
                $model->addChildren($params['dataAssign']);
                $dataAdd    = $params['dataAssign'];
            }

            if( !empty($dataRemove) || !empty($dataAdd) ){
                $Util               = new Util();
                $objLog             = new \stdClass();
                $objLog->dataremove = $dataRemove;
                $objLog->dataAdd    = $dataAdd;
                $objLog->type       = Item::TYPE_ROLE ? 'role' : 'taks';
                $objLog->adminid    = trim(Yii::$app->user->identity->id);
                $objLog->adminname  = trim(Yii::$app->user->identity->username);
                $Util->writeLog('update' . $objLog->type,json_encode($objLog));
            }
        
            return 'success';
        }

        return $this->render('view', [
            'model' => $model, 
            'type'  => $this->type === Item::TYPE_ROLE ? 'permission' : 'route'
        ]);
    }

    /**
     * Creates a new AuthItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new AuthItem(null);
        $model->type = $this->type;
        
        if ($model->load(Yii::$app->getRequest()->post()) && $model->save()) {
            $Util               = new Util();
            $objLog             = new \stdClass();
            $objLog->data       = Yii::$app->getRequest()->post()['AuthItem'];
            $objLog->type       = Item::TYPE_ROLE ? 'role' : 'taks';
            $objLog->adminid    = trim(Yii::$app->user->identity->id);
            $objLog->adminname  = trim(Yii::$app->user->identity->username);
            
            $Util->writeLog('create' . $objLog->type,json_encode($objLog));
            return $this->redirect(['view', 'id' => $model->name]);
        } else {
            return $this->render('create', ['model' => $model]);
        }
    }

    /**
     * Updates an existing AuthItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param  string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->getRequest()->post()) && $model->save()) {
            $Util               = new Util();
            $objLog             = new \stdClass();
            $objLog->data       = Yii::$app->getRequest()->post()['AuthItem'];
            $objLog->type       = Item::TYPE_ROLE ? 'role' : 'taks';
            $objLog->adminid    = trim(Yii::$app->user->identity->id);
            $objLog->adminname  = trim(Yii::$app->user->identity->username);
            $Util->writeLog('update_name_' . $objLog->type,json_encode($objLog));
            return $this->redirect(['view', 'id' => $model->name]);
        }

        return $this->render('update', ['model' => $model]);
    }

    /**
     * Deletes an existing AuthItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param  string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        Configs::authManager()->remove($model->item);
        Helper::invalidate();

        return $this->redirect(['index']);
    }

    /**
     * Assign items
     * @param string $id
     * @return array
     */
    /*public function actionAssign($id)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $model = $this->findModel($id);
        $success = $model->addChildren($items);
        Yii::$app->getResponse()->format = 'json';

        return array_merge($model->getItems(), ['success' => $success]);
    }*/

    /**
     * Assign or remove items
     * @param string $id
     * @return array
     */
    /*public function actionRemove($id)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $model = $this->findModel($id);
        $success = $model->removeChildren($items);
        Yii::$app->getResponse()->format = 'json';

        return array_merge($model->getItems(), ['success' => $success]);
    }*/

    /**
     * @inheritdoc
     */
    public function getViewPath()
    {
        return $this->module->getViewPath() . DIRECTORY_SEPARATOR . 'item';
    }

    /**
     * Label use in view
     * @throws NotSupportedException
     */
    public function labels()
    {
        throw new NotSupportedException(get_class($this) . ' does not support labels().');
    }

    /**
     * Type of Auth Item.
     * @return integer
     */
    public function getType()
    {
        
    }

    /**
     * Finds the AuthItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return AuthItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $auth = Configs::authManager();
        $item = $this->type === Item::TYPE_ROLE ? $auth->getRole($id) : $auth->getPermission($id);
        if ($item) {
            return new AuthItem($item);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
