<?php

/* @var $this yii\web\View */
/* @var $orders array */

use yii\web\View;
use backend\assets\AgrimacAsset;

$this->registerJsVar('AM_ASM_ORDERS', $orders, View::POS_HEAD);
$this->registerJsFile('@web/agrimac/js/am-assembly.js', ['depends' => AgrimacAsset::class]);
?>
<div style="display:flex;gap:14px;height:calc(100vh - 130px)">
    <div style="width:300px;flex-shrink:0;display:flex;flex-direction:column;gap:10px">
        <div id="am-asm-summary" class="am-dark-gradient" style="border-radius:12px;padding:14px 16px;color:#fff"></div>
        <div style="display:flex;gap:6px">
            <button class="am-asm-filter active" data-filter="all">Tất cả</button>
            <button class="am-asm-filter" data-filter="assembling">Đang lắp</button>
            <button class="am-asm-filter" data-filter="assembled">Xong</button>
        </div>
        <div id="am-asm-list" style="flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:8px"></div>
    </div>
    <div id="am-asm-detail" style="flex:1;display:flex"></div>
</div>
