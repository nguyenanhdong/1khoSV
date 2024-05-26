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
            'Mời bạn bè',
        ],
    ]);

    ?>

    <section class="voucher">
        <?= $this->render('/layouts/sidebar_info') ?>
        <div class="invite_friend d-flex flex-column">
            <div class="friend_text">
                <div class="text-center">
                    <span>Giới thiệu bạn bè</span>
                    <p>Sau khi bạn bè của bạn hoàn thành công việc đầu tiên tại 1Kho, người bạn của bạn và bạn sẽ được tặng ngay 50.000đ ngay vào tài khoản. Giới thiệu càng nhiều bạn bè nhé!</p>
                </div>
                <div class="text-center code_friend flex-center flex-column">
                    <span>Mã giới thiệu của bạn</span>
                    <button class="btn_action btn-blue flex-center">0988777888 <img src="/images/icon/copy.svg" alt=""></button>
                    <button class="btn_action bg_blue flex-center">Chia Sẻ</button>
                </div>
            </div>
            <div class="text-center">
                <img class="img_friend" src="/images/page/friend.png" alt="">
            </div>
        </div>
    </section>
</div>