<?php

namespace backend\controllers;

use DateTime;
use yii\db\Expression;
use Cassandra\Date;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use backend\models\Customer;
use backend\models\HistoryWalletPoint;
use backend\models\Banner;
use backend\models\Category;
use backend\models\ProductSale;
use backend\models\ProductReview;
use backend\models\Advertisement;
use yii\helpers\ArrayHelper;
use backend\models\Util;
use common\helpers\Response;
use common\helpers\Jwt\JWT;

class ApiController extends Controller
{

    const TYPE_STRING = 'string';
    const TYPE_INT = 'int';
    const TYPE_ARRAY = 'array';

    public $urlDomain = 'http://1khosv.com';
    public $urlDomainAvatarStaff = 'https://api.1khosv.com';
    public $linkShare = "https://1khosv.com/";

    /** Telegram */
    public $chat_id = -581513801;
    public $token_bot = '1676133200:AAHU68FSplWBDqQ2p0KlBY28VO2l-6AoFCQ';
    /** End Telegram */

    /** Firebase */
    public $projectIdFireBase = 'fastjob-74057';
    public $fireBaseApiKey = 'AIzaSyCrWJqgtPExKWILZtAut1-cmxACjtwc7oY';
    /** End Firebase */

    public $userId;

    public function behaviors()
    {
        if( isset($_REQUEST['from']) && $_REQUEST['from'] == 'CMS' ){
            return [];
        }else{
            return [
                'corsFilter' => [
                    'class' => \yii\filters\Cors::className(),
                    'cors' => [
                        // restrict access to
                        'Origin' => ['*'],
                        'Access-Control-Request-Method' => ['POST', 'GET'],
                        // Allow only POST and PUT methods
                        'Access-Control-Request-Headers' => ['*'],
                        // Allow only headers 'X-Wsse'
                        'Access-Control-Allow-Credentials' => true,
                        'Access-Control-Allow-Headers' => ['Origin, X-Requested-With, Content-Type, Accept, Authorization'],
                        // Allow OPTIONS caching
                        'Access-Control-Max-Age' => 3600,
                        // Allow the X-Pagination-Current-Page header to be exposed to the browser.
                        'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
                    ],

                ],

            ];
        }
    }

    /**
     * @param $action
     * @return bool|void
     * @throws BadRequestHttpException
     */
    public function beforeAction($action)
    {
        date_default_timezone_set('Asia/Saigon');
        $this->enableCsrfValidation = false;
        header('Content-Type: application/json;charset=utf-8');
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $request    = Yii::$app->request;
        $msgError   = "";
        if ($request->isPost) {
            $headers = apache_request_headers();
            $headers = array_change_key_case($headers, CASE_LOWER);
            
            if (isset($headers['authorization']) && !empty($headers['authorization'])) {
                // $msgError       = Response::getErrorMessage('authorization', Response::KEY_INVALID);
                $authorization  = $headers['authorization'];
                $token          = str_replace('Bearer ', '', $authorization);

                $dataToken      = $this->decryptTokenWithJWT($token);

                if ( $dataToken ) {
                    // $msgError     = "";
                    $this->userId = $dataToken['id'];
                }
            }
        } else {
            $msgError = Response::getErrorMessage('request_method', Response::KEY_INVALID);
        }
        if ($msgError) {
            echo json_encode(Response::returnResponse(Response::RESPONSE_CODE_ERR, [], [$msgError]), JSON_UNESCAPED_UNICODE);
            exit();
        }
        return parent::beforeAction($action);
    }

    public static function getParamsRequest($arrParamsGet = [])
    {
        $paramsReturn = [];
        $params = Yii::$app->request->isPost ? json_decode(file_get_contents('php://input'), true) : $_REQUEST;
        if (is_array($params)) {
            foreach ($arrParamsGet as $name => $objParams) {
                $type  = $objParams['type'];
                if (isset($params[$name])) {
                    $paramsReturn[$name] = $type == self::TYPE_INT ? (int)$params[$name] : $params[$name];
                    if ($type == self::TYPE_ARRAY && (empty($paramsReturn[$name]) || !is_array($paramsReturn[$name]))) {
                        $paramsReturn[$name] = [];
                    } else if ($type == self::TYPE_STRING) {
                        $paramsReturn[$name] = trim(strip_tags($params[$name]));
                    }
                } else {
                    $paramsReturn[$name] = $type == self::TYPE_INT ? ( isset($objParams['default']) ? $objParams['default'] : 0 ) : ($type == self::TYPE_ARRAY ? [] : ( isset($objParams['default']) ? $objParams['default'] : "" ));
                }
            }
        } else {
            foreach ($arrParamsGet as $name => $objParams) {
                $paramsReturn[$name] = isset($objParams['default']) ? $objParams['default'] : null;
            }
        }

        $listErrorParams    = [];
        foreach ($paramsReturn as $name => $value) {
            $typeOfValue    = $arrParamsGet[$name]['type'];
            $objParams      = isset($arrParamsGet[$name]['validate']) ? $arrParamsGet[$name]['validate'] : [];
            if( !empty($objParams) ){
                if(!is_array($objParams) )
                    $objParams = [$objParams];

                foreach($objParams as $typeValidate => $objValidate){
                    if( is_numeric($typeValidate) && !is_array($objValidate) )
                        $typeValidate = $objValidate;

                    switch((string)$typeValidate){
                        case Response::KEY_REQUIRED:
                            $isError    = false;
                            if( $typeOfValue == self::TYPE_STRING && (!$value || empty(trim($value)))  ){
                                $isError= true;
                            }
                            else if( $typeOfValue == self::TYPE_INT && (!$value || $value <= 0) ){
                                $isError= true;
                            }
                            else if( $typeOfValue == self::TYPE_ARRAY && empty($value) ){
                                $isError= true;
                            }
                            if( $isError && !isset($listErrorParams[$name]) )
                                $listErrorParams[$name] = Response::getErrorMessage($name, Response::KEY_REQUIRED);
                            break;
                        case Response::KEY_INVALID:
                            $isError    = false;
                            if( isset($objValidate['min']) || isset($objValidate['max']) ){
                                $value_length = strlen($value);
                                if( isset($objValidate['min']) && isset($objValidate['max']) ){
                                    if( $value_length < $objValidate['min'] || $value_length > $objValidate['max'] ){
                                        $isError = true;
                                    }
                                }
                                else if( isset($objValidate['min']) && $value_length < $objValidate['min']){
                                    $isError = true;
                                }
                                else if( isset($objValidate['max']) && $value_length > $objValidate['max']){
                                    $isError = true;
                                }
                            }
                            if( isset($objValidate['regex']) ){
                                $regex = $objValidate['regex'];
                                if(!preg_match($regex, $value)){
                                    $isError = true;
                                }
                            }
                            if( $isError && !isset($listErrorParams[$name]) )
                                $listErrorParams[$name] = Response::getErrorMessage($name, Response::KEY_INVALID);
                            break;
                        default:
                            break;
                    }
                }
            }

        }

        $paramsReturn['listError'] = $listErrorParams;
        return $paramsReturn;
    }

    public function _sendNotifyTelegram($message)
    {
        $urlApiSendMessageTele = 'https://api.telegram.org/bot' . $this->token_bot . '/sendMessage';
        $data = [
            'text' => $message,
            'chat_id' => $this->chat_id
        ];
        // file_get_contents($urlApiSendMessageTele . "?" . http_build_query($data) );
        return true;
    }
    
    private function _validateTokenPhoneLogin($idToken, $phoneCheck)
    {
        $url        = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . $this->fireBaseApiKey;
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
                    $phone      = preg_replace("/[^0-9]+/", "", $info['phoneNumber']);
                    $arrPhone   = str_split($phone);
                    if ($arrPhone[0] != '0') {
                        $phone  = '0' . substr($phone, 2);
                    }
                    if( $phone == $phoneCheck ){
                        return true;
                    }
                }            
            }
        }else{
            $Util = new \backend\models\Util;
            $params = [
                'phone' => $phoneCheck,
                'result'=> $result
            ];
            $Util->writeLog('validate-token-phone-err', json_encode($params));
        }
        return false;
    }

    private function getUserInfoRes($user){
        $resUser                = $user;
        $responseObj            = new \stdClass();
        $responseObj->id        = $resUser->id;
        $responseObj->fullname  = !empty($resUser->fullname) ? $resUser->fullname : '';
        $responseObj->phone     = !empty($resUser->phone) ? $resUser->phone : '';
        $responseObj->address   = !empty($resUser->address) ? $resUser->address : '';
        $responseObj->district  = !empty($resUser->district) ? $resUser->district : '';
        $responseObj->province  = !empty($resUser->province) ? $resUser->province : '';
        $responseObj->avatar    = $this->urlDomain . (!empty($resUser->avatar) ? $resUser->avatar : '/img/avatar.png');
        $responseObj->referral_code = $resUser->referral_code;
        $responseObj->wallet_point  = $resUser->wallet_point;
        $responseObj->is_verify_account = $resUser->is_verify_account;
        $responseObj->device_id = $resUser->device_id;
        $responseObj->app_version = $resUser->clientver;

        $responseObj->access_token      = $this->encryptTokenWithJWT(['id' => $resUser->id]);
        $responseObj->refresh_access_token     = $this->encryptTokenWithJWT(['id' => $resUser->id], 3600*24*45);

        return $responseObj;
    }

    /**
     * API đăng nhập bằng SĐT
     */
    public function actionLoginWithPhone(){
        try{
            $params = self::getParamsRequest([
                'phone' => [
                    'type' => self::TYPE_STRING,
                    'validate' => [Response::KEY_REQUIRED, Response::KEY_INVALID => ['min' => Response::PHONE_MIN_LENGTH, 'max' => Response::PHONE_MAX_LENGTH]]
                ],
                'token' => [
                    'type' => self::TYPE_STRING,
                    'validate' => Response::KEY_REQUIRED
                ],
                'did' => [
                    'type' => self::TYPE_STRING,
                    'validate' => Response::KEY_REQUIRED
                ],
                'clientver' => [
                    'type' => self::TYPE_STRING,
                    'validate' => Response::KEY_REQUIRED
                ]
            ]);
            if( !empty($params['listError']) ){
                return Response::returnResponse(Response::RESPONSE_CODE_ERR, [], $params['listError']);
            }

            $this->writeLogFile('login-with-phone', $params);

            $phone      = $params['phone'];
            $token      = $params['token'];
            $did        = $params['did'];
            $clientver  = $params['clientver'];
            if (strpos($phone, '+84') !== false) {
                $phone  = str_replace('+84', '0', $phone);
            }
            
            // $tokenValid = $this->_validateTokenPhoneLogin($token, $phone);
            // if( !$tokenValid ){
            //     return Response::returnResponse(Response::RESPONSE_CODE_ERR, [], [Response::getErrorMessage('token', Response::KEY_INVALID)]);
            // }

            $user       = Customer::findOne(['phone' => $phone]);
            
            //Khởi tạo tài khoản user trường hợp chưa có
            if (!$user) {
                $user           = new Customer;
                $user->phone    = $phone;
            }else{
                if( $user->status == Customer::STATUS_BANNED ){
                    return Response::returnResponse(Response::RESPONSE_CODE_ERR, [], [Response::getErrorMessage('banned', Response::KEY_FORBIDDEN)]);
                }
            }

            if( !$user->is_verify_account ){
                $user->is_verify_account = Customer::ACCOUNT_VERIFYED;
            }

            $user->device_id    = $did;
            $user->clientver    = $clientver;
            $user->last_login   = date('Y-m-d H:i:s');
            $user->save(false);

            if( !$user->referral_code	){
                $user->referral_code = $this->generateReferralCode($user->id);
                $user->save(false);
            }

            $userRes = $this->getUserInfoRes($user);
            return Response::returnResponse(Response::RESPONSE_CODE_SUCC, $userRes);
        }catch(\Exception $e){
            $this->writeLogFile('login-with-phone-error', [
                'message' => $e->getMessage(),
            ]);
            return Response::returnResponse(Response::RESPONSE_CODE_ERR, [], [Response::getErrorMessage('sys', Response::KEY_SYS_ERR)]);
        }
    }
    /**
     * API làm mới mã accessToken
     */
    public function actionRefreshAccessToken(){
        try{
            $params = self::getParamsRequest([
                'user_id'    => [
                    'type' => self::TYPE_INT,
                    'validate' => Response::KEY_REQUIRED
                ],
                'token' => [
                    'type' => self::TYPE_STRING,
                    'validate' => Response::KEY_REQUIRED
                ]
            ]);
            
            if( !empty($params['listError']) ){
                return Response::returnResponse(Response::RESPONSE_CODE_ERR, [], $params['listError']);
            }

            $user_id    = $params['user_id'];
            $token      = $params['token'];
            $dataToken  = $this->decryptTokenWithJWT($token);
            if( !$dataToken || $dataToken['id'] != $user_id ){
                $msg    = !$dataToken ? Response::getErrorMessage('token', Response::KEY_INVALID) : Response::getErrorMessage('info', Response::KEY_FORBIDDEN);
                return Response::returnResponse(Response::RESPONSE_CODE_ERR, [], [$msg]);
            }

            $responseObj                        = new \stdClass();
            $responseObj->access_token          = $this->encryptTokenWithJWT(['id' => $dataToken['id']]);
            $responseObj->refresh_access_token  = $this->encryptTokenWithJWT(['id' => $dataToken['id']], 3600*24*45);

            return Response::returnResponse(Response::RESPONSE_CODE_SUCC, $responseObj);
        }catch(\Exception $e){
            $this->writeLogFile('refresh-access-token-error', [
                'message' => $e->getMessage(),
            ]);
            return Response::returnResponse(Response::RESPONSE_CODE_ERR, [], [Response::getErrorMessage('sys', Response::KEY_SYS_ERR)]);
        }
    }
    /**
     * API Thông tin user
     */
    public function actionUserInfo(){
        try{
            
            if( !$this->userId ){
                $msg    = Response::getErrorMessage('info', Response::KEY_FORBIDDEN);
                return Response::returnResponse(Response::RESPONSE_CODE_ERR, [], [$msg]);
            }

            $user       = Customer::findOne($this->userId);
            if (!$user || $user->status == Customer::STATUS_BANNED) {
                $msg    = !$user ? Response::getErrorMessage('account', Response::KEY_NOT_FOUND) : Response::getErrorMessage('info', Response::KEY_FORBIDDEN);
            }
            
            $userRes = $this->getUserInfoRes($user);
            return Response::returnResponse(Response::RESPONSE_CODE_SUCC, $userRes);

        }catch(\Exception $e){
            $this->writeLogFile('user-info-error', [
                'message' => $e->getMessage(),
            ]);
            return Response::returnResponse(Response::RESPONSE_CODE_ERR, [], [Response::getErrorMessage('sys', Response::KEY_SYS_ERR)]);
        }
    }
    /**
     * API cập nhật thông tin user
     */
    public function actionUpdateInfo(){

        try {
            $params = self::getParamsRequest([
                'fullname' => [
                    'type' => self::TYPE_STRING,
                    'validate' => Response::KEY_REQUIRED
                ],
                'phone' => [
                    'type' => self::TYPE_STRING,
                    'validate' => [Response::KEY_REQUIRED, Response::KEY_INVALID => ['min' => Response::PHONE_MIN_LENGTH, 'max' => Response::PHONE_MAX_LENGTH]]
                ],
                'district' => [
                    'type' => self::TYPE_STRING,
                    'validate' => Response::KEY_REQUIRED
                ],
                'province' => [
                    'type' => self::TYPE_STRING,
                    'validate' => Response::KEY_REQUIRED
                ],
                'address' => [
                    'type' => self::TYPE_STRING,
                    'validate' => Response::KEY_REQUIRED
                ]
            ]);
            if( !empty($params['listError']) || !$this->userId ){
                $listErr = !empty($params['listError']) ? $params['listError'] : [Response::getErrorMessage('info', Response::KEY_FORBIDDEN)];
                return Response::returnResponse(Response::RESPONSE_CODE_ERR, [], $listErr);
            }

            $this->writeLogFile('update-info', $params);

            $fullname   = $params['fullname'];
            $phone      = $params['phone'];
            $district   = $params['district'];
            $province   = $params['province'];
            $address    = $params['address'];
            if (strpos($phone, '+84') !== false) {
                $phone  = str_replace('+84', '0', $phone);
            }
            $user       = Customer::findOne($this->userId);

            if( !$user || $user->status == Customer::STATUS_BANNED ){
                $msg    = !$user ? Response::getErrorMessage('account', Response::KEY_NOT_FOUND) : Response::getErrorMessage('banned', Response::KEY_FORBIDDEN);
                return Response::returnResponse(Response::RESPONSE_CODE_ERR, [], [$msg]);
            }

            //Check phone exist
            $resultCheckPhone = Customer::find()->where(['phone' => $phone])->andWhere(['<>', 'id', $user->id])->one();
            if ( $resultCheckPhone )
                return Response::returnResponse(Response::RESPONSE_CODE_ERR, [], [Response::getErrorMessage('phone', Response::KEY_EXISTS)]);

            if( $phone != $user->phone ){
                $user->is_verify_account = Customer::ACCOUNT_NOT_VERIFY;
            }

            $user->fullname = $fullname;
            $user->phone    = $phone;
            $user->district = $district;
            $user->province = $province;
            $user->address  = $address;
            $user->save(false);

            $userRes = $this->getUserInfoRes($user);
            return Response::returnResponse(Response::RESPONSE_CODE_SUCC, $userRes);

        } catch (\Exception $e) {
            $this->writeLogFile('update-info-error', [
                'message' => $e->getMessage(),
            ]);
            return Response::returnResponse(Response::RESPONSE_CODE_ERR, [], [Response::getErrorMessage('sys', Response::KEY_SYS_ERR)]);
        }
    }

    /**
     * API ví tích điểm
     */
    public function actionWalletPoint(){

        try {

            $params = self::getParamsRequest([
                'limit'    => [
                    'type' => self::TYPE_INT,
                    'default' => 10
                ],
                'page'    => [
                    'type' => self::TYPE_INT,
                    'default' => 1
                ]
            ]);
            
            if( !$this->userId ){
                $listErr = [Response::getErrorMessage('info', Response::KEY_FORBIDDEN)];
                return Response::returnResponse(Response::RESPONSE_CODE_ERR, [], $listErr);
            }

            $user       = Customer::findOne($this->userId);

            if( !$user || $user->status == Customer::STATUS_BANNED ){
                $msg    = !$user ? Response::getErrorMessage('account', Response::KEY_NOT_FOUND) : Response::getErrorMessage('banned', Response::KEY_FORBIDDEN);
                return Response::returnResponse(Response::RESPONSE_CODE_ERR, [], [$msg]);
            }

            $limit      = $params['limit'];
            $page       = $params['page'];
            $offset     = ($page - 1) * $limit;

            $listHistory= HistoryWalletPoint::find()->select(["id", "type", new Expression('date_format(create_at, "%H:%i %d/%m/%Y") as create_at'), "note", "point", "obj_id"])->where(['user_id' => $user->id])->limit($limit)->offset($offset)->orderBy(['id' => SORT_DESC])->asArray()->all();

            $dataRes = [
                'pointCurrent' => $user->wallet_point,
                'history'      => $listHistory
            ];

            return Response::returnResponse(Response::RESPONSE_CODE_SUCC, $dataRes);

        } catch (\Exception $e) {
            $this->writeLogFile('wallet-point-error', [
                'message' => $e->getMessage(),
            ]);
            return Response::returnResponse(Response::RESPONSE_CODE_ERR, [], [Response::getErrorMessage('sys', Response::KEY_SYS_ERR)]);
        }
    }

    /**
     * API Trang chủ
     */
    public function actionHome(){
        try {

            $params = self::getParamsRequest([
                'limit'    => [
                    'type' => self::TYPE_INT,
                    'default' => 4
                ],
                'page'    => [
                    'type' => self::TYPE_INT,
                    'default' => 1
                ]
            ]);
            
            $limit          = $params['limit'];
            $page           = $params['page'];
            $offset         = ($page - 1) * $limit;

            
            $listBanner     = Banner::getListBannerApp(Banner::BANNER_CUSTOMER, $this->urlDomain);
            $listCategory   = Category::getListCateApp(0, 8, 0, $this->urlDomain);

            $listproductSale= ProductSale::getProductSale(0, 0, 8);
            $listAdvertisementNews = Advertisement::getAdvertisementHome(null, 4, 0);
            $listAdvertisementBuy  = Advertisement::getAdvertisementHome(Advertisement::TYPE_BUY, 4, 0);
            $listAdvertisementSell = Advertisement::getAdvertisementHome(Advertisement::TYPE_SELL, 4, 0);

            $dataRes = [
                'banner'       => $listBanner,//Banner
                'category'     => $listCategory,//Chuyên mục
                'advertisement'=> [
                    'news'     => $listAdvertisementNews,
                    'buy'      => $listAdvertisementBuy,
                    'sell'     => $listAdvertisementSell,
                ],//Tin rao vặt
                'productSale'  => $listproductSale,//Săn sale cùng 1KHO
                'categoryProduct' => [],//List chuyên mục kèm sản phẩm nổi bật
            ];

            return Response::returnResponse(Response::RESPONSE_CODE_SUCC, $dataRes);

        } catch (\Exception $e) {
            $this->writeLogFile('home-error', [
                'message' => $e->getMessage(),
            ]);
            return Response::returnResponse(Response::RESPONSE_CODE_ERR, [], [Response::getErrorMessage('sys', Response::KEY_SYS_ERR)]);
        }
    }

    private function writeLogFile($name, $data){
        if( is_array($data) )
            $data = json_encode($data);
        $Util = new Util;
        $Util->writeLog($name, $data);
    }

    /**
     * Function mã hoá dữ liệu dùng JWT
     * $data: Dữ liệu muốn mã hoá (string|number|array)
     * $timeExpired: Thời gian hết hạn của token (Mặc định 30 ngày)
     * return token
     */
    private function encryptTokenWithJWT($data, $timeExpired = 3600*24*30)
    {
        $jwt    = new JWT(Yii::$app->params['secretKeyJWT'], $timeExpired);
        $dataBeforeEncryt = ['type' => self::TYPE_STRING, 'data' => $data];
        if( is_array($data) ){
            $dataBeforeEncryt['type'] = self::TYPE_ARRAY;
            $dataBeforeEncryt['data'] = json_encode($data);
        }
        return $jwt->encode($dataBeforeEncryt);
    }

    /**
     * Function giải mã dữ liệu dùng JWT
     * $token: Token JWT đã mã hoá
     * return data
     */
    private function decryptTokenWithJWT($token)
    {
        $jwt = new JWT(Yii::$app->params['secretKeyJWT']);
        try{
            $dataDecrypt = $jwt->decode($token);
            if( is_array($dataDecrypt) && isset($dataDecrypt['type']) ){
                if( $dataDecrypt['type'] == self::TYPE_ARRAY ){
                    return json_decode($dataDecrypt['data'], true);
                }
    
                return $dataDecrypt['data'];
            }
        }catch(\Exception $e){
            $this->writeLogFile('decrypt-token-error', [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'token'   => $token    
            ]);
        }
        
        return null;
    }

    protected function secondsTimeSpanToHMS($s)
    {
        $day = floor($s / (3600 * 24)); //Get whole hours
        $s -= $day * 3600 * 24;
        $h = floor($s / 3600); //Get whole hours
        $s -= $h * 3600;
        $m = floor($s / 60); //Get remaining minutes
        $s -= $m * 60;
        return [
            'day' => $day,
            'hour' => $h,
            'minutes' => $m,
            'seconds' => $s
        ];
    }

    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return strtoupper($randomString);
    }

    private function generateReferralCode($id, $maxLength = 6){
        $referral_code      = $id;
        $characters         = '0123456789';
        $charactersLength   = strlen($characters);
        $length             = $maxLength - strlen($id);
        for ($i = 0; $i < $length; $i++) {
            $referral_code .= $characters[rand(0, $charactersLength - 1)];
        }
        return $referral_code;
    }
}
