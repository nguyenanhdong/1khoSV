<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\web\JqueryAsset;

JqueryAsset::register($this);
$this->registerCssFile('@web/agrimac/css/agrimac.css');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Đăng nhập — 1Kho CMS</title>
    <link rel="shortcut icon" type="image/png" sizes="32x32" href="/img/favicon/favicon-32x32.png">
    <?php $this->head() ?>
</head>
<body class="am-login-body">
<?php $this->beginBody() ?>
<div class="am-login">
    <section class="am-login-hero">
        <div class="am-public-brand">
            <div class="am-brand-logo">🚜</div>
            <div>
                <div style="color:#fff;font-weight:800;font-size:16px">1Kho CMS</div>
                <div style="color:rgba(255,255,255,0.5);font-size:11px">v2.0 · Quản lý toàn diện</div>
            </div>
        </div>
        <h1 class="am-login-headline">Quản lý phân phối máy nông nghiệp<br>trên một nền tảng</h1>
        <ul class="am-login-points">
            <li>📋 Đơn hàng đại lý: duyệt, lắp ráp, xuất kho, giao hàng</li>
            <li>🏗 Tồn kho máy &amp; linh kiện, phiếu nhập/xuất</li>
            <li>💰 Công nợ đại lý, hoa hồng Sale &amp; giao hàng</li>
            <li>🛡 Bảo hành điện tử theo serial &amp; mã QR</li>
        </ul>
        <div class="am-login-foot"><?= date('Y') ?> © <?= Html::encode(Yii::$app->name) ?> CMS</div>
    </section>
    <section class="am-login-panel">
        <div class="am-login-card">
            <?= $content ?>
        </div>
    </section>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
