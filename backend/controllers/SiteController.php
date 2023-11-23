<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use backend\models\Customer;
use backend\models\OrderWork;
use DatePeriod;
use DateTime;
use DateInterval;
use backend\controllers\CommonController;
use backend\models\Util;
/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error','test'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
        ];
    }
    public function beforeAction( $action ) {
        if ( parent::beforeAction ( $action ) ) {
    
             //change layout for error action after 
             //checking for the error action name 
             //so that the layout is set for errors only
            if ( $action->id == 'error' ) {
                die('Page not found');
            }
            return true;
        } 
    }
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
    public function actionOnError(){
        echo 123;die;
    }
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $date_start    = date('Y-m-d', strtotime(' - 6 day', time()));
        $date_end      = date('Y-m-d');
        if( isset($_POST['ajax']) ){
            return json_encode($this->getDataStatistic($_POST['type'], $_POST['date_start'],$_POST['date_end'],true));
        }
        $dataStatistic = $this->getDataStatistic(false, $date_start,$date_end);
        
        return $this->render('index',[
            'dataStatistic' => $dataStatistic,
            'date_end' => $date_end,
            'date_start'=> $date_start
        ]);
    }

    private function getDataStatistic($isChartOnly = false, $date_start = '', $date_end = '',$isAjax=false){
        $data = [
            'user_register' => 0,
            'total_transaction' => 0,
            'total_money'   => 0,
            'total_money_draft' => 0,
            'chart'         => [
                'user_register' => [],
                'transaction'   => [],
                'transaction_success' => []
            ]
        ];
        if( !$isChartOnly ){
            $condition      = ['id > 0'];
            if( $isAjax ){
                if( $date_start != '' ){
                    $condition[] = ' create_at >= "' . $date_start . '"';
                }
                if( $date_end != '' ){
                    $condition[] = ' create_at <= "' . $date_end . ' 23:59:59"';
                }
            }
            $condition      = implode(' && ', $condition);
            $data['user_register']      = number_format(Customer::find()->where($condition)->count(),0,'.','.');
            $data['total_transaction']  = number_format(OrderWork::find()->where($condition)->count(),0,'.','.');
            $data['total_money']        = number_format(Yii::$app->db->CreateCommand('SELECT IFNULL(SUM(price),0) as total from order_work where status = 2 and ' . $condition)->queryScalar(),0,'.','.');
            $data['total_money_draft']  = number_format(Yii::$app->db->CreateCommand('SELECT IFNULL(SUM(price),0) as total from order_work where status IN (0,1,2) and ' . $condition)->queryScalar(),0,'.','.');
        }
        
       
        $condition_user_register = '1';
        $params_user_register    = [];
        if( $date_start != '' ){
            $condition_user_register .= ' && create_at >= :date_start';
            $params_user_register[':date_start'] = $date_start;
        }
        
        if( $date_end != '' ){
            $condition_user_register .= ' && create_at <= :date_end';
            $params_user_register[':date_end'] = $date_end . ' 23:59:59';
        }
        
        $sql_statistic_user_register = "
            SELECT count(id) as total, date_format(create_at,'%d/%m/%Y') as date FROM user where " . $condition_user_register . " group by date_format(create_at,'%d/%m/%Y')
        ";
        $result_user_register = Yii::$app->db->CreateCommand($sql_statistic_user_register, $params_user_register)->queryAll();
        $dataUserRegister     = [];
        if( !empty($result_user_register) ){
            foreach($result_user_register as $row){
                $dataUserRegister[$row['date']] = (int)$row['total'];
            }
        }

        $sql_statistic_transaction = "
            SELECT count(id) as total, date_format(create_at,'%d/%m/%Y') as date FROM order_work where " . $condition_user_register . " group by date_format(create_at,'%d/%m/%Y')
        ";
        $result_transaction = Yii::$app->db->CreateCommand($sql_statistic_transaction, $params_user_register)->queryAll();
        $dataTransaction     = [];
        if( !empty($result_transaction) ){
            foreach($result_transaction as $row){
                $dataTransaction[$row['date']] = (int)$row['total'];
            }
        }

        $sql_statistic_transaction_success = "
            SELECT count(id) as total, date_format(create_at,'%d/%m/%Y') as date FROM order_work where " . $condition_user_register . " and status = 2 group by date_format(create_at,'%d/%m/%Y')
        ";
        $result_transaction = Yii::$app->db->CreateCommand($sql_statistic_transaction_success, $params_user_register)->queryAll();
        $dataTransactionSuccess     = [];
        if( !empty($result_transaction) ){
            foreach($result_transaction as $row){
                $dataTransactionSuccess[$row['date']] = (int)$row['total'];
            }
        }

        $date_end   = date('Y-m-d', strtotime(' + 1 day',strtotime($date_end)));
        $period = new DatePeriod(
            new DateTime($date_start),
            new DateInterval('P1D'),
            new DateTime($date_end)
        );
        foreach ($period as $key => $value) {
            $date = $value->format('d/m/Y');
            
            $data['chart']['user_register'][] = array($date, (isset($dataUserRegister[$date]) ? $dataUserRegister[$date] : 0));
            $data['chart']['transaction'][] = array($date, (isset($dataTransaction[$date]) ? $dataTransaction[$date] : 0));
            $data['chart']['transaction_success'][] = array($date, (isset($dataTransactionSuccess[$date]) ? $dataTransactionSuccess[$date] : 0));
        }
        
        return $data;
    }
    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        $this->layout = 'login';
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $this->layout = 'login';
        $model = new LoginForm();
        $model->login_backend = 1;
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            Yii::$app->user->identity->save();
            if( isset($_GET['return']) && !empty($_GET['return']) )
                return $this->redirect($_GET['return']);
            return $this->goBack();

        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionTest(){
        // var_dump(Util::sendOneSignal(6,'demo title','demo description'));die;
    }
}
