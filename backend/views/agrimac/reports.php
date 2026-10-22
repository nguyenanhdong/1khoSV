<?php

/* @var $this yii\web\View */
/* @var $report array dữ liệu AgrimacRepo::reports() */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use backend\assets\AgrimacAsset;
use backend\components\AgrimacData as D;

$this->registerJsVar('AM_REPORT', ['trend' => $report['trend'], 'byMonth' => $report['trendByMonth'],
    'channels' => ['dealer' => $report['kpi']['current']['dealer'], 'retail' => $report['kpi']['current']['retail'], 'market' => $report['kpi']['current']['market']]], View::POS_HEAD);
$this->registerJsFile('@web/agrimac/js/am-reports.js', ['depends' => AgrimacAsset::class]);

$k = $report['kpi'];
$cur = $k['current'];
$prev = $k['previous'];
$m = function ($v) { return Html::encode(D::money($v)); };
$delta = function ($now, $before) {
    if (!$before) {
        return $now ? '<span class="am-rp-delta up">mới phát sinh</span>' : '<span class="am-rp-delta">—</span>';
    }
    $pct = round(($now - $before) / abs($before) * 100);
    return '<span class="am-rp-delta ' . ($pct >= 0 ? 'up' : 'down') . '">' . ($pct >= 0 ? '▲ ' : '▼ ') . abs($pct) . '% so với kỳ trước</span>';
};
$hours = function ($h) {
    if ($h === null) {
        return '—';
    }
    return $h >= 48 ? round($h / 24, 1) . ' ngày' : $h . ' giờ';
};
$minutes = function ($mins) {
    if ($mins === null) {
        return '—';
    }
    return $mins >= 60 ? floor($mins / 60) . ' giờ ' . ($mins % 60) . ' phút' : $mins . ' phút';
};
$bar = function ($value, $max) {
    $w = $max > 0 ? max(2, round($value / $max * 100)) : 0;
    return '<div class="am-rp-bar"><span style="width:' . $w . '%"></span></div>';
};
$presets = [
    'Tháng này'   => [date('Y-m-01'), date('Y-m-d')],
    'Tháng trước' => [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month'))],
    'Quý này'     => [date('Y-') . str_pad((string)(floor((date('n') - 1) / 3) * 3 + 1), 2, '0', STR_PAD_LEFT) . '-01', date('Y-m-d')],
    'Năm nay'     => [date('Y-01-01'), date('Y-m-d')],
    '12 tháng'    => [date('Y-m-d', strtotime('-365 days')), date('Y-m-d')],
];
$flowLabels = D::ORDER_FLOWS;
?>
<div class="am-rp">
    <form class="am-rp-filter" method="get" action="<?= Url::to(['/agrimac/reports']) ?>">
        <div class="am-rp-presets">
            <?php $activePreset = null; foreach ($presets as $label => [$f, $t]) { if ($activePreset === null && $report['from'] === $f && $report['to'] === $t) $activePreset = $label; } ?>
            <?php foreach ($presets as $label => [$f, $t]): ?>
                <a class="am-filter <?= $label === $activePreset ? 'active' : '' ?>" href="<?= Url::to(['/agrimac/reports', 'from' => $f, 'to' => $t]) ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
        <label>Từ <input type="date" name="from" class="am-input" value="<?= Html::encode($report['from']) ?>"></label>
        <label>Đến <input type="date" name="to" class="am-input" value="<?= Html::encode($report['to']) ?>"></label>
        <button class="am-btn" type="submit">Xem báo cáo</button>
        <span class="am-rp-period">Kỳ so sánh: <?= date('d/m/Y', strtotime($report['prevFrom'])) ?> – <?= date('d/m/Y', strtotime($report['prevTo'])) ?></span>
    </form>

    <!-- 1. KPI tổng quan -->
    <div class="am-rp-kpis">
        <div class="am-rp-kpi"><div class="l">Doanh thu đơn bán</div><div class="v"><?= $m($cur['revenue']) ?></div><?= $delta($cur['revenue'], $prev['revenue']) ?></div>
        <div class="am-rp-kpi"><div class="l">Lợi nhuận gộp</div><div class="v"><?= $m($cur['profit']) ?></div><?= $delta($cur['profit'], $prev['profit']) ?></div>
        <div class="am-rp-kpi"><div class="l">Biên lợi nhuận</div><div class="v"><?= $k['margin'] !== null ? number_format($k['margin'], 1, ',', '.') . '%' : '—' ?></div><span class="am-rp-delta">doanh thu − giá vốn</span></div>
        <div class="am-rp-kpi"><div class="l">Đơn hoàn thành</div><div class="v"><?= number_format($cur['orders']) ?></div><?= $delta($cur['orders'], $prev['orders']) ?></div>
        <div class="am-rp-kpi"><div class="l">Giá trị đơn TB</div><div class="v"><?= $m($k['avgOrder']) ?></div><span class="am-rp-delta"><?= number_format($k['newOrders']) ?> đơn mới tạo · <?= $k['cancelled'] ?> huỷ</span></div>
        <div class="am-rp-kpi"><div class="l">Doanh thu sàn 1kho</div><div class="v"><?= $m($cur['market']) ?></div><?= $delta($cur['market'], $prev['market']) ?></div>
    </div>

    <div class="am-rp-grid2">
        <div class="am-card am-rp-card">
            <div class="am-rp-title">📈 Doanh thu & lợi nhuận theo <?= $report['trendByMonth'] ? 'tháng' : 'ngày' ?></div>
            <div id="am-rp-trend" class="am-rp-chart"></div>
        </div>
        <div class="am-card am-rp-card">
            <div class="am-rp-title">🧭 Doanh thu theo kênh</div>
            <div id="am-rp-channels"></div>
            <div class="am-rp-sub" style="margin-top:12px">Công nợ đại lý</div>
            <table class="am-rp-table">
                <tr><td>Tổng dư nợ hiện tại</td><td class="r b"><?= $m($report['debt']['total']) ?></td></tr>
                <tr><td>Nợ phát sinh trong kỳ</td><td class="r"><?= $m($report['debt']['added']) ?></td></tr>
                <tr><td>Đã thu trong kỳ</td><td class="r" style="color:#059669"><?= $m($report['debt']['collected']) ?></td></tr>
                <tr><td>Đại lý đang nợ / vượt hạn mức</td><td class="r"><?= $report['debt']['debtors'] ?> / <b style="color:<?= $report['debt']['over'] ? '#ef4444' : 'inherit' ?>"><?= $report['debt']['over'] ?></b></td></tr>
            </table>
            <?php foreach ($report['debt']['overDealers'] as $d): ?>
                <div class="am-rp-warn">⚠ <?= Html::encode($d['name']) ?>: nợ <?= $m($d['debt']) ?> / hạn mức <?= $m($d['limit']) ?></div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 2. Hiệu suất Kinh doanh -->
    <div class="am-card am-rp-card">
        <div class="am-rp-title">👔 Hiệu suất Kinh doanh (Sales)</div>
        <?php $maxRev = max(array_merge([0], array_column($report['salesPeople'], 'revenue'))); ?>
        <table class="am-rp-table">
            <thead><tr><th>Nhân viên</th><th class="r">Đơn hoàn thành</th><th>Doanh thu</th><th class="r">Lợi nhuận</th><th class="r">Lead mới</th><th class="r">Tỉ lệ chốt</th><th class="r">Hoa hồng</th></tr></thead>
            <?php foreach ($report['salesPeople'] as $s): ?>
                <tr><td><b><?= Html::encode($s['name']) ?></b></td><td class="r"><?= $s['orders'] ?></td>
                    <td><div class="am-rp-barcell"><?= $bar($s['revenue'], $maxRev) ?><span><?= $m($s['revenue']) ?></span></div></td>
                    <td class="r"><?= $m($s['profit']) ?></td><td class="r"><?= $s['leads'] ?></td>
                    <td class="r"><?= $s['winRate'] !== null ? $s['winRate'] . '%' : '—' ?></td><td class="r"><?= $m($s['commission']) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$report['salesPeople']): ?><tr><td colspan="7" class="am-empty">Chưa có nhân viên Kinh doanh</td></tr><?php endif; ?>
        </table>
        <div class="am-rp-sub" style="margin-top:10px">CRM trong kỳ: <?= $report['crm']['leads'] ?> lead mới · <?= $report['crm']['won'] ?> chốt · <?= $report['crm']['lost'] ?> thất bại
            · tỉ lệ chốt <?= $report['crm']['winRate'] !== null ? $report['crm']['winRate'] . '%' : '—' ?> · giá trị pipeline đang mở <?= $m($report['crm']['pipelineValue']) ?></div>
    </div>

    <div class="am-rp-grid2">
        <div class="am-card am-rp-card">
            <div class="am-rp-title">🚜 Top sản phẩm bán chạy</div>
            <?php $maxP = max(array_merge([0], array_column($report['topProducts'], 'revenue'))); ?>
            <table class="am-rp-table">
                <thead><tr><th>Sản phẩm</th><th class="r">SL</th><th>Doanh thu</th><th class="r">Lợi nhuận</th></tr></thead>
                <?php foreach ($report['topProducts'] as $p): ?>
                    <tr><td><?= Html::encode($p['name']) ?><div class="am-mini-sub"><?= Html::encode($p['code']) ?></div></td><td class="r"><?= $p['qty'] ?></td>
                        <td><div class="am-rp-barcell"><?= $bar($p['revenue'], $maxP) ?><span><?= $m($p['revenue']) ?></span></div></td><td class="r"><?= $m($p['profit']) ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$report['topProducts']): ?><tr><td colspan="4" class="am-empty">Chưa có đơn hoàn thành trong kỳ</td></tr><?php endif; ?>
            </table>
        </div>
        <div class="am-card am-rp-card">
            <div class="am-rp-title">🏪 Top đại lý theo doanh số</div>
            <?php $maxD = max(array_merge([0], array_column($report['topDealers'], 'revenue'))); ?>
            <table class="am-rp-table">
                <thead><tr><th>Đại lý</th><th class="r">Đơn</th><th>Doanh số</th><th class="r">Dư nợ / hạn mức</th></tr></thead>
                <?php foreach ($report['topDealers'] as $d): ?>
                    <tr><td><?= Html::encode($d['name']) ?></td><td class="r"><?= $d['orders'] ?></td>
                        <td><div class="am-rp-barcell"><?= $bar($d['revenue'], $maxD) ?><span><?= $m($d['revenue']) ?></span></div></td>
                        <td class="r" style="color:<?= $d['debt'] > $d['limit'] ? '#ef4444' : 'inherit' ?>"><?= $m($d['debt']) ?> / <?= $m($d['limit']) ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$report['topDealers']): ?><tr><td colspan="4" class="am-empty">Chưa có đơn đại lý hoàn thành trong kỳ</td></tr><?php endif; ?>
            </table>
        </div>
    </div>

    <!-- 3. Vận hành -->
    <div class="am-rp-grid2">
        <div class="am-card am-rp-card">
            <div class="am-rp-title">⚙️ Đơn đang xử lý theo bước</div>
            <?php foreach (D::ORDER_TYPES as $type => $t): ?>
                <div class="am-rp-sub" style="margin:8px 0 4px;color:<?= $t['color'] ?>"><?= $t['label'] ?></div>
                <div class="am-rp-steps">
                    <?php foreach ($flowLabels[$type] as $status => $label): if ($status === 'delivered') continue; ?>
                        <?php $n = $report['pipeline'][$type][$status] ?? 0; ?>
                        <a class="am-rp-step <?= $n ? 'has' : '' ?>" href="<?= Url::to(['/agrimac/orders']) ?>"><b><?= $n ?></b><span><?= Html::encode($label) ?></span></a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            <?php if ($report['stuck']): ?>
                <div class="am-rp-sub" style="margin-top:12px">Đơn nằm quá 3 ngày ở một bước</div>
                <table class="am-rp-table">
                    <?php foreach ($report['stuck'] as $s): ?>
                        <tr><td><a href="<?= Url::to(['/agrimac/orders', 'order' => $s['code']]) ?>"><b><?= Html::encode($s['code']) ?></b></a> · <?= Html::encode(D::ORDER_TYPES[$s['type']]['short']) ?></td>
                            <td><?= Html::encode($s['status']) ?></td><td class="r" style="color:#ef4444"><?= $s['days'] ?> ngày</td></tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
        <div class="am-card am-rp-card">
            <div class="am-rp-title">⏱ Thời gian xử lý đơn bán (trung bình)</div>
            <table class="am-rp-table">
                <?php foreach ($report['leadTimes'] as [$label, $h]): ?>
                    <tr><td><?= Html::encode($label) ?></td><td class="r b"><?= $hours($h) ?></td></tr>
                <?php endforeach; ?>
            </table>
            <div class="am-rp-title" style="margin-top:16px">🔩 Lắp ráp</div>
            <table class="am-rp-table">
                <tr><td>Đơn lắp xong trong kỳ</td><td class="r b"><?= $report['assembly']['orders'] ?> đơn · <?= $report['assembly']['units'] ?> máy</td></tr>
                <tr><td>Máy đã nhập kho</td><td class="r"><?= $report['assembly']['stocked'] ?> máy</td></tr>
                <tr><td>Thời gian lắp trung bình / máy</td><td class="r b"><?= $minutes($report['assembly']['perUnit']) ?></td></tr>
            </table>
            <?php if ($report['assembly']['byWorker']): ?>
                <table class="am-rp-table" style="margin-top:6px">
                    <thead><tr><th>Thợ lắp</th><th class="r">Đơn</th><th class="r">Máy</th><th class="r">Tổng giờ</th><th class="r">TB / máy</th></tr></thead>
                    <?php foreach ($report['assembly']['byWorker'] as $w): ?>
                        <tr><td><?= Html::encode($w['name']) ?></td><td class="r"><?= $w['orders'] ?></td><td class="r"><?= $w['units'] ?></td>
                            <td class="r"><?= $minutes($w['minutes']) ?></td><td class="r b"><?= $minutes($w['perUnit']) ?></td></tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div class="am-rp-grid2">
        <div class="am-card am-rp-card">
            <div class="am-rp-title">🚛 Giao hàng</div>
            <table class="am-rp-table">
                <thead><tr><th>Nhà xe</th><th class="r">Đơn gửi</th><th class="r">Đang chở</th><th class="r">TG vận chuyển TB</th></tr></thead>
                <?php foreach ($report['carriers'] as $c): ?>
                    <tr><td><?= Html::encode($c['name']) ?></td><td class="r"><?= $c['orders'] ?></td><td class="r"><?= $c['open'] ?></td><td class="r"><?= $hours($c['hours']) ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$report['carriers']): ?><tr><td colspan="4" class="am-empty">Chưa gửi đơn nào qua nhà xe trong kỳ</td></tr><?php endif; ?>
            </table>
            <?php if ($report['drivers']): ?>
                <table class="am-rp-table" style="margin-top:8px">
                    <thead><tr><th>Nhân viên giao hàng</th><th class="r">Đơn gửi</th><th class="r">Khách đã nhận</th></tr></thead>
                    <?php foreach ($report['drivers'] as $d): ?>
                        <tr><td><?= Html::encode($d['name']) ?></td><td class="r"><?= $d['orders'] ?></td><td class="r"><?= $d['done'] ?></td></tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
        <div class="am-card am-rp-card">
            <div class="am-rp-title">🏗 Kho</div>
            <table class="am-rp-table">
                <tr><td>Giá trị tồn máy thành phẩm</td><td class="r b"><?= $m($report['stock']['productValue']) ?></td></tr>
                <tr><td>Giá trị tồn linh kiện</td><td class="r b"><?= $m($report['stock']['partValue']) ?></td></tr>
                <tr><td>Mã sắp hết (máy / linh kiện)</td><td class="r" style="color:<?= $report['stock']['lowProducts'] + $report['stock']['lowParts'] ? '#b45309' : 'inherit' ?>"><?= $report['stock']['lowProducts'] ?> / <?= $report['stock']['lowParts'] ?></td></tr>
            </table>
            <?php if ($report['topParts']): ?>
                <div class="am-rp-sub" style="margin-top:10px">Linh kiện xuất nhiều nhất trong kỳ</div>
                <table class="am-rp-table">
                    <?php foreach ($report['topParts'] as $p): ?>
                        <tr><td><?= Html::encode($p['name']) ?></td><td class="r"><?= $p['qty'] ?></td><td class="r"><?= $m($p['value']) ?></td></tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
            <div class="am-rp-title" style="margin-top:16px">🛡 Bảo hành</div>
            <table class="am-rp-table">
                <tr><td>Máy kích hoạt bảo hành</td><td class="r"><?= $report['warranty']['activated'] ?></td></tr>
                <tr><td>Khiếu nại mới / đang mở</td><td class="r"><?= $report['warranty']['claims'] ?> / <b style="color:<?= $report['warranty']['openClaims'] ? '#b45309' : 'inherit' ?>"><?= $report['warranty']['openClaims'] ?></b></td></tr>
                <tr><td>Đơn bảo hành · chi phí linh kiện</td><td class="r"><?= $report['warranty']['orders'] ?> · <?= $m($report['warranty']['cost']) ?></td></tr>
                <?php foreach ($report['claimProducts'] as $c): ?>
                    <tr><td style="color:#6b7280">↳ <?= Html::encode($c['name']) ?></td><td class="r"><?= $c['claims'] ?> khiếu nại</td></tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
