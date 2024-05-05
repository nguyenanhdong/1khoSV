<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use yii\widgets\Breadcrumbs;

?>
<div class="container">
    <?php
    echo Breadcrumbs::widget([
        'homeLink' => ['label' => '', 'url' => '/'],
        'links' => [
            ['label' => 'Máy cày', 'url' => ['product/delivery']],
            'Đăng tin',
        ],
    ]);

    ?>

    <section class="post_success_delivery flex-center flex-column">
        <div class="img_success">
            <img src="/images/icon/phone-success.png" alt="">
        </div>
        <div class="text_success flex-center flex-column">
            <span>Đăng tin thành công</span>
            <p class="text-center">Đơn hàng của bạn đã được xác nhận, chúng <br> tôi sẽ sớm gửi cho bạn email xác nhận.</p>
        </div>
        <a class="btn_action btn-gray flex-center" href="/">Về trang chủ</a>
    </section>
</div>