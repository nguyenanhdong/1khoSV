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
}
