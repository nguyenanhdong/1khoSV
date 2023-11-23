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
use yii\helpers\ArrayHelper;
use backend\models\Util;

class ApiController extends Controller
{

    public $urlDomain = 'http://1khosv.com';
    public $urlDomainAvatarStaff = 'https://api.1khosv.com';
    public $linkShare = "https://1khosv.com/";
    public $chat_id = -581513801;
    public $token_bot = '1676133200:AAHU68FSplWBDqQ2p0KlBY28VO2l-6AoFCQ';
    public $projectIdFireBase = 'fastjob-74057';
    public $key_token_staff = 'AAHU68FSplWBDqQ2p0KlBY28VO2l1123AAHU68FSplWBDqQ2p0KlBY28VO2l';
   
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
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $msgError = "";
        if ($request->isPost) {
            $headers = apache_request_headers();
            $headers = array_change_key_case($headers, CASE_LOWER);
            $Util = new Util;
            // $Util->writeLog('header-request',json_encode(getallheaders()));
            if (isset($headers['authorization']) && !empty($headers['authorization'])) {
                $msgError = 'Authorization invalid';
                $authorization = $headers['authorization'];
                $authorization = str_replace('Bearer ', '', $authorization);

                $data_token = $this->encryptDecrypt($authorization, 'decrypt');

                if (strpos($data_token, '#') !== false) {
                    $data_token = explode('#', $data_token);
                    if (count($data_token) > 1) {
                        $msgError = '';

                        $this->userId = $data_token[1];

                    }
                }
            }
        } else {
            $msgError = "Method not allowed";
        }
        if ($msgError) {
            echo json_encode([
                'resultCode' => 0,
                "resultMessage" => $msgError
            ]);
            exit();
        }
        return parent::beforeAction($action);
    }

    public static function getParamsRequest($arrParamsGet = [])
    {
        $paramsReturn = [];
        $params = Yii::$app->request->isPost ? json_decode(file_get_contents('php://input'), true) : $_REQUEST;
        if (is_array($params)) {
            foreach ($arrParamsGet as $name => $type) {
                if (isset($params[$name])) {
                    $paramsReturn[$name] = $type == 'int' ? (int)$params[$name] : $params[$name];
                    if ($type == 'array' && (empty($paramsReturn[$name]) || !is_array($paramsReturn[$name]))) {
                        $paramsReturn[$name] = [];
                    } else if ($type == 'string') {
                        $paramsReturn[$name] = trim(strip_tags($params[$name]));
                    }
                } else {
                    $paramsReturn[$name] = $type == 'int' ? 0 : ($type == 'array' ? [] : "");
                }
            }
        } else {
            foreach ($arrParamsGet as $name => $type) {
                $paramsReturn[$name] = null;
            }
        }
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
    
    private function encryptDecrypt($string, $action = 'encrypt')
    {
        $encrypt_method = "AES-128-CBC";
        $secret_key = 'J9SxUpfWDDmWkfAzLCWH9k78fYPQDsLBpXfRswWQ2XmQAyaHgbSFtDq26Lt7qczpSa5wzs2rtjP2sGEWJhaV45B'; // user define private key
        $key = hash('sha256', $secret_key);
        $ivlen = openssl_cipher_iv_length($encrypt_method);
        if ($action == 'encrypt') {
            $iv = openssl_random_pseudo_bytes($ivlen);
            $ciphertext_raw = openssl_encrypt($string, $encrypt_method, $key, $options = OPENSSL_RAW_DATA, $iv);
            $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
            $output = base64_encode($iv . $hmac . $ciphertext_raw);
        } else if ($action == 'decrypt') {
            $c = base64_decode($string);
            $iv = substr($c, 0, $ivlen);
            $hmac = substr($c, $ivlen, $sha2len = 32);
            $ciphertext_raw = substr($c, $ivlen + $sha2len);
            $output = openssl_decrypt($ciphertext_raw, $encrypt_method, $key, $options = OPENSSL_RAW_DATA, $iv);
            $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
            if (!hash_equals($hmac, $calcmac)) {
                $output = "";
            }
        }
        return $output;
    }

    private function encrypt($string)
    {
        $key = $this->key_token_staff;
        return base64_encode(openssl_encrypt($string, "AES-128-ECB", $key));
    }

    private function decrypt($string_encrypt)
    {
        $key = $this->key_token_staff;
        return openssl_decrypt(base64_decode($string_encrypt), "AES-128-ECB", $key);
    }

    private function checkAuth($token, $uid)
    {
        if ($token) {
            $data_token = $this->decrypt($token);
            if (strpos($data_token, '#') !== false) {
                $data_token = explode('#', $data_token);
                if (count($data_token) > 0) {
                    $uid = $data_token[0];
                    if ($uid == $uid) {
                        return true;
                    }
                }
            }
        }
        return false;
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
