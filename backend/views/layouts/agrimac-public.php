<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;

$this->registerCssFile('@web/agrimac/css/agrimac.css');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= Html::encode($this->title ?: 'Kích hoạt bảo hành') ?> — 1Kho</title>
    <?php $this->head() ?>
</head>
<body class="am-public">
<?php $this->beginBody() ?>
<div class="am-public-wrap">
    <div class="am-public-brand">
        <div class="am-brand-logo">🚜</div>
        <div>
            <div style="color:#fff;font-weight:800;font-size:15px">1Kho</div>
            <div style="color:rgba(255,255,255,0.5);font-size:11px">Bảo hành điện tử</div>
        </div>
    </div>
    <?= $content ?>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
