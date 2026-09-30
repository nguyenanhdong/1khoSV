<?php

/* @var $this \yii\web\View */
/* @var $role string */
/* @var $page string|null trang AgriMac đang mở */
/* @var $legacy string|null controller sàn 1kho đang mở */
/* @var $canSwitch bool */

use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\AgrimacAuth;
use backend\components\AgrimacData as D;

$roleInfo = D::ROLES[$role];
$identity = Yii::$app->user->identity;
$lastLogin = !empty($identity->last_login) ? date('H:i d/m', strtotime($identity->last_login)) : null;
?>
<aside class="am-sidebar">
    <a class="am-brand" href="<?= Url::to(['/agrimac/dashboard']) ?>">
        <div class="am-brand-logo">🚜</div>
        <div>
            <div class="am-brand-title">1Kho CMS</div>
            <div class="am-brand-sub">v2.0 · Quản lý toàn diện</div>
        </div>
    </a>

    <?php if ($canSwitch): ?>
    <div class="am-role-box">
        <div class="am-role-label">XEM THEO VAI TRÒ (ADMIN)</div>
        <select id="am-role-select" class="am-role-select" data-url="<?= Url::to(['/agrimac/dashboard']) ?>">
            <?php foreach (D::ROLES as $key => $r): ?>
                <option value="<?= $key ?>" <?= $key === $role ? 'selected' : '' ?>><?= $r['icon'] . ' ' . Html::encode($r['label']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>

    <?php
    $legacyItem = function ($controller, $item) use ($legacy) { ?>
        <a href="<?= Html::encode($item['url']) ?>" class="am-nav-item <?= $controller === $legacy ? 'active' : '' ?>">
            <span class="am-nav-icon"><?= $item['icon'] ?></span>
            <span><?= Html::encode($item['label']) ?></span>
        </a>
    <?php };
    $canLegacy = AgrimacAuth::canUseLegacy();
    $agrimacMenu = D::menuFor($role);
    ?>
    <nav class="am-nav">
        <?php foreach ($agrimacMenu as $id => $item): ?>
            <?php if ($canLegacy) {
                foreach (D::LEGACY_MENU as $controller => $legacyMenu) {
                    if (($legacyMenu['before'] ?? null) === $id) $legacyItem($controller, $legacyMenu);
                }
            } ?>
            <a href="<?= Url::to(['/agrimac/' . $id]) ?>" class="am-nav-item <?= $id === $page ? 'active' : '' ?>">
                <span class="am-nav-icon"><?= $item['icon'] ?></span>
                <span><?= Html::encode($item['label']) ?></span>
            </a>
        <?php endforeach; ?>

        <?php if ($canLegacy): ?>
            <?php foreach (D::LEGACY_MENU as $controller => $item): ?>
                <?php if (empty($item['before']) || !isset($agrimacMenu[$item['before']])) $legacyItem($controller, $item); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </nav>

    <div class="am-side-foot">
        <div class="am-side-avatar" style="background:<?= $roleInfo['color'] ?>33;color:<?= $roleInfo['color'] ?>"><?= Html::encode(mb_substr($roleInfo['label'], 0, 1)) ?></div>
        <div style="flex:1;min-width:0">
            <div class="am-side-name"><?= Html::encode($roleInfo['label']) ?></div>
            <div class="am-side-sub" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= Html::encode(trim((string)($identity->fullname ?? '')) ?: $identity->username) ?><?= $lastLogin ? ' · ' . Html::encode($lastLogin) : '' ?></div>
        </div>
        <a class="am-logout" href="<?= Url::to(['/site/logout']) ?>" title="Đăng xuất">⏻</a>
    </div>
</aside>
