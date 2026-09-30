<?php

/* @var $this yii\web\View */
/* @var $leads array */

use yii\web\View;
use backend\assets\AgrimacAsset;

$this->registerJsVar('AM_LEADS', $leads, View::POS_HEAD);
$this->registerJsFile('@web/agrimac/js/am-crm.js', ['depends' => AgrimacAsset::class]);
?>
<div id="am-crm" style="display:flex;gap:14px;height:calc(100vh - 140px)">
    <div id="am-crm-board" style="flex:1;display:flex;gap:11px;overflow-x:auto"></div>
    <div id="am-crm-detail" class="am-hidden" style="width:290px;background:#fff;border-radius:14px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.08);overflow-y:auto;flex-shrink:0"></div>
</div>
