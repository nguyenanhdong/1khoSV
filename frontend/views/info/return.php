<?php

/* @var $this yii\web\View */
/* @var $refunds array yêu cầu trả hàng/hoàn tiền của khách (order_refund + sản phẩm) */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use backend\models\OrderRefund;
use frontend\controllers\HelperController;

$domain = Yii::$app->params['urlDomain'];
$statusLabel = [
    OrderRefund::STATUS_PENDING => ['Đang chờ xử lý', '#f59e0b'],
    OrderRefund::STATUS_APPROVE => ['Đồng ý trả hàng/hoàn tiền', '#10b981'],
    OrderRefund::STATUS_REJECT  => ['Bị từ chối', '#ef4444'],
];
$situations = [1 => 'Đã nhận hàng nhưng hàng có vấn đề', 2 => 'Chưa nhận hàng / nhận thiếu hàng'];
?>
<div class="container">
    <?= Breadcrumbs::widget(['homeLink' => ['label' => '', 'url' => '/'], 'links' => ['Trả hàng hoàn tiền']]) ?>

    <section class="voucher">
        <?= $this->render('/layouts/sidebar_info') ?>
        <div class="return">
            <h2>Trả hàng, hoàn tiền</h2>
            <div class="item_return">
                <img src="/images/icon/return.svg" alt="">
                <div>
                    <span>Cách gửi yêu cầu</span>
                    <p>Vào <a href="<?= Url::to(['/info/purchase-history']) ?>">Đơn đã mua</a>, mở chi tiết đơn và bấm <strong>Trả hàng/Hoàn tiền</strong> (trong vòng 10 ngày kể từ khi nhận hàng).
                        Trường hợp được chấp nhận, voucher đã dùng có thể không được hoàn lại.</p>
                </div>
            </div>

            <h2 class="mt-4">Yêu cầu của bạn</h2>
            <?php if (!$refunds): ?>
                <p class="color-gray">Bạn chưa gửi yêu cầu trả hàng/hoàn tiền nào.</p>
            <?php endif; ?>
            <?php foreach ($refunds as $r): ?>
                <?php $st = $statusLabel[(int)$r['status']] ?? ['', '#6b7280']; $img = $r['product_image'] ? $domain . explode(';', $r['product_image'])[0] : ''; ?>
                <a class="item_return" href="<?= Url::to(['/info/order-detail', 'id' => $r['order_id']]) ?>">
                    <img src="<?= Html::encode($img ?: '/images/icon/return.svg') ?>" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:8px" onerror="this.onerror=null;this.src='/images/icon/return.svg'">
                    <div>
                        <span><?= Html::encode($r['product_name'] ?: 'Đơn #' . $r['order_id']) ?> · Đơn #<?= (int)$r['order_id'] ?></span>
                        <p><?= Html::encode($situations[(int)$r['type_situation']] ?? '') ?> — <?= Html::encode($r['reason']) ?></p>
                        <p>Số tiền hoàn: <strong><?= HelperController::formatPrice($r['price_refund']) ?>đ</strong> ·
                            Trạng thái: <strong style="color:<?= $st[1] ?>"><?= $st[0] ?></strong>
                            <?= (int)$r['status'] === OrderRefund::STATUS_REJECT && $r['reason_cancel'] ? ' (' . Html::encode($r['reason_cancel']) . ')' : '' ?></p>
                        <p class="color-gray" style="font-size:12px">Gửi lúc <?= date('H:i d/m/Y', strtotime($r['created_at'])) ?><?= $r['time_process'] ? ' · Xử lý lúc ' . date('H:i d/m/Y', strtotime($r['time_process'])) : '' ?></p>
                    </div>
                    <i class="far fa-angle-right"></i>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</div>
