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
            'Đánh giá',
        ],
    ]);

    ?>

    <section class="voucher">
        <?= $this->render('/layouts/sidebar_info') ?>
        <div class="history_right review">
            <div class="tab_voucher">
                <button class="active tablinks" data-tab="tab_review">Chưa đánh giá</button>
                <button class="tablinks" data-tab="tab_reviewed">Đã đánh giá</button>
            </div>
            <div class="tab_content active" id="tab_review">
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
                                        <button class="btn_action btn-blue flex-center">Đánh giá</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="comment tab_content" id="tab_reviewed">
                <div class="comment_list">
                    <?php for ($i = 0; $i < 10; $i++) { ?>
                        <div class="comment_item">
                            <img class="comment_avatar" src="/images/icon/avatar.png" alt="">
                            <div class="comment_group_right">
                                <div class="user_name flex-item-center">
                                    <p>Bùi Thúy Hằng</p>
                                    <span>•</span>
                                    <span>20-10-2024</span>
                                </div>
                                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book</p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
</div>