<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\web\View;
use backend\components\AgrimacData as D;
use backend\components\AgrimacUi as Ui;
use backend\assets\AgrimacAsset;

$this->registerJsVar('AM_ROLES', D::ROLES, View::POS_HEAD);
$this->registerJsVar('AM_ROLE_PAGES', array_map(function ($role) {
    return array_values(array_column(D::menuFor($role), 'label'));
}, array_combine(array_keys(D::ROLES), array_keys(D::ROLES))), View::POS_HEAD);
$this->registerJsFile('@web/agrimac/js/am-users.js', ['depends' => AgrimacAsset::class]);
?>
<div style="display:flex;gap:14px">
    <div style="flex:1">
        <div class="am-toolbar-end"><button class="am-btn" data-action="new-user">+ Tạo tài khoản</button></div>
        <div class="am-card" style="overflow:hidden">
            <table class="am-table">
                <?= Ui::thead(['Nhân viên', 'Vai trò', 'Email', 'Truy cập CMS', 'App', 'Đăng nhập lần cuối', 'Trạng thái', '']) ?>
                <tbody id="am-user-rows"></tbody>
            </table>
        </div>
    </div>

    <div style="width:320px;flex-shrink:0">
        <div class="am-card" style="overflow:hidden">
            <div style="padding:12px 16px;border-bottom:1px solid #e5e7eb;font-weight:700;font-size:13px">🔑 Ma trận phân quyền</div>
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:11px">
                    <thead>
                    <tr style="background:#f8fafc">
                        <th style="padding:8px 10px;text-align:left;font-weight:600;color:#6b7280;font-size:10px">Module</th>
                        <?php foreach (D::ROLES as $key => $r): ?>
                            <th title="<?= Html::encode($r['label']) ?>" style="padding:8px 6px;text-align:center;font-weight:600;color:<?= $r['color'] ?>;font-size:10px"><?= $r['icon'] ?></th>
                        <?php endforeach; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i = 0; foreach (D::PERMISSION_MODULES as $module => $label): ?>
                        <tr style="background:<?= $i++ % 2 === 0 ? '#fff' : '#fafafa' ?>">
                            <td style="padding:7px 10px;font-size:11px;font-weight:600;color:#374151"><?= Html::encode($label) ?></td>
                            <?php foreach (D::ROLES as $key => $r): ?>
                                <td style="padding:7px 6px;text-align:center">
                                    <?= D::canAccess($key, $module) ? '<span style="color:#059669;font-size:14px">✓</span>' : '<span style="color:#e5e7eb;font-size:12px">✕</span>' ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="padding:10px 14px;border-top:1px solid #e5e7eb;display:flex;flex-wrap:wrap;gap:5px">
                <?php foreach (D::ROLES as $r): ?>
                    <span style="font-size:10px;color:<?= $r['color'] ?>;background:<?= $r['color'] ?>15;padding:2px 7px;border-radius:10px;font-weight:700"><?= $r['icon'] . ' ' . Html::encode(explode(' ', $r['label'])[0]) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
