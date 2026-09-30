<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\web\View;
use backend\assets\AgrimacAsset;
use backend\components\AgrimacData as D;
use backend\components\AgrimacRepo as R;

AgrimacAsset::register($this);

$role     = $this->params['amRole'];
$page     = $this->params['amPage'];
$roleInfo = D::ROLES[$role];
$current  = D::MENU[$page] ?? D::MENU['dashboard'];

$this->registerJsVar('AM_DATA', R::clientConfig($role), View::POS_HEAD);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>1Kho CMS — <?= Html::encode($current['label']) ?></title>
    <?php $this->head() ?>
</head>
<body class="am-body">
<?php $this->beginBody() ?>
<div class="am-app">
    <?= $this->render('agrimac/_sidebar', ['role' => $role, 'page' => $page, 'legacy' => null, 'canSwitch' => !empty($this->params['amCanSwitchRole'])]) ?>

    <div class="am-main">
        <header class="am-topbar">
            <div class="am-topbar-title"><?= $current['icon'] . ' ' . Html::encode($current['label']) ?></div>
            <div class="am-topbar-right">
                <span class="am-pill" style="background:#fef3c7;color:#b45309"><?= Html::encode(D::PERIOD_LABEL) ?></span>
                <span class="am-pill" style="background:<?= $roleInfo['color'] ?>22;color:<?= $roleInfo['color'] ?>"><?= $roleInfo['icon'] . ' ' . Html::encode($roleInfo['label']) ?></span>
            </div>
        </header>
        <main class="am-content"><?= $content ?></main>
    </div>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
