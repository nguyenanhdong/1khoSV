<?php

namespace backend\models;
use Yii;
date_default_timezone_set("Asia/Bangkok");
class Util extends \yii\base\Model
{
	public static $arrAppId = [
		'app'		=> '85531368-d793-4920-affe-9bc6abbffc6b',
		'staff'		=> '278afd09-7aa5-4877-8fa0-0ce8fbb0be04',
	];
	public static $arrApiKey = [
		'app'		=> 'NjEzMzQzM2MtNDBmOS00MGU5LWEwYjMtOTI5OGNhYWJhODhl',
		'staff'		=> 'NTFhZDc3YTQtNGE5NS00OWJiLTg5N2YtNzE3MGFkNjIzMDMz',
	];
	public static $included_segmentsArr = [
		'app'		=> ['Subscribed Users'],//['yyy','yyy2'],//
	];
	public static function sendPostoOneSignnal($fields, $api_key='OGFkYjUzMDMtNTMxOC00MjIxLWFiYmYtYmZmMjkyOGEwN2Fj'){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                                                   'Authorization: Basic ' . $api_key));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

	public static function sendOneSignal($custid,$title,$content,$app_id = 'app',$evt='no_event',$data = []){
		if(empty($data)){
			$data = ['evt'=>$evt];
		}
		else
			$data['evt'] = $evt;

		$arrAppId 	= self::$arrAppId;
		$arrApiKey 	= self::$arrApiKey;

		$fields = array(
            'app_id' => $arrAppId[$app_id],
            'contents' =>  array(
                "en" =>  $content
            ),

            'tags'=> array(
                array(
                    'key' => 'user_id',
                    'relation' =>'=',
                    'value'=> $custid
                )
            ),
            'headings'=> array(
                'en' => $title
            ),
            "data"  => $data
            // 'url'=>$url,
            // 'send_after'=>$obj['notischetime']." GMT+0700"
        );

        return self::sendPostoOneSignnal(json_encode($fields), $arrApiKey[$app_id]);
	}

	public static function customSendOneSignal($args){
		$arrAppId 	= self::$arrAppId;
		$arrApiKey 	= self::$arrApiKey;
		$included_segmentsArr = self::$included_segmentsArr;
		$app_id 	= isset($args['app_id']) ? $args['app_id'] : 'app';
		$fields 	= array(
            'app_id' => $arrAppId[$app_id],
            'contents' =>  array(
                "en" => $args['message']
            ),
            'headings'=> array(
                'en' => $args['title']
			),
			'included_segments' => $included_segmentsArr['app']
		);
		
        return self::sendPostoOneSignnal(json_encode($fields), $arrApiKey[$app_id]);
	}

	public function httpPostGCM($url, $data_string)
	{
		$ch = curl_init($url);
		$data = json_decode($data_string);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		// curl_setopt($ch, CURLOPT_CAINFO, "cacert.pem");
		//curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		  'Content-Type: application/json',
		  'Authorization: key=AIzaSyBwp4zEWL2-H_CZoTuobIZBuW3_d6kGneE',
		  'Content-Length: ' . strlen($data_string))
		);
		$result = curl_exec($ch);

		$err = "";
		if (curl_errno($ch)) {
		   print curl_error($ch);
		   $err = curl_error($ch);
		}

		curl_close($ch);
		// $obj = array(
		// 	"result" => $result,
		// 	"err" => $err
		// );
		$obj = json_decode($result);
		return $obj;
	}



	public function writeLog($typeLog,$stringlog){
		try{
			$dir =  __DIR__;
			$path = "logs/".$typeLog;
			if (!file_exists($path)) {
				mkdir($path, 0777, true);
			}
			$timeWrite = date("Y_m_d");
			$fileName = $path."/log.".$timeWrite.".log";
			$fh = fopen($fileName, 'a+') or die("Can't create file");
			fwrite($fh,date('Y-m-d H:i:s',time()).": ". $stringlog."\n");
			fclose($fh);
		}catch(Exception $e){

		}
	}


}
