<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Chưa được phân quyền';
?>
<div class="am-public-card" style="text-align:center">
    <div style="font-size:44px">🔒</div>
    <h1 class="am-public-title">Tài khoản chưa được gán vai trò trong 1Kho CMS</h1>
    <p class="am-public-text">Xin chào <b><?= Html::encode(Yii::$app->user->identity->fullname ?: Yii::$app->user->identity->username) ?></b>,
        tài khoản của bạn chưa có vai trò trong 1Kho CMS. Vui lòng liên hệ quản trị viên để được phân quyền.</p>
    <p class="am-public-text" style="margin-top:12px"><a href="<?= Url::to(['/site/index']) ?>">← Về trang quản trị chính</a></p>
</div>
