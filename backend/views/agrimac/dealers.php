<?php

/* @var $this yii\web\View */
/* @var $dealers array */
/* @var $provinces string[] */

use yii\web\View;
use backend\assets\AgrimacAsset;
use backend\components\AgrimacData as D;

$this->registerJsVar('AM_DEALER_PAGE', [
    'dealers'   => $dealers,
    'provinces' => $provinces,
    'levels'    => D::DEALER_LEVELS,
], View::POS_HEAD);
$this->registerJsFile('@web/agrimac/js/am-dealers.js', ['depends' => AgrimacAsset::class]);
?>
<div style="display:flex;gap:14px">
    <div style="flex:1">
        <div id="am-dealer-toolbar" class="am-toolbar-end"></div>
        <div class="am-card" style="overflow:hidden;margin-bottom:12px">
            <table class="am-table">
                <thead><tr>
                    <th>Mã</th><th>Tên đại lý</th><th>Tỉnh</th><th>SĐT</th><th>Cấp</th><th>Hạn mức CN</th>
                    <th>Dư nợ hiện tại</th><th>Tổng mua</th><th>Trạng thái</th><th></th>
                </tr></thead>
                <tbody id="am-dealer-rows"></tbody>
            </table>
        </div>
        <div id="am-dealer-warnings"></div>
    </div>
    <div id="am-dealer-detail" class="am-hidden" style="width:280px;background:#fff;border-radius:14px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.08);flex-shrink:0"></div>
</div>
