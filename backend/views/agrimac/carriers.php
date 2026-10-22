<?php

/* @var $this yii\web\View */
/* @var $carriers array nhà xe */

use yii\web\View;
use backend\assets\AgrimacAsset;

$this->registerJsVar('AM_CARRIER_LIST', $carriers, View::POS_HEAD);
$this->registerJsFile('@web/agrimac/js/am-carriers.js', ['depends' => AgrimacAsset::class]);
?>
<div>
    <div style="display:flex;gap:10px;align-items:center;margin-bottom:14px">
        <input id="am-carrier-search" class="am-input" placeholder="🔍 Tìm theo tên, SĐT, tuyến..." style="max-width:320px">
        <span id="am-carrier-count" style="font-size:12px;color:#6b7280"></span>
        <button class="am-btn" data-action="new-carrier" style="margin-left:auto">+ Thêm nhà xe</button>
    </div>
    <div class="am-card" style="overflow:auto">
        <table class="am-table">
            <thead><tr><th>Nhà xe</th><th>Số điện thoại</th><th>Địa chỉ bến / tuyến</th><th>Ghi chú</th><th style="text-align:center">Đơn đã gửi</th><th>Gửi gần nhất</th><th></th></tr></thead>
            <tbody id="am-carrier-rows"></tbody>
        </table>
    </div>
</div>
