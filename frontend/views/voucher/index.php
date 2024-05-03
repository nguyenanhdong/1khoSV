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
        <div class="voucher_sidebar">
            <div class="shop_info">
                <div class="shop_avatar">
                    <img class="w-100" src="/images/page/lazada.png" alt="">
                </div>
                <div class="shop_des">
                    <p>Shop Máy Cày <img src="/images/icon/badge.svg" alt=""></p>
                    <span>Ví tích điểm <strong>1.000</strong> xu</span>
                </div>
            </div>
            <div class="sidebar_item mt-4">
                <a href="">Thông tin cá nhân <i class="far fa-angle-right"></i></a>
            </div>
            <div class="content_sidebar">
                <div class="sidebar_item_action">
                    <p>Lịch sử mua hàng</p>
                    <div class="group_item_action">
                        <a href="">
                            <div class="flex-center">
                                <img src="/images/icon/list.svg" alt="">
                            </div>
                            <span>Đã xác nhận</span>
                        </a>
                        <a href="">
                            <div class="flex-center">
                                <img src="/images/icon/bag-search.svg" alt="">
                            </div>
                            <span>Chờ xác nhận</span>
                        </a>
                        <a href="">
                            <div class="flex-center">
                                <img src="/images/icon/car.svg" alt="">
                            </div>
                            <span>Đang giao</span>
                        </a>
                        <a href="">
                            <div class="flex-center">
                                <img src="/images/icon/package-sended.svg" alt="">
                            </div>
                            <span>Đã mua</span>
                        </a>
                    </div>

                </div>
                <div class="sidebar_item_action mt-4 action_color">
                    <p>Quan tâm</p>
                    <div class="group_item_action">
                        <a href="">
                            <div class="flex-center">
                                <img src="/images/icon/danh-gia.svg" alt="">
                            </div>
                            <span>Đánh giá</span>
                        </a>
                        <a href="">
                            <div class="flex-center">
                                <img src="/images/icon/da-xem.svg" alt="">
                            </div>
                            <span>Sản phẩm đã xem</span>
                        </a>
                        <a href="">
                            <div class="flex-center">
                                <img src="/images/icon/heart.svg" alt="">
                            </div>
                            <span>Sản phẩm yêu thích</span>
                        </a>
                        <a href="">
                            <div class="flex-center">
                                <img src="/images/icon/hoan-tien.svg" alt="">
                            </div>
                            <span>Trả hàng, hoàn tiền</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="sidebar_item">
                <a href="">Ví tích điểm <i class="far fa-angle-right"></i></a>
            </div>
            <div class="sidebar_item">
                <a href="">Chia sẻ ứng dụng <i class="far fa-angle-right"></i></a>
            </div>
            <div class="sidebar_item">
                <a href="">Đánh giá ứng dụng <i class="far fa-angle-right"></i></a>
            </div>
            <div class="sidebar_item">
                <a href="">Liên hệ <i class="far fa-angle-right"></i></a>
            </div>
            <div class="sidebar_item">
                <a href="">Giới thiệu <i class="far fa-angle-right"></i></a>
            </div>
            <div class="sidebar_item">
                <a href="">Mời bạn bè <i class="far fa-angle-right"></i></a>
            </div>
            <div class="sidebar_item">
                <a href="">Bán hàng cùng sàn <i class="far fa-angle-right"></i></a>
            </div>
            <div class="sidebar_item">
                <a href="">Xoá tài khoản <i class="far fa-angle-right"></i></a>
            </div>
            <button class="log_out"><img src="/images/icon/logout.svg" alt="">Đăng Xuất</button>
        </div>

        <div class="voucher_content">
            <div class="tab_voucher">
                <button class="active">Chưa sử dụng</button>
                <button>Đã dùng/hết hạn</button>
            </div>
            <div class="voucher_list">
                <?php for ($i = 0; $i < 20; $i++) { ?>
                    <div class="voucher_item">
                        <div class="voucher_avatar flex-center">
                            <img src="/images/page/img-voucher.png" alt="">
                        </div>
                        <div class="voucher_desc">
                            <span>Giảm 50k</span>
                            <p>Hoàn 10% cho đơn hàng có giá trị trên 1tr vào KHOvoucher</p>
                            <a href="">Dùng ngay</a>
                        </div>
                        <div class="tooltips">
                            <img src="/images/icon/i.svg" alt="">
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>
</div>