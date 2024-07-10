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
            ['label' => 'Lịch sử mua hàng', 'url' => ['']],
            'Chi tiết đơn hàng',
        ],
    ]);

    ?>

    <section class="voucher">
        <?= $this->render('/layouts/sidebar_info') ?>
        <div class="order_detail_right">
            <div class="status_order">
                <h2><?= $data['status_name'] ?></h2>
                <?php if(!empty($data['date_refund_expire'])){ ?>
                    <div class="note_order flex-item-center">
                        <img src="/images/icon/note-order.svg" alt="">
                        <p>Nếu hàng nhận được có vấn đề, bạn có thể gửi yêu cầu Trả hàng/Hoàn tiền trước 10-10-2023</p>
                    </div>
                <?php } ?>
                <div class="desc_item">
                    <div class="flex-center avatar_pro">
                        <img src="<?= $data['product_info']['image'] ?>" alt="<?= $data['product_info']['name'] ?>">
                    </div>
                    <div class="text_desc d-flex flex-column">
                        <p><?= $data['product_info']['name'] ?></p>
                        <div class="flex-item-center">
                            <strong><?= HelperController::formatPrice($data['product_info']['price']) ?></strong>
                            <span>-<?= $data['product_info']['percent_discount'] ?>%</span>
                        </div>
                        <div class="flex-item-center justify-content-between">
                            <p>Số lượng <?= $data['product_info']['quantity'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="detail_order">
                <div class="payment_type d-flex flex-column">
                    <div class="type_text d-flex flex-column">
                        <div class="title_type flex-item-center justify-content-between">
                            <p>Thông tin vận chuyển</p>
                        </div>
                        <div class="type_text_item">
                            <div class="flex-center">
                                <img src="/images/icon/map.svg" alt="">
                            </div>
                            <div>
                                <p><?=  $data['shipping_info']['address'] .', ' . $data['shipping_info']['district'] . ', ' . $data['shipping_info']['province'] ?></p>
                                <span><?= $data['shipping_info']['province'] ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="type_text">
                        <div class="title_type flex-item-center justify-content-between">
                            <p>Phương thức thanh toán</p>
                        </div>
                        <div class="type_text_item">
                            <div class="flex-center">
                                <img src="/images/icon/bank.svg" alt="">
                            </div>
                            <div>
                                <p><?= $data['bank_payment_info']['ten_bank'] ?></p>
                                <span><?= $data['bank_payment_info']['stk'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="price_order">
                    <h2>Thanh toán</h2>
                    <div class="d-flex justify-content-between">
                        <p>Giá</p>
                        <span><?= HelperController::formatPrice($data['pay_info']['price']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <p>Phí ship</p>
                        <span><?= HelperController::formatPrice($data['pay_info']['fee_ship']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <p>Tổng</p>
                        <span class="price_final"><?= HelperController::formatPrice($data['pay_info']['total_price']) ?></span>
                    </div>
                    <div class="action_history">
                        <button class="btn_action btn-blue flex-center">Trả hàng/Hoàn tiền</button>
                        <button class="btn_action btn-orange flex-center">Đánh giá</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>