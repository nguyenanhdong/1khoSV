<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use yii\widgets\Breadcrumbs;
use backend\controllers\CommonController;
use frontend\controllers\HelperController;

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
            <?php if(!empty($data)){ ?>
                <h2>Đã xem</h2>
                <div class="list_seen">
                    <?php 
                        foreach($data as $row){
                    ?>
                        <div class="group_item_shop d-flex flex-column">
                            <div class="item_shop">
                                <div class="item_shop_left d-flex flex-column">
                                    <div class="desc_item">
                                        <div class="flex-center avatar_pro">
                                            <img src="<?= $row['image'] ?>" alt="">
                                        </div>
                                        <div class="text_desc d-flex flex-column">
                                            <p><?= $row['name'] ?></p>
                                            <div class="flex-item-center">
                                                <strong><?= HelperController::formatPrice($row['price']) ?></strong>
                                                <span>-<?= $row['percent_discount'] ?>%</span>
                                            </div>
                                            <div class="flex-item-center justify-content-between">
                                                <p>Số lượng <?= $row['quantity_sold'] ?></p>
                                                <div class="rating_product flex-item-center">
                                                    <?php
                                                        for ($i = 0; $i < 5; $i++) {
                                                    ?>
                                                        <?php if ($i < $row['star']) { ?>
                                                            <img src="/images/icon/star-active.svg" alt="">
                                                        <?php } else { ?>
                                                            <img src="/images/icon/star-inactive.svg" alt="">
                                                        <?php } ?>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="item_shop_right d-flex flex-column">
                                    <div class="btn_item">
                                        <div class="action_viewd">
                                            <div class=" position-relative check_heart">
                                                <!-- <input type="checkbox" class="checkbox_heart"> 
                                                <label for="heart">
                                                </label> -->
                                                <!-- <img src="/images/icon/heart-inactive.svg" alt=""> -->
                                            </div>
                                            <a target="_blank" href="<?= Url::to(['/product/detail', 'id' => $row['id']]) ?>" class="btn_action btn-blue flex-center">Xem chi tiết</a>
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
                    <p>Chưa có sản phẩm nào</p>
                </div>
            <?php } ?>
            <?php if($checkLoadMore){ ?>
                <div class="see_more_product">
                    <button class="btn_load_more see_more_product_seen">Xem thêm</button>
                </div>
            <?php } ?>
        </div>
    </section>
</div>