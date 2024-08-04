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
                if (!empty($dataNotReview)) {
                    foreach ($dataNotReview as $row) {
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
                                            <button class="btn_action btn-blue flex-center" data-toggle="modal" data-target="#modalReview<?= $row['product_id'] ?>">Đánh giá</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="modalReview<?= $row['product_id'] ?>" tabindex="-1" role="dialog" aria-labelledby="modaReviewTitle" aria-hidden="true">
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
                                                <!-- <div class="feedback">
                                                    <div class="rating">
                                                        <input type="radio" name="rating_<?= $row['product_id'] ?>" id="rating-<?= $row['product_id'] ?>_5">
                                                        <label for="rating-<?= $row['product_id'] ?>_5"></label>
                                                        <input type="radio" name="rating_<?= $row['product_id'] ?>" id="rating-<?= $row['product_id'] ?>_4">
                                                        <label for="rating-<?= $row['product_id'] ?>_4"></label>
                                                        <input type="radio" name="rating_<?= $row['product_id'] ?>" id="rating-<?= $row['product_id'] ?>_3">
                                                        <label for="rating-<?= $row['product_id'] ?>_3"></label>
                                                        <input type="radio" name="rating_<?= $row['product_id'] ?>" id="rating-<?= $row['product_id'] ?>_2">
                                                        <label for="rating-<?= $row['product_id'] ?>_2"></label>
                                                        <input type="radio" name="rating_<?= $row['product_id'] ?>" id="rating-<?= $row['product_id'] ?>_1">
                                                        <label for="rating-1"></label>
                                                    </div>
                                                </div> -->

                                                <div id="group-stars">
                                                    <div class="rating-group">
                                                        <input disabled checked class="rating__input rating__input--none" name="rating<?= $row['product_id'] ?>" id="rating<?= $row['product_id'] ?>-none" value="0" type="radio">
                                                        <label aria-label="1 star" class="rating__label" for="rating<?= $row['product_id'] ?>-1"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                        <input class="rating__input" name="rating<?= $row['product_id'] ?>" id="rating<?= $row['product_id'] ?>-1" value="1" type="radio">
                                                        <label aria-label="2 stars" class="rating__label" for="rating<?= $row['product_id'] ?>-2"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                        <input class="rating__input" name="rating<?= $row['product_id'] ?>" id="rating<?= $row['product_id'] ?>-2" value="2" type="radio">
                                                        <label aria-label="3 stars" class="rating__label" for="rating<?= $row['product_id'] ?>-3"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                        <input class="rating__input" name="rating<?= $row['product_id'] ?>" id="rating<?= $row['product_id'] ?>-3" value="3" type="radio">
                                                        <label aria-label="4 stars" class="rating__label" for="rating<?= $row['product_id'] ?>-4"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                        <input class="rating__input" name="rating<?= $row['product_id'] ?>" id="rating<?= $row['product_id'] ?>-4" value="4" type="radio">
                                                        <label aria-label="5 stars" class="rating__label" for="rating<?= $row['product_id'] ?>-5"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                        <input class="rating__input" name="rating<?= $row['product_id'] ?>" id="rating<?= $row['product_id'] ?>-5" value="5" type="radio">
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="form-group">
                                                <div class="box-image flex-center flex-column">
                                                    <div class="form-group field-fileInput">
                                                        <label class="control-label" for="fileInput">Link ảnh sản phẩm</label>
                                                        <input type="hidden" name="Advertisement[image][]" value=""><input type="file" id="fileInput" name="Advertisement[image][]" multiple="" accept="image/*,video/*">

                                                        <div class="help-block"></div>
                                                    </div> <img src="/images/icon/icon-img.svg" alt="">
                                                    <label for="">Chọn 3 video ngắn dưới 1 phút + 8 hình ảnh</label>
                                                    <i class="error">Dung lượng ảnh tối đa 1MB, dung lượng video tối đa 20MB</i>
                                                </div>
                                                <div class="preview-box" id="previewBox"></div>
                                            </div>
                                            <div class="form-group">
                                                <label for="">Đánh giá <span class="color_red">*</span></label>
                                                <textarea rows="5" name="" id=""></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                <?php }
                } ?>
                <?php if ($dataCheckLoadMoreNotReview) { ?>
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