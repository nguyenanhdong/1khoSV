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
        <div class="point_right">
            <div class="point_top flex-center">
                <div class="point_bg flex-center flex-column">
                    <p>Ví tích điểm</p>
                    <span>Số Ví trong tài khoản</span>
                    <div class="flex-item-center">
                        <img src="/images/icon/vi.svg" alt="">
                        <span>50.000</span>
                    </div>
                </div>
            </div>
            <div class="point_list d-flex flex-column">
                <?php for($i = 0; $i < 3;$i++) { ?>
                    <div class="point_item active flex-item-center justify-content-between">
                        <div class="point_left flex-item-center">
                            <img src="/images/icon/x.svg" alt="">
                            <div>
                                <p>Dùng Ví để mua sửa phẩm</p>
                                <p>14:00 - 20/08/2023</p>
                            </div>
                        </div>
                        <div class="point_num">
                            <span>-30.000</span>
                        </div>
                    </div>
                    <div class="point_item inactive flex-item-center justify-content-between">
                        <div class="point_left flex-item-center">
                            <img src="/images/icon/ok.svg" alt="">
                            <div>
                                <p>Dùng Ví để mua sửa phẩm</p>
                                <p>14:00 - 20/08/2023</p>
                            </div>
                        </div>
                        <div class="point_num">
                            <span>-30.000</span>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>
</div>