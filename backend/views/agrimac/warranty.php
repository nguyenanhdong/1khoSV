<?php

/* @var $this yii\web\View */
/* @var $warranties array */
/* @var $claims array */

use yii\web\View;
use backend\assets\AgrimacAsset;

$this->registerJsVar('AM_WARRANTY_PAGE', ['warranties' => $warranties, 'claims' => $claims], View::POS_HEAD);
$this->registerJsFile('@web/agrimac/js/am-warranty.js', ['depends' => AgrimacAsset::class]);
?>
<div>
    <div class="am-tabs" data-group="warranty">
        <button class="am-tab active" data-tab="activations">🛡 Kích hoạt bảo hành</button>
        <button class="am-tab" data-tab="claims">⚠ Phiếu khiếu nại</button>
    </div>

    <div data-tab-group="warranty" data-tab-pane="activations">
        <div style="background:#e0f2fe;border-radius:10px;padding:10px 16px;margin-bottom:14px;border:1px solid #7dd3fc;font-size:12px;color:#0369a1">
            📱 <b>Kích hoạt từ app khách hàng:</b> Khách quét QR dán trên máy → điền thông tin → hệ thống tự cập nhật trạng thái bên dưới.
            Khách gọi hotline thì bấm <b>Kích hoạt</b> để nhập hộ.
        </div>
        <div class="am-card" style="overflow:hidden">
            <table class="am-table">
                <thead><tr>
                    <th>Serial</th><th>Sản phẩm</th><th>Đại lý</th><th>Khách hàng</th><th>SĐT</th>
                    <th>Ngày kích hoạt</th><th>Hết hạn</th><th>Khiếu nại</th><th>Trạng thái</th><th></th>
                </tr></thead>
                <tbody id="am-warranty-rows"></tbody>
            </table>
        </div>
    </div>

    <div data-tab-group="warranty" data-tab-pane="claims" class="am-hidden">
        <div class="am-toolbar-end"><button class="am-btn" data-action="new-claim">+ Tạo phiếu khiếu nại</button></div>
        <div class="am-card" style="overflow:hidden">
            <table class="am-table">
                <thead><tr>
                    <th>Mã KC</th><th>Khách hàng</th><th>Serial</th><th>Sản phẩm</th><th>Vấn đề</th>
                    <th>Ngày tạo</th><th>Phân công</th><th>Trạng thái</th><th>Ghi chú</th><th></th>
                </tr></thead>
                <tbody id="am-claim-rows"></tbody>
            </table>
        </div>
    </div>
</div>
