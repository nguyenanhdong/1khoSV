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
            ['label' => 'Lịch sử mua hàng', 'url' => ['']],
            'Chi tiết đơn hàng',
        ],
    ]);

    ?>

    <section class="voucher">
        <?= $this->render('/layouts/sidebar_info') ?>
        <div class="order_detail_right">
            <div class="status_order">
                <h2>Đơn hàng đã hoàn thành</h2>
                <div class="note_order flex-item-center">
                    <img src="/images/icon/note-order.svg" alt="">
                    <p>Nếu hàng nhận được có vấn đề, bạn có thể gửi yêu cầu Trả hàng/Hoàn tiền trước 10-10-2023</p>
                </div>
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
                                <p>Số 50 Xuân Thuỷ, Cầu Giấy, Hà Nội</p>
                                <span>Hà Nội</span>
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
                                <p>MBBank</p>
                                <span>**** 5647</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="price_order">
                    <h2>Thanh toán</h2>
                    <div class="d-flex justify-content-between">
                        <p>Giá</p>
                        <span>200.000</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <p>Phí ship</p>
                        <span>30.000</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <p>Tổng</p>
                        <span class="price_final">230.000</span>
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