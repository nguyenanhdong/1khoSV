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
                <div class="list_review">
                    <?php
                    if (!empty($dataNotReview)) {
                        foreach ($dataNotReview as $row) {
                    ?>
                            <div class="group_item_shop px-0 d-flex flex-column">
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
                                                        for ($i = 0; $i < 5; $i++) {
                                                        ?>
                                                            <?php if ($i < $row['product_star']) { ?>
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
                                            <div class="action_form">
                                                <button class="btn_action btn-blue flex-center" data-toggle="modal" data-target="#modalReview<?= $row['order_id'] ?>">Đánh giá</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade" id="modalReview<?= $row['order_id'] ?>" tabindex="-1" role="dialog" aria-labelledby="modaReviewTitle" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content modal_content_review">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <h2>ĐÁNH GIÁ SẢN PHẨM</h2>
                                                <div class="product_review desc_item">
                                                    <div class="flex-center avatar_pro">
                                                        <img src="<?= $row['product_img'] ?>" alt="">
                                                    </div>
                                                    <div class="product_review_detail">
                                                        <p><?= $row['product_name'] ?></p>
                                                        <div class="flex-item-center text_desc">
                                                            <strong><?= HelperController::formatPrice($row['price']) ?></strong>
                                                            <span>-<?= $row['percent_discount'] ?>%</span>
                                                        </div>
                                                        <p>Số lượng <?= $row['quantity'] ?></p>
                                                    </div>
                                                </div>
                                                <div class="form_review">
                                                    <label>Chất lượng sản phẩm <span class="color_red">*</span></label>

                                                    <div id="group-stars">
                                                        <div class="rating-group">
                                                            <input  disabled checked class="rating__input rating__input--none rating_<?= $row['order_id'] ?>" name="rating<?= $row['order_id'] ?>" id="rating<?= $row['order_id'] ?>-none" value="0" type="radio">
                                                            <label aria-label="1 star" class="rating__label" for="rating<?= $row['order_id'] ?>-1"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                            <input  class="rating__input rating_<?= $row['order_id'] ?>" name="rating<?= $row['order_id'] ?>" id="rating<?= $row['order_id'] ?>-1" value="1" type="radio">
                                                            <label aria-label="2 stars" class="rating__label" for="rating<?= $row['order_id'] ?>-2"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                            <input class="rating__input rating_<?= $row['order_id'] ?>" name="rating<?= $row['order_id'] ?>" id="rating<?= $row['order_id'] ?>-2" value="2" type="radio">
                                                            <label aria-label="3 stars" class="rating__label" for="rating<?= $row['order_id'] ?>-3"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                            <input  class="rating__input rating_<?= $row['order_id'] ?>" name="rating<?= $row['order_id'] ?>" id="rating<?= $row['order_id'] ?>-3" value="3" type="radio">
                                                            <label aria-label="4 stars" class="rating__label" for="rating<?= $row['order_id'] ?>-4"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                            <input  class="rating__input rating_<?= $row['order_id'] ?>" name="rating<?= $row['order_id'] ?>" id="rating<?= $row['order_id'] ?>-4" value="4" type="radio">
                                                            <label aria-label="5 stars" class="rating__label" for="rating<?= $row['order_id'] ?>-5"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                            <input  class="rating__input rating_<?= $row['order_id'] ?>" name="rating<?= $row['order_id'] ?>" id="rating<?= $row['order_id'] ?>-5" value="5" type="radio">
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="form-group">
                                                    <div class="box-image flex-center flex-column">
                                                        <div class="form-group field-fileInput">
                                                            <input type="hidden" name="Advertisement[image][]" value=""><input class="fileInput" type="file" id="fileInput_<?= $row['order_id'] ?>" order-id="<?= $row['order_id'] ?>" name="Advertisement[image][]" multiple="" accept="image/*,video/*">
                                                            <div class="help-block"></div>
                                                        </div> <img src="/images/icon/icon-img.svg" alt="">
                                                        <label for="">Chọn 3 video ngắn dưới 1 phút + 8 hình ảnh</label>
                                                        <i class="error">Dung lượng ảnh tối đa 1MB, dung lượng video tối đa 20MB</i>
                                                    </div>
                                                    <div class="preview-box" id="previewBox_<?= $row['order_id'] ?>"></div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="">Đánh giá <span class="color_red">*</span></label>
                                                    <textarea rows="5" name="" id="content_<?= $row['order_id'] ?>"></textarea>
                                                </div>
                                                <div class="form-group">
                                                    <button type="button" pro-id="<?= $row['product_id'] ?>" od-id="<?= $row['order_id'] ?>" class="btn_action btn_submit_review btn-orange flex-center">Gửi đánh giá</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php }
                    } ?>
                </div>
                <?php if ($dataCheckLoadMoreNotReview) { ?>
                    <div class="see_more_product">
                        <button dt-type="not-review" class="btn_load_more see_more_product_not_review">Xem thêm</button>
                    </div>
                <?php } ?>
            </div>
            <div class="comment tab_content" id="tab_reviewed">
                <div class="comment_list">
                    <?php
                        if(!empty($dataReviewd)){
                            foreach($dataReviewd as $row){
                                $elementNav = '';
                                $elementFor = '';
                                if(!empty($row['video_image'])){
                                    foreach($row['video_image'] as $link){ 
                                        if(strpos($link, 'mp4') !== false){
                                            $elementNav .= '<div class="item_slide slide_nav position-relative">
                                                            <video class="img_slide_nav" width="640" height="360">
                                                                <source src="'. $link .'" type="video/mp4">
                                                            </video>
                                                            <div class="icon_play flex-center"><img src="/images/icon/play.svg"></div>
                                                        </div>';
                                            $elementFor .= '<div class="item_slide item_for">
                                                                <video class="" width="640" height="360" controls>
                                                                    <source src="'. $link .'" type="video/mp4">
                                                                </video>
                                                            </div>';
                                        }else{
                                            $elementNav .= '<div class="item_slide slide_nav"><img class="img_slide_nav" src="'. $link .'"></div>';
                                            $elementFor .= '<div class="item_slide item_for"><img class="" src="'. $link .'"></div>';
                                        }
            
                                    }
                                }
                    ?>
                        <div class="comment_item">
                            <img class="comment_avatar" src="<?= !empty($row['avatar']) ? $row['avatar'] : '/images/icon/user-icon.svg' ?>" alt="">
                            <div class="comment_group_right">
                                <div class="user_name flex-item-center">
                                    <p><?= $row['fullname'] ?></p>
                                    <span>•</span>
                                    <span><?= $row['date_review'] ?></span>
                                </div>
                                <p><?= $row['content'] ?></p>
                                <div class="video_image_comment slider-comment-nav">
                                    <?= $elementNav ?>
                                </div>
                                <div class="video_image_comment slider-comment-for hide">
                                    <?= $elementFor ?>
                                </div>
                            </div>
                        </div>
                    <?php }} ?>
                </div>
                <?php if ($dataCheckLoadMoreReviewed) { ?>
                    <div class="see_more_product">
                        <button dt-type="reviewed" class="btn_load_more see_more_product_reviewed">Xem thêm</button>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>
</div>