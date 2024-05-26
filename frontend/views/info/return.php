<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use yii\widgets\Breadcrumbs;
use backend\controllers\CommonController;

?>
<div class="container">
    <?php
    echo Breadcrumbs::widget([
        'homeLink' => ['label' => '', 'url' => '/'],
        'links' => [
            'Trả hàng hoàn tiền',
        ],
    ]);

    ?>

    <section class="voucher">
        <?= $this->render('/layouts/sidebar_info') ?>
        <div class="return">
            <h2>Chọn tình huống đang gặp</h2>
            <div class="item_return">
                <img src="/images/icon/return.svg" alt="">
                <div>
                    <span>Tôi đã nhận hàng & hàng có vấn đề</span>
                    <p>Lưu ý: Trường hợp yêu cầu Trả hàng, hoàn tiền của bạn được chấp nhận, Voucher có thể sẽ không được hoàn lại</p>
                </div>
                <i class="far fa-angle-right"></i>
            </div>
            <div class="item_return">
                <img src="/images/icon/return.svg" alt="">
                <div>
                    <span>Tôi đã nhận hàng & hàng có vấn đề</span>
                    <p>Lưu ý: Trường hợp yêu cầu Trả hàng, hoàn tiền của bạn được chấp nhận, Voucher có thể sẽ không được hoàn lại</p>
                </div>
                <i class="far fa-angle-right"></i>
            </div>
        </div>
    </section>
</div>