<?php
namespace common\helpers;

use Yii;
class Request
{
    public static function getRequest($url){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYPEER => false
        ));

        $resp = curl_exec($curl);

        curl_close($curl);
        return $resp;
    }
}
