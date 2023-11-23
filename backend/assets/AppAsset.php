<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'css/notifications/toastr/toastr.css',
        '/assets/global/plugins/select2/select2.css',
        '/css/formplugins/bootstrap-daterangepicker/bootstrap-daterangepicker.css',
    ];
    public $js = [
        // 'js/mustache.js',
        '/assets/global/plugins/select2/select2.min.js',
        'js/notifications/toastr/toastr.js',
        '/js/dependency/moment/moment.js',
        '/js/formplugins/bootstrap-daterangepicker/bootstrap-daterangepicker.js',
        'assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.js',
        'js/main.js'
    ];
    public $depends = [
        // 'yii\web\YiiAsset',
        // 'yii\bootstrap\BootstrapAsset',
    ];
}
