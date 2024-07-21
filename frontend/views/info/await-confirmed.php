<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use yii\widgets\Breadcrumbs;
use backend\controllers\CommonController;
use backend\models\Order;
use frontend\controllers\HelperController;

?>
<div class="container">
    <?php
    echo Breadcrumbs::widget([
        'homeLink' => ['label' => '', 'url' => '/'],
        'links' => [
            'Lịch sử mua hàng',
            'Chờ xác nhận',
        ],
    ]);

    ?>
    <?= $this->render('/layouts/tab_history') ?>
    <section class="voucher">
        <?= $this->render('/layouts/sidebar_info') ?>
        <div class="history_right">
                <?php 
                    if(!empty($data)){
                ?>
                <div class="product_info_list">
                    <?php foreach($data as $row){ ?>
                        <div class="group_item_shop d-flex flex-column">
                            <div class="title_shop d-flex justify-content-between">
                                <p><?= $row['agent_name'] ?></p>
                                <span>Chờ xác nhận</span>
                            </div>
                            <div class="item_shop">
                                <div class="item_shop_left d-flex flex-column">
                                    <div class="desc_item">
                                        <div class="flex-center avatar_pro">
                                            <img src="<?= $row['product_image'] ?>" alt="<?= $row['product_name'] ?>">
                                        </div>
                                        <div class="text_desc d-flex flex-column">
                                            <p><?= $row['product_name'] ?></p>
                                            <div class="flex-item-center">
                                                <strong><?= HelperController::formatPrice($row['price']) ?></strong>
                                                <span>-<?= $row['percent_discount'] ?>%</span>
                                            </div>
                                            <div class="flex-item-center justify-content-between">
                                                <p>Số lượng <?= $row['quantity'] ?></p>
                                                <div class="rating_product flex-item-center">
                                                    <?php for($i = 0; $i < $row['star']; $i++){ ?>
                                                        <img src="/images/icon/star-active.svg" alt="">
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="item_shop_right d-flex flex-column">
                                    <div class="btn_item">
                                        <div class="action_form not_purchased">
                                            <a href="<?= Url::to(['/info/order-detail', 'id' => $row['order_id']]) ?>" class="btn_action btn-blue flex-center">Xem chi tiết</a>
                                            <!-- <button class="btn_action btn-orange flex-center">Mua hàng</button> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php }else{ ?>
                <div class="empty_order flex-center">
                    <img src="/images/page/empty.png" alt="">
                    <p>Chưa có đơn hàng nào</p>
                    <a class="btn_action btn-blue flex-center" href="/">Mua sắm ngay</a>
                </div>
            <?php } ?>
            <?php if($dataCheckLoadMore) { ?>
                <div class="see_more_product">
                    <button dt-type="pending" class="see_more_product_history">Xem thêm</button>
                </div>
            <?php } ?>
        </div>
    </section>
</div>