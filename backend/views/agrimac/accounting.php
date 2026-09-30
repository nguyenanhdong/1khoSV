<?php

/* @var $this yii\web\View */
/* @var $rows array */
/* @var $dealers array */
/* @var $revenue float */
/* @var $comSale float */
/* @var $comDeli float */
/* @var $debt float */
/* @var $overLimit int */
/* @var $commissions array */

use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\AgrimacData as D;
use backend\components\AgrimacUi as Ui;
use backend\assets\AgrimacAsset;
use yii\web\View;

$this->registerJsVar('AM_COMMISSIONS', $commissions, View::POS_HEAD);
$this->registerJsFile('@web/agrimac/js/am-accounting.js', ['depends' => AgrimacAsset::class]);
?>
<div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px">
        <?= Ui::stat('💵', 'Doanh thu HT', D::money($revenue), 'Đơn đã giao', '#10b981') ?>
        <?= Ui::stat('💰', 'Tổng công nợ ĐL', D::money($debt), $overLimit . ' ĐL vượt hạn mức', '#f97316') ?>
        <?= Ui::stat('👔', 'HH Sale tháng', D::money($comSale), D::COMMISSION_RATES['sale'] . '% doanh số', '#8b5cf6') ?>
        <?= Ui::stat('🚚', 'HH Giao hàng', D::money($comDeli), D::COMMISSION_RATES['delivery'] . '% doanh số', '#3b82f6') ?>
    </div>

    <div class="am-card" style="overflow:hidden">
        <div class="am-row-between" style="padding:14px 18px;border-bottom:1px solid #e5e7eb">
            <div style="font-weight:700;font-size:14px">🧾 Sổ thu chi <?= Html::encode(mb_strtolower(D::PERIOD_LABEL)) ?></div>
            <a href="<?= Url::to(['agrimac/accounting-export']) ?>" style="padding:6px 14px;background:#10b981;color:#fff;border-radius:8px;font-size:12px;font-weight:700">⬇ Xuất Excel</a>
        </div>
        <table class="am-table">
            <?= Ui::thead(['Mã đơn', 'Đại lý', 'Doanh thu', 'HH Sale', 'HH Giao hàng', 'Thực thu', 'Ngày']) ?>
            <tbody>
            <?php foreach ($rows as $o): ?>
                <tr>
                    <td><span style="font-weight:700"><?= Html::encode($o['id']) ?></span></td>
                    <td style="font-size:12px"><?= Html::encode(D::shortDealerName($dealers[$o['dealerId']]['name'] ?? '')) ?></td>
                    <td><span style="font-weight:700;color:#059669"><?= D::money($o['total']) ?></span></td>
                    <td style="color:#7c3aed;font-weight:600"><?= D::money($o['comSale']) ?></td>
                    <td style="color:#0891b2;font-weight:600"><?= D::money($o['comDeli']) ?></td>
                    <td><span style="font-weight:800;color:#1a2035"><?= D::money($o['total'] - $o['comSale'] - $o['comDeli']) ?></span></td>
                    <td style="color:#9ca3af;font-size:12px"><?= Html::encode($o['deliveredAt'] ?: $o['date']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="2" style="padding:11px 13px;font-weight:700">TỔNG</td>
                <td style="padding:11px 13px;font-weight:800;color:#059669"><?= D::money($revenue) ?></td>
                <td style="padding:11px 13px;font-weight:700;color:#7c3aed"><?= D::money($comSale) ?></td>
                <td style="padding:11px 13px;font-weight:700;color:#0891b2"><?= D::money($comDeli) ?></td>
                <td style="padding:11px 13px;font-weight:900;font-size:15px"><?= D::money($revenue - $comSale - $comDeli) ?></td>
                <td></td>
            </tr>
            </tfoot>
        </table>
    </div>

    <div class="am-card" style="overflow:hidden;margin-top:16px">
        <div class="am-row-between" style="padding:14px 18px;border-bottom:1px solid #e5e7eb">
            <div>
                <div style="font-weight:700;font-size:14px">👔 Hoa hồng theo nhân viên</div>
                <div style="font-size:11px;color:#9ca3af;margin-top:2px">Tính khi đơn hoàn thành: Sale <?= D::COMMISSION_RATES['sale'] ?>% · Giao hàng <?= D::COMMISSION_RATES['delivery'] ?>% doanh thu</div>
            </div>
            <div id="am-com-summary" style="font-size:12px;color:#6b7280"></div>
        </div>
        <table class="am-table">
            <?= Ui::thead(['Nhân viên', 'Vai trò', 'Số đơn', 'Doanh số tính HH', 'Hoa hồng', 'Đã chi trả', 'Còn phải trả', '']) ?>
            <tbody id="am-com-rows"></tbody>
        </table>
    </div>
</div>
