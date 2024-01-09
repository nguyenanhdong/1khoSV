<?php
namespace common\helpers;

use Yii;
class Format
{
    public static function formatPrice($price){
        if( $price >= 1000 ){
            if( $price < 1000000 ){
                $price = $price / 1000 . 'k';
            }else{
                $prefix = 'tr';
                $price = $price / 1000000;
                if( strpos($price, '.') !== false ){
                    $price = str_replace('.', $prefix, $price);
                }else{
                    $price = $price . $prefix;
                }
            }
            return $price;
        }

        return $price;
    }

    public static function secondsTimeSpanToHMS($s)
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

    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return strtoupper($randomString);
    }

    public static function generateReferralCode($id, $maxLength = 6){
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
