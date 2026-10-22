<?php

/* @var $this yii\web\View */
/* @var $orders array */
/* @var $dealers array */
/* @var $leads array */
/* @var $warranties array */
/* @var $claims array */
/* @var $products array sản phẩm dưới mức tồn tối thiểu (tối đa 8) */
/* @var $lowStockCount int */

use yii\helpers\Html;
use backend\components\AgrimacData as D;
use backend\components\AgrimacUi as Ui;

$flow = [
    ['💬', 'Tư vấn, tạo & duyệt đơn', 'Kinh doanh', '#ede9fe', '#8b5cf6'],
    ['📤', 'Xuất kho / xuất linh kiện', 'Kế toán công nợ', '#dbeafe', '#3b82f6'],
    ['🔩', 'Nhận LK → lắp → nhập kho', 'Bộ phận lắp ráp', '#e0f2fe', '#0ea5e9'],
    ['🚛', 'Gửi nhà xe → khách nhận', 'NV giao hàng', '#ffedd5', '#f97316'],
    ['💰', 'Công nợ & thu tiền', 'Kế toán công nợ', '#d1fae5', '#059669'],
];

$pipelineTotal = array_sum(array_column($leads, 'value'));
$countStatus = function ($status) use ($orders) {
    return count(array_filter($orders, function ($o) use ($status) {
        return $o['status'] === $status;
    }));
};
$revenue = array_sum(array_map(function ($o) {
    return $o['type'] === 'new' && $o['status'] === 'delivered' ? $o['total'] : 0;
}, $orders));
$countWhere = function (callable $fn) use ($orders) {
    return count(array_filter($orders, $fn));
};
$waitWarehouse = $countWhere(function ($o) { return $o['status'] === 'confirmed'; });
$waitShipping = $countWhere(function ($o) { return $o['type'] !== 'assembly' && in_array($o['status'], ['exported', 'shipping'], true); });
$inAssembly = $countWhere(function ($o) { return $o['type'] === 'assembly' && in_array($o['status'], ['exported', 'received', 'assembling', 'assembled'], true); });
$inProgress = count(array_filter($orders, function ($o) {
    return !in_array($o['status'], ['delivered', 'cancelled'], true);
}));
$overLimit = array_values(array_filter($dealers, function ($d) {
    return $d['debt'] > $d['limit'];
}));
$openClaims = count(array_filter($claims, function ($c) {
    return $c['status'] !== 'resolved';
}));
$countWarranty = function ($status) use ($warranties) {
    return count(array_filter($warranties, function ($w) use ($status) {
        return $w['status'] === $status;
    }));
};
?>
<div>
    <div class="am-card-lg" style="padding:14px 18px;margin-bottom:16px">
        <div style="font-weight:700;font-size:12px;color:#374151;margin-bottom:11px">📌 Quy trình vận hành phòng ban</div>
        <div class="am-flow">
            <?php foreach ($flow as $i => [$icon, $label, $dept, $bg, $border]): ?>
                <div style="display:flex;align-items:center;flex-shrink:0">
                    <div class="am-flow-step" style="background:<?= $bg ?>;border-color:<?= $border ?>">
                        <div style="font-size:18px"><?= $icon ?></div>
                        <div style="font-size:11px;font-weight:700;color:#374151;margin-top:3px;line-height:1.3"><?= Html::encode($label) ?></div>
                        <?php if ($dept): ?><div style="font-size:9px;color:#9ca3af;margin-top:1px"><?= Html::encode($dept) ?></div><?php endif; ?>
                    </div>
                    <?php if ($i < count($flow) - 1): ?><div class="am-flow-arrow">→</div><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:11px;margin-bottom:16px">
        <?= Ui::stat('💵', 'Doanh thu', D::money($revenue), $countWhere(function ($o) { return $o['type'] === 'new' && $o['status'] === 'delivered'; }) . ' đơn bán hoàn thành', '#10b981') ?>
        <?= Ui::stat('📋', 'Đơn đang xử lý', (string)$inProgress,
            $countStatus('pending') . ' chờ duyệt · ' . $waitWarehouse . ' chờ kho · ' . $inAssembly . ' lắp ráp · ' . $waitShipping . ' chờ giao/đang chở', '#f59e0b') ?>
        <?= Ui::stat('💬', 'Pipeline CRM', count($leads) . ' lead', 'Giá trị: ' . D::money($pipelineTotal), '#8b5cf6') ?>
        <?= Ui::stat('🛡', 'Khiếu nại BH', (string)$openClaims, 'Đang xử lý', '#ef4444') ?>
        <?= Ui::stat('⚠', 'CN quá hạn mức', count($overLimit) . ' ĐL',
            $overLimit ? D::shortDealerName($overLimit[0]['name']) . ': ' . round($overLimit[0]['debt'] / $overLimit[0]['limit'] * 100) . '% hạn mức' : 'Không có', '#f97316') ?>
    </div>

    <div style="display:grid;grid-template-columns:1.2fr 1fr 1fr;gap:14px">
        <div class="am-card" style="padding:16px">
            <div class="am-section-title">📋 Đơn hàng gần đây</div>
            <?php foreach ($orders as $o): ?>
                <div class="am-list-row">
                    <div>
                        <div style="font-weight:700;font-size:12px"><?= Html::encode($o['id']) ?></div>
                        <div style="font-size:11px;color:#6b7280"><?= Html::encode(D::ORDER_TYPES[$o['type']]['short'] . ' · ' . ($o['customerType'] === 'dealer' ? D::shortDealerName($o['customer']) : $o['customer'])) ?></div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:12px;font-weight:700;color:#059669"><?= D::money($o['total']) ?></div>
                        <?= Ui::orderStatus($o['status'], $o['type']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="am-card" style="padding:16px">
            <div class="am-section-title">💬 CRM Pipeline</div>
            <?php foreach (D::LEAD_STAGES as $stage => $st):
                $inStage = array_filter($leads, function ($l) use ($stage) {
                    return $l['stage'] === $stage;
                }); ?>
                <div class="am-list-row">
                    <?= Ui::badge($stage, $st['color'], $st['bg']) ?>
                    <div style="text-align:right">
                        <span style="font-weight:700;font-size:13px"><?= count($inStage) ?></span>
                        <span style="font-size:11px;color:#9ca3af;margin-left:6px"><?= D::money(array_sum(array_column($inStage, 'value'))) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
            <div style="margin-top:10px;font-size:12px;color:#6b7280">Tổng pipeline: <b style="color:#059669"><?= D::money($pipelineTotal) ?></b></div>
        </div>

        <div style="display:flex;flex-direction:column;gap:12px">
            <div class="am-card" style="padding:14px">
                <div class="am-section-title">🛡 Bảo hành</div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;text-align:center">
                    <?php foreach ([
                        [$countWarranty('active'), 'Kích hoạt', '#d1fae5', '#059669'],
                        [$countWarranty('unactivated'), 'Chưa KH', '#fef3c7', '#b45309'],
                        [$openClaims, 'Khiếu nại', '#fee2e2', '#ef4444'],
                    ] as [$val, $label, $bg, $color]): ?>
                        <div style="background:<?= $bg ?>;border-radius:8px;padding:9px 4px">
                            <div style="font-weight:900;font-size:18px;color:<?= $color ?>"><?= $val ?></div>
                            <div style="font-size:10px;color:#6b7280"><?= $label ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="am-card" style="padding:14px">
                <div class="am-section-title">🏗 Sắp hết hàng <span style="font-size:11px;font-weight:600;color:#9ca3af">(<?= number_format($lowStockCount, 0, ',', '.') ?> sản phẩm dưới mức tối thiểu)</span></div>
                <?php foreach ($products as $p): ?>
                    <div style="display:flex;justify-content:space-between;gap:8px;padding:5px 0;border-bottom:1px solid #f3f4f6;font-size:12px">
                        <span style="color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= Html::encode($p['name']) ?></span>
                        <span style="font-weight:700;white-space:nowrap;color:<?= $p['stock'] == 0 ? '#ef4444' : '#f59e0b' ?>"><?= $p['stock'] ?>/<?= $p['minStock'] ?></span>
                    </div>
                <?php endforeach; ?>
                <?php if (!$products): ?><div style="font-size:12px;color:#9ca3af">Tất cả sản phẩm đều đủ hàng ✓</div><?php endif; ?>
            </div>
        </div>
    </div>
</div>
