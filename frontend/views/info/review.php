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
                <?php
                    if(!empty($dataNotReview)){
                        foreach($dataNotReview as $row){
                ?>
                    <div class="group_item_shop d-flex flex-column">
                        <div class="item_shop">
                            <div class="item_shop_left d-flex flex-column">
                                <div class="desc_item">
                                    <div class="flex-center avatar_pro">
                                        <img src="<?= $row['product_img'] ?>" alt="">
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
                                                <?php 
                                                    for($i = 0; $i < 5; $i++){ 
                                                ?>
                                                    <?php if( $i < $row['product_star']){ ?>
                                                        <img src="/images/icon/star-active.svg" alt="">
                                                    <?php }else{ ?>
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
                                    <div class="action_form">
                                        <button class="btn_action btn-blue flex-center" data-toggle="modal" data-target="#modalReview<?= $row['product_id'] ?>">Đánh giá</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="modalReview<?= $row['product_id'] ?>" tabindex="-1" role="dialog" aria-labelledby="modaReviewTitle" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <h2>Địa chỉ giao hàng</h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php }} ?>
                <?php if($dataCheckLoadMoreNotReview) { ?>
                    <div class="see_more_product">
                        <button dt-type="not-review" class="btn_load_more see_more_product_review">Xem thêm</button>
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