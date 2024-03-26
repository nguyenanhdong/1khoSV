<?php

namespace frontend\assets;

use yii\web\AssetBundle;
use yii\web\View;
/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/sweetalert.css',
        'css/slick-theme.css',
        'css/slick.css',
        'js/toastr/toastr.min.css',
        'css/site.css',
        'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css',
    ];
    public $js = [
        '/js/jquery.min.js',
        '/js/bootstrap.min.js',
        '/js/sweetalert2.js',
        '/js/slick.min.js',
        'js/toastr/toastr.min.js',
        '/js/script.js',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD
    ];
    public $depends = [
        // 'yii\web\YiiAsset',
        // 'yii\bootstrap\BootstrapAsset',
    ];
}
