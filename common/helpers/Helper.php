<?php
namespace common\helpers;

use Yii;
class Helper
{
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
