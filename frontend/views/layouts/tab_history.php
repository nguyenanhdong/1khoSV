<?php
use yii\helpers\Url;
$controller_action = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;
?>

<div class="tab_history d-lg-none d-flex">
    <a class="<?= $controller_action == 'info/await-confirmed' ? 'active' : '' ?>" href="<?= Url::to(['/info/await-confirmed']) ?>">Chờ xác nhận</a>
    <a class="<?= $controller_action == 'info/confirmed' ? 'active' : '' ?>" href="<?= Url::to(['/info/confirmed']) ?>">Đã xác nhận</a>
    <a class="<?= $controller_action == 'info/delivering' ? 'active' : '' ?>" href="<?= Url::to(['/info/delivering']) ?>">Đang giao</a>
    <a class="<?= $controller_action == 'info/purchase-history' ? 'active' : '' ?>" href="<?= Url::to(['/info/purchase-history']) ?>">Đã mua</a>
</div>