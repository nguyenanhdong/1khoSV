<?php
namespace frontend\controllers;

use backend\controllers\ApiNewController;
use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use common\models\Users;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ForgotPassword;
use frontend\models\Contact;
use backend\models\News;

use backend\models\UserLogin;
use backend\models\Config;
use backend\models\EmailPromotion;
use common\helpers\Helper;
use common\helpers\Response;
use common\models\AccountLoginFirebaseForm;

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
                'only' => ['logout'],
                'rules' => [
                    // [
                    //     'actions' => ['signup'],
                    //     'allow' => true,
                    //     'roles' => ['?'],
                    // ],
                    [
                        'actions' => ['logout','historyOrder'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post','get'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'oAuthFb'],
            ],
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    public function oAuthFb($client)
    {
        try {
            $userAttributes = $client->getUserAttributes();
            if (is_array($userAttributes) && $userAttributes) {
                /*
                * Check có tài khoản hay chưa bằng fb_id
                */
                // $model = new UsersLoginForm();
                $fullname   = '';
                $email      = '';
                
                $email      = isset($userAttributes['email']) ? $userAttributes['email'] : '';
                $fullname   = $userAttributes['name'];
                $fb_id      = $userAttributes['id'];
                $app_access_token = "1637958006659754|NThZ9F49ZlHR_7WvYucvwFdel_8";
                $avatar      = "https://graph.facebook.com/". $fb_id ."/picture?type=large&access_token=" . $app_access_token;
                //Kiểm tra xem id fb đã tồn tại chưa: Nếu có thì login / Tạo tài khoản mới
                $checkExist = Users::findOne(['fb_id'=>$fb_id]);
                if( !$checkExist && !empty($email) ){
                    $checkExist = Users::findOne(['email'=>$email]);
                }

                if( $checkExist ){
                    $checkExist->fb_id = $fb_id;
                    $checkExist->save(false);
                    $user           = $checkExist;
                }else{
                    $user           = new Users();
                    $user->fullname = $fullname;
                    $user->email    = $email;
                    $user->fb_id    = $fb_id;
                    $user->avatar   = $avatar;
                    // $user->generateEmailVerificationToken();
                    $user->save(false);
                    // if( !empty($email) ){
                    //     SignupForm::sendEmail($user);
                    // }
                }
                Yii::$app->user->login($user, 3600 * 24 * 30 );
                return $this->goHome();
            } else {
                
            }
            return $this->goHome();
        } catch (\Exception $e) {
            var_dump($e);die;
        }
    }
    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        // $modelLogin = new AccountLoginFirebaseForm();
        // $modelLogin->phone = '+84983182596';
        // if( $modelLogin->login() ){
        //     return [
        //         'status' => true,
        //         'message'=> 'Login successful',
        //     ];
        // }else{
        //     return [
        //         'status' => false,
        //         'message'=> 'Login failed',
        //     ];
        // }
        // die;

        // var_dump(Yii::$app->user->isGuest);die;
        // $user = Yii::$app->user->identity;
        // echo '<pre>';
        // print_r($user);
        // echo '</pre>';die;
        $dataHome = ApiNewController::Home();
        // echo '<pre>';
        // print_r($dataHome);
        // echo '</pre>';die;
        return $this->render('index',[
         'dataHome' => isset($dataHome['data']) ? $dataHome['data'] : []
        ]);
    }
    //chính sách đổi trả
    public function actionReturnPolicy(){
        $this->view->title = 'Chính sách đổi trả hàng hoá';
        return $this->render('return');
    }
    //chính sách bảo mật
    public function actionPrivacyPolicy(){
        $this->view->title = 'Chính sách bảo mật';
        return $this->render('privacy');
    }
    //chính sách bảo hành
    public function actionGuarantee(){
        $this->view->title = 'Chính sách bảo hành';
        return $this->render('guarantee');
    }
    // public function actionLogin()
    // {   
    //     $model = new LoginForm();
    //     if ($model->load(Yii::$app->request->post())) {
    //         Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    //         if (!$model->validate()) {
    //             foreach($model->getErrors() as $row){
    //                 return [
    //                     'status' => 0,
    //                     'message' => $row['0']
    //                 ];
    //                 break;
    //             }
    //         }
    //         $user = Users::findOne(['email' => $model->username]);
    //         if($user['date_banned'] != null){
    //             $date_curren = strtotime(date("Y/m/d H:i:s"));
    //             $time_banner = strtotime($user['date_banned']);
    //             if($time_banner > $date_curren)
    //                 return [
    //                     'status' => 0,
    //                     'message' => 'Tài khoản của bạn đang bị khóa'
    //                 ];

    //         }

    //         if( $model->login() ){
           
    //         }
    //         else
    //             return [
    //                 'status' => 0,
    //                 'message' => 'Thông tin đăng nhập không chính xác'
    //             ];
    //     } else {
    //         $model->password = '';

    //         return $this->render('login', [
    //             'model' => $model,
    //         ]);
    //     }
    // }
    public function actionLogin(){
        if( Yii::$app->request->isPost ){
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $postData = Yii::$app->request->post();
            if( isset($postData['type']) && !empty($postData['type']) && isset($postData['token']) && !empty($postData['token']) ){
                $type = $postData['type'];
                $token= $postData['token'];
                
                if( $type == "idToken" ){
                    $data = $this->_validateIdTokenFireBase($token);
                }else{
                    $data = $this->_validateAccessTokenFireBase($token);
                }

                if( $data['status'] ){
                    /*
                    * Viết code tạo tài khoản, login tại đây
                    */
                    $dataUser       = $data['data'];
                    $modelAccount   = null;
                    $phone          = '';
                    if( isset($dataUser['phone']) && !empty($dataUser['phone']) ){
                        $phone      = $dataUser['phone'];
                        $modelAccount = Users::findByPhone($phone);
                    }
                    
                    //Create account if not exits
                    if( !$modelAccount ){
                        $modelAccount = new Users;
                        $modelAccount->phone = $phone;
                        $modelAccount->status = 1;
                        $modelAccount->status = Users::STATUS_ACTIVE;
                        $modelAccount->is_verify_account = Users::ACCOUNT_VERIFYED;
                        $modelAccount->create_at = date('Y-m-d H:i:s');
                        $modelAccount->save(false);
                    }

                    //Login
                    $modelLogin = new AccountLoginFirebaseForm();
                    $modelLogin->phone = $phone;
                    if( $modelLogin->login() ){
                        return [
                            'status' => true,
                            'message'=> 'Login successful',
                        ];
                    }else{
                        return [
                            'status' => false,
                            'message'=> 'Login failed',
                        ];
                    }
                }
            }else{
                return [
                    'status' => false,
                    'message'=> 'Invalid information'
                ];
            }
        }
        return $this->render('login');
    }
    public static function _validateIdTokenFireBase($idToken)
    {
        $apiKey     = Yii::$app->params['fireBase']['login']['apiKey'];
        $url        = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . $apiKey;
        $ch         = curl_init ();
        $fields     = json_encode ( ['idToken' => $idToken] );
        $headers    = array (
            "Content-Type: application/json"
        );
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );

        $result     = curl_exec ( $ch );
        $http_status= curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close ( $ch );
        if( $http_status == 200 ){
            $data       = json_decode($result, true);
            if( is_array($data) && isset($data['users']) && isset($data['users'][0])){
                $info = $data['users'][0];
                if( isset($info['phoneNumber']) ){
                    $phone      = str_replace(' ', '', $info['phoneNumber']);//preg_replace("/[^0-9]+/", "", $info['phoneNumber']);
                    // $arrPhone   = str_split($phone);
                    // if ($arrPhone[0] != '0') {
                    //     $phone  = '0' . substr($phone, 2);
                    // }
                    return [
                        'status' => true,
                        'data'   => [
                            'phone'     => $phone,
                            'email'     => '',
                            'firstName' => '',
                            'lastName'  => ''
                        ]
                    ];
                }else if( isset($info['email']) ){
                    $displayName = explode(' ', $info['displayName']);
                    $firstName   = $displayName[0];
                    array_shift($displayName);
                    $lastName    = implode(' ', $displayName);
                    return [
                        'status' => true,
                        'data'   => [
                            'email'     => $info['email'],
                            'phone'     => '',
                            'firstName' => $firstName,
                            'lastName'  => $lastName,
                        ]
                    ];
                }
            }
        }else{
            return [
                'status' => false,
                'message'=> 'Error! Authentication failed'
            ];
        }
        return [
            'status' => false,
            'message'=> 'Error! Authentication failed'
        ];
    }
    public static function _validateAccessTokenFireBase($idToken)
    {
        $url        = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $idToken;
        $ch         = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );

        $result     = curl_exec ( $ch );
        $http_status= curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close ( $ch );
        if( $http_status == 200 ){
            $data       = json_decode($result, true);
            if( is_array($data) && isset($data['id']) && isset($data['email'])){
                return [
                    'status' => true,
                    'data'   => [
                        'email'     => $data['email'],
                        'phone'     => '',
                        'firstName' => $data['given_name'],
                        'lastName'  => $data['family_name'],
                    ]
                ];   
            }else{
                return [
                    'status' => false,
                    'message'=> 'Error! Authentication failed'
                ];
            } 
        }else{
            return [
                'status' => false,
                'message'=> 'Error! Authentication failed'
            ];
        }
        return [
            'status' => false,
            'message'=> 'Error! Authentication failed',
        ];
    }
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }







    







    public function actionAbout(){
        $this->view->title = 'Giới thiệu';
        Yii::$app->view->registerMetaTag([
            'name' => 'description',
            'content' => "Công ty CP thức ăn chăn nuôi PHAVICO là một trong những nhà sản xuất thức ăn chăn nuôi uy tín hàng đầu tại Việt Nam"
        ]);
        Yii::$app->view->registerMetaTag([
            'property' => 'og:image',
            'prefix' => 'og: http://ogp.me/ns#',
            'content' => '/images/icon/logo-fa.svg'
        ]);
        Yii::$app->view->registerMetaTag([
            'name' => 'og:description',
            'content' => "Công ty CP thức ăn chăn nuôi PHAVICO là một trong những nhà sản xuất thức ăn chăn nuôi uy tín hàng đầu tại Việt Nam"
        ]);
        Yii::$app->view->registerMetaTag([
            'name' => 'twitter:description',
            'content' => "Công ty CP thức ăn chăn nuôi PHAVICO là một trong những nhà sản xuất thức ăn chăn nuôi uy tín hàng đầu tại Việt Nam"
        ]);
        // Yii::$app->view->registerMetaTag([
        //     'name' => 'description',
        //     'content' => ''
        // ]);
      
        return $this->render('about', [
           
        ]);
    }

    public function actionSaveEmailOffer(){
        if(isset($_POST['email'])){
            $check_isset = EmailPromotion::findOne(['email' => $_POST['email']]);
            if(!$check_isset){
                $model = new EmailPromotion();
                $model->email = $_POST['email'];
                if($model->save(false));
                return 1;
            }else{
                return 2;
            }
        }
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $address = Config::find()->where(['status' => 1,'type' => 'address'])->orderBy(['position' => SORT_ASC])->asArray()->one();
        $hotline = Config::find()->where(['status' => 1,'type' => 'hotline'])->orderBy(['position' => SORT_ASC])->asArray()->one();

        $this->view->title = 'Liên hệ - Phavico';
        Yii::$app->view->registerMetaTag([
            'name' => 'description',
            'content' => 'Công ty CP thức ăn chăn nuôi Phavico, Hotline: ' . $hotline['name'] . ', địa chỉ: ' . $address['name']
        ]);
        Yii::$app->view->registerMetaTag([
            'name' => 'og:description',
            'content' => 'Công ty CP thức ăn chăn nuôi Phavico, Hotline: ' . $hotline['name'] . ', địa chỉ: ' . $address['name']
        ]);
        Yii::$app->view->registerMetaTag([
            'name' => 'twitter:description',
            'content' => 'Công ty CP thức ăn chăn nuôi Phavico, Hotline: ' . $hotline['name'] . ', địa chỉ: ' . $address['name']
        ]);
        $model = new Contact();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $post = Yii::$app->request->post('Contact');
            $model->name = $post['name'];
            $model->phone = $post['phone'];
            $model->email = $post['email'];
            $model->address = $post['address'];
            $model->note = $post['note'];
            $model->create = date('Y-m-d h:i:s');
            $model->save(false);
            Yii::$app->session->setFlash('success', 'Cảm ơn bạn đã để lại lời nhắn. Chúng tôi sẽ liên hệ lại với bạn trong 24h tới.');
            
            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    


    private function checkLogin(){
        if (!Yii::$app->user->isGuest) {
            $user       = Yii::$app->user->identity;
            $maxLogin   = 3;//Yii::$app->params['max_login'];
            $totalLoginPlace = UserLogin::find()->where(['user_id'=>$user->id,'status'=>0])->count();
            $user_login = UserLogin::find()->where(['user_id'=>$user->id,'status'=>0])->asArray()->all();
            $list_ip = array_column($user_login,'ip_address');
            $ip_current = $_SERVER['REMOTE_ADDR'];
            $modelUser  = Users::findOne(['id'=>$user->id]);
            if(in_array($ip_current,$list_ip)){
                return true;
            }
            //Check total login place with config params
            if( $totalLoginPlace < $maxLogin ){
                // $browser        = get_browser(null, true);
                $ip_address     = $_SERVER['REMOTE_ADDR'];
                $user_agent     = $_SERVER['HTTP_USER_AGENT'];
                $os             = NULL;//$browser['platform'];
                $browser_name   = NULL;//$browser['browser'];
                $version        = NULL;//str_replace('.0','',$browser['version']);
                // if( $version == 0 || strpos($user_agent,'coc_coc_browser') !== false ){
                //     $dataUg     = explode(' ', $user_agent);
                //     foreach($dataUg as $r){
                //         if( strpos($r,'coc_coc_browser') !== false ){
                //             $version = str_replace('coc_coc_browser/','', $r);
                //             break;
                //         }
                //     }
                // }
                $device         = NULL;//$browser['device_type'] == 'Desktop' ? 'PC' : 'Mobile';
                $checkIpExits   = UserLogin::find()->where(['user_id'=>$user->id,'user_agent'=>$user_agent, 'ip_address' => $ip_address, 'is_revoke'=>0])->one();
                if( !$checkIpExits ){
                    $model      = new UserLogin;
                    $model->user_id     = $user->id;
                    $model->time_login  = date('Y-m-d H:i:s');
                    $model->os          = $os;
                    $model->browser     = $browser_name;
                    $model->version     = $version;
                    $model->user_agent  = $user_agent;
                    $model->device      = $device;
                    $model->ip_address  = $ip_address;
                    $model->token       = md5($user_agent . $ip_address . $user->id);
                    $model->save(false);
                    $totalLoginPlace++;

                    Yii::$app->response->cookies->add(new \yii\web\Cookie([
                        'name' => 'tklgusf',
                        'value' => $model->token,
                        'expire' => time() + 86400 * 30,
                    ]));
                    if( $totalLoginPlace == $maxLogin ){
                        //Send email warning
    
                    }
                }else{
                    if( $modelUser->status == 1 ){
                        $modelUser->time_login  = date('Y-m-d H:i:s');
                        $modelUser->ip_address  = $ip_address;
                        $modelUser->status = 0;
                        $modelUser->save(false);
                    }
                }
                
                return true;
            }else{
                //Lock account
                $modelUser->status = 3;
                $modelUser->save(false);

                Yii::$app->user->logout();

                Yii::$app->session->setFlash('error', 'Bạn đã đăng nhập đối đa 3 thiết bị. Vui lòng đăng xuất ở thiết bị khác trước khi đăng nhập.');

                return false;
            }
        }
        
        return true;
    }




    /*
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) ) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if (!$model->validate()) {
                foreach($model->getErrors() as $row){
                    return [
                        'status' => 0,
                        'message' => $row['0']
                    ];
                    break;
                }
            }
            if($model->signup()){
                // Yii::$app
                // ->mail
                // ->compose()
                // ->setFrom(['support@elearning.abe.edu.vn' => 'ABE Academy'])
                // ->setTo($model->email)
                // ->setSubject('Đăng ký tài khoản thành công')
                // ->setHtmlBody('<p>Chúc mừng bạn đã đăng ký tài khoản thành công</p>')
                // ->send();
                return [
                    'status' => 1,
                    'message' => "Success"
                ];
            }
            else
                return [
                    'status' => 0,
                    'message' => 'Có lỗi xảy ra. Vui lòng thử lại'
                ];
        }

        return $this->renderAjax('signup', [
            'model' => $model,
        ]);
    }
    /*
     * Update user up.
     *
     * @return mixed
     */
    public function actionUpdatephone()
    {
        $models = new SignupForm();
        if(isset($_POST['phone'])){
            $model = Users::findOne(['id' => Yii::$app->user->identity->id]);
            $model_check = Users::findOne(['phone' => $_POST['phone']]);
            if(empty($model_check)){
                $model->phone = $_POST['phone'];
                $model->save(false);
                return 1;
            }else{
                return 2;
            }
       
        }
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionForgotPassword()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new ForgotPassword();
        if ($model->load(Yii::$app->request->post()) ) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if (!$model->validate()) {
                foreach($model->getErrors() as $row){
                    return [
                        'status' => 0,
                        'message' => $row['0']
                    ];
                    break;
                }
            }
            if( $model->forgot() )
                return [
                    'status' => 1,
                    'message'=> 'Yêu cầu khôi phục mật khẩu thành công. Mật khẩu mới đã được gửi vào email của bạn'
                ];
            else
                return [
                    'status' => 0,
                    'message' => 'Có lỗi xảy ra. Vui lòng thử lại'
                ];          
        }
        return $this->renderAjax('forgot_password', [
            'model' => $model,
        ]);
        
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        if(empty($token) || !Yii::$app->user->isGuest){
            return $this->goHome();
        }
        if(isset($_POST['password']) && !empty($_POST['password'])){
            $model = Users::find()
            ->where(['verification_token' => $token]) 
            ->one();
            $pass = $_POST['password'];
            $model->password = md5(md5($pass));
            $model->save(false);
            return $this->goHome();
        }
        return $this->render('resetPassword', [
        ]);
    }


    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        $model     = Users::findOne(['verification_token'=>$token]);
        if( $model ){
            $model->is_verify_account = 1;
            $model->verification_token= '';
            $model->date_verify_email = date('Y-m-d H:i:s');
            $model->save(false);

            Yii::$app->session->setFlash('success', 'Xác thực tài khoản thành công');
        }
        
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
    }
    public function actionRegisterfree(){
        if (!Yii::$app->user->isGuest) {
            $this->redirect('/product/index');
        }else{
            $this->redirect('/site/signup');
        }
    }

    public function actionTerms(){
        return $this->render('terms',[
        ]);
    }


    public function actionRefund(){
        return $this->render('refund');
    }
    /**
    * Function xử lý push log lượt xem bài viết, chuyên mục
    */
    public function actionTracking(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        if( $request->isPost ){
            $params_post = $request->post();
            $params      = $this->validateParamsLog($params_post);
            if( is_array($params) ){
                $params_push_queue = [
                    'id'            => (int)$params['tracking_id'],
                    'type'          => (int)$params['tracking_type'],
                    'date'          => date('Y-m-d H:i:s'),
                    'session_id'    => $params['session'],
                    'user_agent'    => $params['user_agent'],
                    'ip_address'    => $params['ip_address'],
                    'url'           => $params['url'],
                    'user_id'       => !Yii::$app->user->isGuest ? Yii::$app->user->identity->id : 0
                ];
                
                Yii::$app->queue->push(new \backend\components\ProcessLogView($params_push_queue));
            }
        }
        exit();
    }

}
