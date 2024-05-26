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
            ['label' => 'Quan tâm', 'url' => ['']],
            'Đã xem',
        ],
    ]);

    ?>

    <section class="voucher">
        <?= $this->render('/layouts/sidebar_info') ?>
        <div class="history_right favourite">
            <h2>Đã xem</h2>
            <?php for($i = 0; $i < 5; $i++) { ?>
                <div class="group_item_shop d-flex flex-column">
                    <div class="item_shop">
                        <div class="item_shop_left d-flex flex-column">
                            <div class="desc_item">
                                <div class="flex-center avatar_pro">
                                    <img src="/images/page/may-cay.png" alt="">
                                </div>
                                <div class="text_desc d-flex flex-column">
                                    <p>Máy cày Kubota sử dụng công nghệ mới</p>
                                    <div class="flex-item-center">
                                        <strong>200.000</strong>
                                        <span>-36%</span>
                                    </div>
                                    <div class="flex-item-center justify-content-between">
                                        <p>Số lượng 1</p>
                                        <div class="rating_product flex-item-center">
                                            <img src="/images/icon/star-active.svg" alt="">
                                            <img src="/images/icon/star-active.svg" alt="">
                                            <img src="/images/icon/star-active.svg" alt="">
                                            <img src="/images/icon/star-active.svg" alt="">
                                            <img src="/images/icon/star-inactive.svg" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="item_shop_right d-flex flex-column">
                            <div class="btn_item">
                                <div class="action_form">
                                    <div class=" position-relative check_heart">
                                        <!-- <input type="checkbox" class="checkbox_heart"> 
                                        <label for="heart">
                                        </label> -->
                                        <img src="/images/icon/heart-inactive.svg" alt="">
                                    </div>
                                    <button class="btn_action btn-blue flex-center">Xem chi  tiết</button>
                                    <button class="btn_action btn-orange flex-center">Mua lại</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </section>
</div>