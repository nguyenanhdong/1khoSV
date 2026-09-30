<?php

/* @var $this yii\web\View */
/* @var $orders array */
/* @var $products array */
/* @var $market array|null đơn hàng sàn 1kho (chỉ Admin) */

use yii\web\View;
use backend\assets\AgrimacAsset;

$this->registerJsVar('AM_ORDERS', $orders, View::POS_HEAD);
$this->registerJsVar('AM_ORDER_PRODUCTS', $products, View::POS_HEAD);
$this->registerJsFile('@web/agrimac/js/am-orders.js', ['depends' => AgrimacAsset::class]);
if ($market) {
    $this->registerJsVar('AM_MARKET', $market, View::POS_HEAD);
    $this->registerJsFile('@web/agrimac/js/am-market-orders.js', ['depends' => AgrimacAsset::class]);
}
$height = $market ? 'calc(100vh - 176px)' : 'calc(100vh - 130px)';
?>
<?php if ($market): ?>
<div class="am-tabs" id="am-order-scope">
    <button type="button" class="am-tab active" data-scope="dealer">📋 Đơn đại lý (<?= count($orders) ?>)</button>
    <button type="button" class="am-tab" data-scope="market">🛒 Đơn sàn 1kho (<span id="am-market-total"><?= array_sum($market['counts']) ?></span>)</button>
</div>
<?php endif; ?>

<div id="am-scope-dealer" style="display:flex;gap:14px;height:<?= $height ?>;overflow:hidden">
    <div style="flex:1;display:flex;flex-direction:column;min-width:0">
        <div id="am-order-filters" style="display:flex;gap:6px;margin-bottom:11px;flex-wrap:wrap;align-items:center"></div>
        <div class="am-card" style="overflow:auto;flex:1">
            <table class="am-table">
                <thead><tr>
                    <th></th><th>Mã đơn</th><th>Đại lý</th><th>Sản phẩm</th><th>SL</th><th>Doanh thu</th>
                    <th>Giá vốn LK</th><th>Lợi nhuận</th><th>Sale</th><th>Trạng thái</th>
                </tr></thead>
                <tbody id="am-order-rows"></tbody>
            </table>
        </div>
    </div>
    <div id="am-order-detail" class="am-panel am-hidden" style="width:340px;flex-shrink:0;display:flex;flex-direction:column;overflow:hidden"></div>
</div>

<?php if ($market): ?>
<div id="am-scope-market" style="display:none;gap:14px;height:<?= $height ?>;overflow:hidden">
    <div style="flex:1;display:flex;flex-direction:column;min-width:0">
        <div id="am-market-filters" style="display:flex;gap:6px;margin-bottom:8px;flex-wrap:wrap;align-items:center"></div>
        <div style="display:flex;gap:8px;margin-bottom:11px;align-items:center">
            <input id="am-market-search" class="am-input" style="max-width:340px" placeholder="🔍 Mã đơn, tên / SĐT khách, đại lý, sản phẩm..." autocomplete="off">
            <select id="am-market-payment" class="am-input" style="max-width:230px">
                <option value="">Mọi phương thức thanh toán</option>
                <?php foreach ($market['payments'] as $key => $label): ?>
                    <option value="<?= (int)$key ?>"><?= \yii\helpers\Html::encode($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="am-card" style="overflow:auto;flex:1">
            <table class="am-table" id="am-market-table">
                <thead><tr>
                    <th>Mã đơn</th><th>Khách hàng</th><th>Sản phẩm</th><th>SL</th><th>Tổng tiền</th>
                    <th class="am-mcol-opt">Thanh toán</th><th class="am-mcol-opt">Đại lý</th><th>Ngày tạo</th><th>Trạng thái</th>
                </tr></thead>
                <tbody id="am-market-rows"><tr><td colspan="9" class="am-empty" style="padding:30px">Đang tải...</td></tr></tbody>
            </table>
        </div>
        <div id="am-market-pager" style="margin-top:8px"></div>
    </div>
    <div id="am-market-detail" class="am-panel am-hidden" style="width:380px;flex-shrink:0;display:flex;flex-direction:column;overflow:hidden"></div>
</div>
<?php endif; ?>
