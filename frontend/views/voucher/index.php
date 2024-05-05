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
            // ['label' => '   Sample Post', 'url' => ['post/edit', 'id' => 1]],
            'Ví voucher ',
        ],
    ]);

    ?>

    <section class="voucher">
        <?= $this->render('/layouts/sidebar_info') ?>
        <div class="voucher_content">
            <div class="tab_voucher">
                <button class="active">Chưa sử dụng</button>
                <button>Đã dùng/hết hạn</button>
            </div>
            <div class="voucher_list">
                <?php for ($i = 0; $i < 20; $i++) { ?>
                    <div class="voucher_item">
                        <div class="voucher_avatar flex-center">
                            <img src="/images/page/img-voucher.png" alt="">
                        </div>
                        <div class="voucher_desc">
                            <span>Giảm 50k</span>
                            <p>Hoàn 10% cho đơn hàng có giá trị trên 1tr vào KHOvoucher</p>
                            <a href="">Dùng ngay</a>
                        </div>
                        <div class="tooltips">
                            <img src="/images/icon/i.svg" alt="">
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>
</div>