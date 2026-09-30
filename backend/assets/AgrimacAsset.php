<?php

namespace backend\assets;

use yii\web\AssetBundle;

class AgrimacAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'agrimac/css/agrimac.css',
    ];
    public $js = [
        'agrimac/js/agrimac.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
